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
 * @class View.Fields.Base.StatusField
 * @alias SUGAR.App.view.fields.BaseStatusField
 * @extends View.Field
 */
({
    /**
     * Additional CSS Classes to be added to hbs
     */
    cssClasses: '',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.buildCSSClasses();
    },

    /**
     * Gets the field value and sets cssClasses
     */
    buildCSSClasses: function() {
        var status = this.model.get(this.name);
        if (status) {
            status = status.replace(' ', '_');
            this.cssClasses = 'field_' + this.name + '_' + status;
        }
    }
})
