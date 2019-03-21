/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * MultiLineList supports more than one line of data per Model row. User can group
 * relevant data into the same column of the data-table.
 *
 * The view metadata of each field columns uses subfields property to determine the
 * actual data being shown. Each subfields entry contains field data.
 *
 * Example:
 * array(
 *     'fields' => array(
 *         array(
 *             'user' => 'user',
 *             'label' => 'LBL_USER',
 *             'width' => 'xlarge',
 *             'subfields' => array(
 *                 array(
 *                     'name' => 'user_name',
 *                     'label' => 'LBL_USER_NAME',
 *                     'enable' => true,
 *                     'default' => true,
 *                 ),
 *                 array(
 *                     'name' => 'user_id',
 *                     'label' => 'LBL_USER_ID',
 *                     'enable' => true,
 *                     'default' => true,
 *                 ),
 *             ),
 *         ),
 *     ),
 * )
 *
 * @class View.Views.Base.MultiLineListView
 * @alias SUGAR.App.view.views.BaseMultiLineListView
 * @extends View.Views.Base.ListView
 */
({
    extendsFrom: 'ListView',
    className: 'multi-line-list-view',
    drawerModelId: null,

    /**
     * @override
     */
    initialize: function(options) {
        var listViewMeta = app.metadata.getView(options.module, 'multi-line-list') || {};
        options.meta = _.extend({}, listViewMeta, options.meta || {});

        this._super('initialize', [options]);

        this.leftColumns = [];
        this.addActions(this.meta);

        var leftColumnsEvents = {};
        //add an event delegate for left action dropdown buttons onclick events
        if (this.leftColumns.length) {
            leftColumnsEvents = {
                'hidden.bs.dropdown .actions': 'updateDropdownDirection',
                'shown.bs.dropdown .actions': 'updateDropdownDirection',
            };
        }
        this.events = _.extend({}, this.events, leftColumnsEvents, {
            'click .multi-line-row': 'handleRowClick',
        });
    },

    /**
     * Trigger action when a model row is clicked
     *
     * @param {Object} event Click event that triggers the function
     */
    handleRowClick: function(event) {
        var $el = this.$(event.target);

        // ignore event triggered by dorpdown-toggle or any action dropdown is open
        if (this.isDropdownToggle($el) || this.isActionsDropdownOpen()) {
            return;
        };

        var modelId = $el.closest('.multi-line-row').data('id');

        // close drawer when drawer is open with data from different model
        if (app.drawer.count() && modelId !== this.drawerModelId) {
            app.drawer.closeImmediately();
            this._clearDrawerModelId();
        }

        if (app.drawer.count() === 0) {
            var model = this.collection.get(modelId);
            app.drawer.open({
                layout: 'row-model-data',
                direction: 'horizontal',
                context: {
                    model: model,
                    module: model.get('_module')
                }
            });
            this._setDrawerModelId(modelId);
        }
    },

    /**
     * Add rowactions to left column
     *
     * @param {Object} meta View metadata
     */
    addActions: function(meta) {
        if (meta && _.isObject(meta.rowactions)) {
            var _generateMeta = function(label, cssClass, buttons) {
                return {
                    'type': 'fieldset',
                    'css_class': 'overflow-visible',
                    'fields': [
                        {
                            'type': 'rowactions',
                            'no_default_action': true,
                            'label': label || '',
                            'css_class': cssClass,
                            'buttons': buttons || []
                        }
                    ],
                };
            };
            var def = meta.rowactions;
            this.leftColumns.push(_generateMeta(def.label, def.css_class, def.actions));
        }
    },

    /**
     * Set drawerModelId
     *
     * @param {string} id id of row model data
     */
    _setDrawerModelId: function(id) {
        this.drawerModelId = id;
    },

    /**
     * Reset drawerModelId
     */
    _clearDrawerModelId: function() {
        this.drawerModelId = null;
    },

    /**
     * Check if any rowaction dropdown-menu is open
     *
     * @return {boolean} dropdown-menu open or not
     */
    isActionsDropdownOpen: function() {
        return !!this.$('.fieldset.actions.list.btn-group.open').length;
    },

    /**
     * Check if the event is triggered from dropdown-toggle
     *
     * @param {jQuery} $el element that trigger the event
     * @return {boolean} element is dropdown-toggle or not
     */
    isDropdownToggle: function($el) {
        return $el.hasClass('dropdown-toggle') || $el.parent().hasClass('dropdown-toggle');
    },

    /**
     * Update CSS class of dropdown-menu based on its vertical position
     *
     * @param {Event} event Shown/Hidden event
     */
    updateDropdownDirection: function(event) {
        var $buttonGroup = this.$(event.currentTarget).first();
        var windowHeight = $(window).height() - 65; // height of window less padding
        var menuHeight = $buttonGroup.height() + $buttonGroup.children('ul').first().height();
        if (windowHeight < $buttonGroup.offset().top + menuHeight) {
            $buttonGroup.toggleClass('dropup');
        }
    },
})
