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
 * @class View.Fields.Base.NameField
 * @alias SUGAR.App.view.fields.BaseNameField
 * @extends View.Field
 */
({
    plugins: ['EllipsisInline'],
    'events': {
        'keyup input[name=name]': 'handleKeyup'
    },
    _render: function() {
        if (this.view.name === 'record') {
            this.def.link = false;
        } else if (this.view.name === 'preview') {
            this.def.link = true;
        }
        this._super('_render');
    },
    /**
     * Handles the keyup event in the account create page
     */
    handleKeyup: _.throttle(function() {
        var searchedValue = this.$('input.inherit-width').val();
        if (searchedValue && searchedValue.length >= 3) {
            this.context.trigger('input:name:keyup', searchedValue);
        }
    }, 1000, {leading: false})
})
