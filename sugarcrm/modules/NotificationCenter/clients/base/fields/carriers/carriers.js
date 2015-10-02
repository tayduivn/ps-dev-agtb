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

/**
 * @class View.Fields.Base.NotificationCenterCarrierField
 * @alias SUGAR.App.view.fields.BaseNotificationCenterCarrierField
 * @extends View.Fields.Base.BaseField
 */
({
    events: {
        'click #carriers-list input': 'handleCarrierClick'
    },

    /**
     * List of available carriers from config model.
     */
    items: [],

    /**
     * Extract carriers data from the model before render.
     * @inheritdoc
     */
    _render: function() {
        this._setItems();
        this._super('_render');
    },

    /**
     * Override to remove default DOM change listener, we use custom handling.
     * @inheritDoc
     */
    bindDomChange: function() {
    },

    /**
     * @inheritDoc
     */
    bindDataChange: function() {
        if (this.model) {
            this.model.on('change:carriers', this.render, this);
        }
    },

    /**
     * Extracts all carriers from model and prepares them to be rendered by a field.
     * @private
     */
    _setItems: function() {
        this.items = [];
        _.each(this.model.get('carriers'), function(value, key) {
           this.items.push({
               name: key,
               label: app.lang.get('LBL_TITLE', key),
               enabled: value.status
           });
        }, this);
    },

    /**
     * Enable/disable particular carrier.
     * @param {Event} evt DOM event.
     */
    handleCarrierClick: function(evt) {
        var modifiedCarriers, name;
        name = $(evt.currentTarget).attr('name');
        modifiedCarriers = _.clone(this.model.get('carriers'));
        modifiedCarriers[name].status = $(evt.currentTarget).is(':checked');
        this.model.set('carriers', modifiedCarriers);
    }
})

