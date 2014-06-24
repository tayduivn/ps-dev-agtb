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
 * Rowaction is a button that when selected will trigger a Backbone Event.
 *
 * @class View.Fields.Base.RowactionField
 * @alias SUGAR.App.view.fields.BaseRowactionField
 * @extends View.Fields.Base.ButtonField
 */
({
    extendsFrom: 'ButtonField',
    initialize: function(options) {
        this.options.def.events = _.extend({}, this.options.def.events, {
            'click .rowaction': 'rowActionSelect'
        });
        this._super("initialize", [options]);
    },
    /**
     * Triggers event provided at this.def.event on the view's context object by default.
     * Can be configured to trigger events on 'view' itself or the view's 'layout'.
     * @param evt
     */
    rowActionSelect: function(evt) {
        if(this.isDisabled()){
            return;
        }
        // make sure that we are not disabled first
        if(this.preventClick(evt) !== false) {
            var target = this.view.context;  // view's 'context' is target by default
            if (this.def.target === 'view') {
                target = this.view;
            } else if (this.def.target === 'layout') {
                target = this.view.layout;
            }
            if ($(evt.currentTarget).data('event')) {
                target.trigger($(evt.currentTarget).data('event'), this.model, this);
            }
        }
    }
})
