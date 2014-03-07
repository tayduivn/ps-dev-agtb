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
    events: {
        "click a[data-action=plus]": "plus",
        "click a[data-action=minus]": "minus"
    },

    plugins: [],

    initialize: function(options) {
        debugger;
        this._super("initialize", [options]);
        if (!this.model.has('useful')) {
            this.model.add('useful');
            this.model.set('useful', 0);
        }
        if (!this.model.has('useful')) {
            this.model.add('notuseful');
            this.model.set('notuseful', 0);
        }
    },

    plus: function(e) {
        this.model.set('useful', parseInt(this.model.get('useful')) + 1);
        this.model.save();
        this.voted = true;
        this._render();
    },

    minus: function(e) {
        this.model.set('notuseful', parseInt(this.model.get('notuseful')) + 1);
        this.model.save();
        this.voted = true;
        this._render();
    },

    _render: function() {
        debugger;
        this._super('_render');
    }
})
