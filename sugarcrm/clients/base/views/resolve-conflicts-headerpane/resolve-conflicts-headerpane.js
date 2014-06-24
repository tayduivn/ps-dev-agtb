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
 * @class View.Views.Base.ResolveConflictsHeaderpaneView
 * @alias SUGAR.App.view.views.BaseResolveConflictsHeaderpaneView
 * @extends View.Views.Base.HeaderpaneView
 */
({
    extendsFrom: 'HeaderpaneView',

    /**
     * Register event handlers for the buttons and set the title.
     * @param options
     */
    initialize: function(options) {
        this.events = _.extend({}, this.events, {
            'click [name=select_button]': 'selectClicked',
            'click [name=cancel_button]': 'cancelClicked'
        });

        this._super('initialize', [options]);

        this._setTitle();

        this.context.on("change:selection_model", this.enableSelectButton, this);
    },

    /**
     * Set header pane title.
     * @private
     */
    _setTitle: function() {
        var modelToSave = this.context.get('modelToSave'),
            titleTemplate = Handlebars.compile(app.lang.getAppString('LBL_RESOLVE_CONFLICT')),
            name = modelToSave.get('name') || modelToSave.get('full_name');

        this.title = titleTemplate({
            name: name
        });
    },

    /**
     * Perform action according to whether the client's or database's data was selected.
     * @param event
     */
    selectClicked: function(event) {
        var selected = this.context.get('selection_model'),
            modelToSave = this.context.get('modelToSave'),
            dataInDb = this.context.get('dataInDb'),
            origin;

        if (selected instanceof Backbone.Model) {
            origin = selected.get('_dataOrigin');
            if (origin === 'client') {
                modelToSave.set('date_modified', dataInDb.date_modified);
                app.drawer.close(modelToSave, false);
            } else if (origin === 'database') {
                modelToSave.set(dataInDb);
                // trigger sync so that synced attributes are reset
                modelToSave.trigger('sync');
                app.drawer.close(modelToSave, true);
            }
        }
    },

    /**
     * Enable select button when a row has been selected.
     * @param context
     * @param selected
     */
    enableSelectButton: function(context, selected) {
        if (selected) {
            this.$('[name=select_button]').removeClass('disabled');
        }
    },

    /**
     * Close the drawer when cancel is clicked.
     * @param event
     */
    cancelClicked: function(event) {
        app.drawer.close();
    }
})
