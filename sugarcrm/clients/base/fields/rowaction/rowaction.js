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
 * Rowaction is a button that when selected will trigger a Backbone Event.
 *
 * @class View.Fields.Base.RowactionField
 * @alias SUGAR.App.view.fields.BaseRowactionField
 * @extends View.Fields.Base.ButtonField
 */
({
    extendsFrom: 'ButtonField',

    /**
     * @inheritdoc
     */
    initialize: function(options) {
        this.options.def.events = _.extend({}, this.options.def.events, {
            'click .rowaction': 'rowActionSelect'
        });
        this._super('initialize', [options]);
    },

    /**
     * Triggers event provided at this.def.event on the view's context object by default.
     * Can be configured to trigger events on 'view' itself or the view's 'layout'.
     * @param {Object} [evt] The click event.
     */
    rowActionSelect: function(evt) {
        // make sure that we are not disabled first
        if (this.preventClick(evt) !== false) {
            this.propagateEvent(evt);
        }
    },

    /**
     * Triggers an event on the target to inform listeners that the button was
     * clicked.
     *
     * The event specified in the button metadata will be used as the name of
     * the event. Otherwise, the button name will be wrapped in a namespace:
     *
     *     ```
     *     button:save_button:click
     *     ```
     *
     * @see View.Fields.Base.RowactionField#getTarget
     * @param {Object} [event] The click event.
     */
    propagateEvent: function(event) {
        var eventName = this.def.event || 'button:' + this.name + ':click';
        this.getTarget().trigger(eventName, this.model, this, event);
    },

    /**
     * Returns the target on which the event should be triggered.
     *
     * @return {Core.Context} By default, the event should be triggered on the
     * context.
     * @return {View.View} The event should be triggered on the view.
     * @return {View.Layout} The event should be triggered on the layout.
     */
    getTarget: function() {
        var target;

        switch (this.def.target) {
            case 'view':
                target = this.view;
                break;
            case 'layout':
                target = this.view.layout;
                break;
            default:
                target = this.view.context;
        }

        return target;
    }
})
