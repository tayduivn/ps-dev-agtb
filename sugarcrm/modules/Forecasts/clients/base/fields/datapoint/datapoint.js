/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
({

    /**
     * Tracking the type of totals we are seeing
     */
    previous_type: '',

    /**
     * Arrow Colors
     */
    arrow: '',

    /**
     * What was the first total we got for a given type
     */
    initial_total: '',

    /**
     * The total we want to display
     */
    total: 0,

    /**
     * Can we actually display this field and have the data binding on it
     */
    hasAccess: true,

    initialize: function(options) {
        app.view.Field.prototype.initialize.call(this, options);

        this.total_field = this.total_field || this.name;

        this.hasAccess = app.utils.getColumnVisFromKeyMap(this.name, 'forecastsWorksheet');

        // before we try and render, lets see if we can actually render this field
        this.before('render', function() {
            return this.hasAccess
        }, this);
    },

    bindDataChange: function() {
        if(!this.hasAccess) {
            return;
        }
        this.context.on('forecasts:worksheet:totals', function(totals, type) {

            var new_total = totals[this.total_field];

            if(this.previous_type != type) {
                this.initial_total = new_total
            }

            if(this.previous_type == type) {
                // figure out the arrows
                this.arrow = app.utils.getArrowIconColorClass(new_total, this.initial_total);
            } else {
                this.arrow = '';
            }

            this.total = new_total;
            this.previous_type = type;

            if(!this.disposed) this.render();
        }, this);
    }


})
