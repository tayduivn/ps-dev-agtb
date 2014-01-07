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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({
    plugins: ['EllipsisInline'],
    'events': {
        'keyup input[name=name]': 'handleKeyup'
    },

    _render: function() {
        if (this.view.name === 'record') {
            this.def.link = false;
        } else if (this.view.name ==='preview') {
            this.def.link = true;
        }
        this._super('_render');
    },

    handleKeyup: _.throttle(function()
	{
		var searchedValue = this.$('input.inherit-width').val();

        if(searchedValue && searchedValue.length >= 3)
        {
            this.context.trigger('input:name:keyup',searchedValue);    
        }	
	},1000,{leading:false})
})
