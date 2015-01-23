/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
({
    plugins: ['Editable'],

    events: {
        'click [data-spotlight=add]': 'addRow'
    },

    initialize: function(options) {
        this._super('initialize', [options]);
    
        this.initRows();
        this.backupModel = this.collection.toJSON()
    },

    hasUnsavedChanges: function() {
        var newModel = this.collection.toJSON();

        return !_.isEqual(this.backupModel, newModel);
    },

    initRows: function() {
        var key = app.user.lastState.buildKey('spotlight', 'config');
        var data = app.user.lastState.get(key);
        if (!data) {
            return;
        }
        _.each(data, function(row) {
            var rowComponent = this.addRow();
            rowComponent.model.set('action', row.action);
            rowComponent.model.set('keyword', row.keyword);
        }, this);
    },

    /**
     * Adds a `spotlight-config-list-row` view to the layout.
     * @param {Event} [evt] The `click` event.
     */
    addRow: function(evt) {
        var def = _.extend({view: 'spotlight-config-list-row'}, app.metadata.getView(null, 'spotlight-config-list-row'));
        var rowComponent = this.createComponentFromDef(def, this.context, this.module);

        this.addComponent(rowComponent, def);
        rowComponent.render();
        return rowComponent;
    },

    /**
     * @override
     */
    _placeComponent: function(component) {
        this.$('[data-spotlight=actions]').append(component.el);
    }
})
