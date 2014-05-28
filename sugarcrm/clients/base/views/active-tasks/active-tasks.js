/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * Active tasks dashlet takes advantage of the tabbed dashlet abstraction by
 * using its metadata driven capabilities to configure its tabs in order to
 * display information about tasks module.
 *
 * Besides the metadata properties inherited from Tabbed dashlet, Active tasks
 * dashlet also supports other properties:
 *
 * - {Array} overdue_badge field def to support overdue calculation, and showing
 *   an overdue badge when appropriate.
 *
 * @class View.Views.Base.ActiveTasksView
 * @alias SUGAR.App.view.views.BaseActiveTasksView
 * @extends View.Views.Base.TabbedDashletView
 */
({
    extendsFrom: 'TabbedDashletView',

    /**
     * {@inheritDoc}
     *
     * @property {Object} _defaultSettings
     * @property {Number} _defaultSettings.limit Maximum number of records to
     *   load per request, defaults to '10'.
     * @property {String} _defaultSettings.visibility Records visibility
     *   regarding current user, supported values are 'user' and 'group',
     *   defaults to 'user'.
     */
    _defaultSettings: {
        limit: 10,
        visibility: 'user'
    },

    /**
     * {@inheritDoc}
     */
    initialize: function(options) {
        options.meta = options.meta || {};
        options.meta.template = 'tabbed-dashlet';

        this.plugins = _.union(this.plugins, [
            'LinkedModel'
        ]);

        this.tbodyTag = 'ul[data-action="pagination-body"]';

        this._super('initialize', [options]);
    },

    /**
     * {@inheritDoc}
     */
    _initEvents: function() {
        this._super('_initEvents');
        this.on('active-tasks:close-task:fire', this.closeTask, this);
        return this;
    },

    /**
     * Complete the selected task
     * @param model {app.Bean} Task model to be marked as completed
     */
    closeTask: function(model){
        var self = this;
        var name = model.get('name') || '',
            context = app.lang.get('LBL_MODULE_NAME_SINGULAR', model.module).toLowerCase() + ' ' + name.trim();
        app.alert.show('complete_task_confirmation:' + model.get('id'), {
            level: 'confirmation',
            messages: app.utils.formatString(app.lang.get('LBL_ACTIVE_TASKS_DASHLET_CONFIRM_CLOSE'), [context]),
            onConfirm: function() {
                model.save({status: 'Completed'}, {
                    showAlerts: true,
                    success: self._getRemoveModelCompleteCallback()
                });
            }
        });
    },

    /**
     * {@inheritDoc}
     *
     * FIXME: This should be removed when metadata supports date operators to
     * allow one to define relative dates for date filters.
     */
    _initTabs: function() {
        this._super("_initTabs");

        // FIXME: since there's no way to do this metadata driven (at the
        // moment) and for the sake of simplicity only filters with 'date_due'
        // value 'today' are replaced by today's date
        var today = new Date();
        today.setHours(23, 59, 59);
        today.toISOString();

        _.each(_.pluck(_.pluck(this.tabs, 'filters'), 'date_due'), function(filter) {
            _.each(filter, function(value, operator) {
                if (value === 'today') {
                    filter[operator] = today;
                }
            });
        });
    },

    /**
     * Create new record.
     *
     * @param {Event} event Click event.
     * @param {Object} params
     * @param {String} params.layout Layout name.
     * @param {String} params.module Module name.
     */
    createRecord: function(event, params) {
        if (this.module !== 'Home') {
            this.createRelatedRecord(params.module, params.link);
        } else {
            var self = this;
            app.drawer.open({
                layout: 'create-actions',
                context: {
                    create: true,
                    module: params.module
                }
            }, function(context, model) {
                if (!model) {
                    return;
                }
                self.context.resetLoadFlag();
                self.context.set('skipFetch', false);
                if (_.isFunction(self.loadData)) {
                    self.loadData();
                } else {
                    self.context.loadData();
                }
            });
        }

    },

    /**
     * New model related properties are injected into each model.
     * Update the picture url's property for model's assigned user.
     *
     * @param {Bean} model Appended new model.
     */
    bindCollectionAdd: function(model) {
        var pictureUrl = app.api.buildFileURL({
            module: 'Users',
            id: model.get('assigned_user_id'),
            field: 'picture'
        });
        model.set('picture_url', pictureUrl);
        this._super('bindCollectionAdd', [model]);
    },

    /**
     * {@inheritDoc}
     *
     * New model related properties are injected into each model:
     *
     * - {Boolean} overdue True if record is prior to now.
     */
    _renderHtml: function() {
        if (this.meta.config) {
            this._super('_renderHtml');
            return;
        }

        var tab = this.tabs[this.settings.get('activeTab')];

        if (tab.overdue_badge) {
            this.overdueBadge = tab.overdue_badge;
        }

        this._super('_renderHtml');
    }
})
