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
 * This field uses the `EmailClientLaunch` plugin to launch the appropriate
 * email client.
 *
 * Recipients to pre-populate are pulled from the mass_collection.
 * For external email client: Changes to the mass_collection will rebuild the mailto: link
 * For internal email client: Recipients are gathered from the mass_collection on click.
 * In order for the recipients to be prepopulated, this field requires the models in the
 * mass_collection contain the email field - if not, recipients will simply be blank (no error).
 *
 * @class View.Fields.Base.MassEmailButtonField
 * @alias SUGAR.App.view.fields.BaseMassEmailButtonField
 * @extends View.Fields.Base.ButtonField
 */
({
    extendsFrom: 'ButtonField',

    /**
     * {@inheritDoc}
     *
     * Add the `EmailClientLaunch` plugin and force use of `ButtonField`
     * templates.
     */
    initialize: function(options) {
        this.plugins = _.union(this.plugins, ['EmailClientLaunch']);
        this._super('initialize', [options]);
    },

    /**
     * Set up `add`, `remove` and `reset` listeners on the `mass_collection` so
     * we can render this button appropriately whenever the mass collection changes.
     */
    bindDataChange: function() {
        var massCollection = this.context.get('mass_collection');
        massCollection.on('add remove reset', this.render, this);
        this._super('bindDataChange');
    },

    /**
     * Clean up listener on mass_collection updates
     */
    unbindData: function() {
        var massCollection = this.context.get('mass_collection');
        if (massCollection) {
            massCollection.off(null, null, this);
        }
        this._super('unbindData');
    },

    /**
     * Map mass collection models to the appropriate format
     * required to prepopulate the to_addresses on email compose
     *
     * @returns {Object} options to prepopulate on the email compose
     * @private
     */
    _retrieveEmailOptionsFromLink: function() {
        var massCollection = this.context.get('mass_collection'),
            toAddresses = _.map(massCollection.models, function(model) {
                return {bean: model};
            }, this);
        return {
            to_addresses: toAddresses
        };
    }
})
