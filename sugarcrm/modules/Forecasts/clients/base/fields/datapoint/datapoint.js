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
/**
 * Datapoints in the info pane for Forecasts
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
     * What was the number from the last commit
     */
    last_commit: undefined,

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
            if(!this.hasAccess) {
                return false;
            }

            // adjust the arrow
            this.arrow = app.utils.getArrowIconColorClass(this.total, this.initial_total);

            return true;
        }, this);
    },

    bindDataChange: function() {
        if(!this.hasAccess) {
            return;
        }

        this.context.on('change:selectedUser change:selectedTimePeriod', function() {
            this.last_commit = undefined;
            this.initial_total = 0;
            this.total = 0;
            this.arrow = '';
        }, this);

        // any time the main forecast collection is reset
        // this contains the commit history
        this.collection.on('reset', function() {
            // get the first line
            var model = _.first(this.collection.models)
            if(!_.isUndefined(model)) {
                this.last_commit = app.math.round(model.get(this.total_field), 2);
                this.initial_total = app.math.round(model.get(this.total_field), 2);
            } else {
                this.last_commit = undefined;
                this.initial_total = 0;
                this.total = 0;
                this.arrow = '';
            }
            if(!this.disposed) this.render();
        }, this);
        this.context.on('forecasts:worksheet:totals', function(totals, type) {
            var field = this.total_field;
            if(type == "manager") {
                field += '_adjusted'
            }
            this.total = totals[field];
            this.previous_type = type;

            if(!this.disposed) this.render();
        }, this);
    }
})
