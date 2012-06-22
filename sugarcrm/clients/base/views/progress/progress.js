/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.FilterView
 * @alias SUGAR.App.layout.FilterView
 * @extends View.View
 */
({
    initialize: function (options) {
        _.bindAll(this); // Don't want to worry about keeping track of "this"
        // CSS className must be changed to avoid conflict with Bootstrap CSS.
        options.className = "progressBar";
        app.view.View.prototype.initialize.call(this, options);
    },

    bindDataChange: function () {
        this.model = this.context.model.forecasts.progress;
        this.worksheetCollection = this.context.model.forecasts.worksheet;
        
        this.model.on('change', this.render);
        this.worksheetCollection.on('change reset', this.calculatePipelineSize);
        this.context.on("change:selectedUser", this.updateProgressForSelectedUser);
    },
    
    calculatePipelineSize: function() {
        var ps = 0;
        var likelyTotal = this.worksheetCollection.reduce(function(memo, model) {
            // Only add up values that are "included" in the worksheet.
            if ( model.get('forecast') === true && !(/closed (?:won|lost)/i).test(model.get("sales_stage")) ) {
                console.log("adding likely for ", model.get('name'));
                memo += parseInt(model.get('likely_case_worksheet'), 10);
            }
            return memo;
        }, 0);
        
        if ( likelyTotal > 0 ) {
            ps = this.model.get('revenue') / likelyTotal;
            
            if ( ps < 2 ) {
                // Round to 1 decimal place
                ps = Math.round( ps * 10 )/10;
            } else {
                // Show whole number
                ps = Math.round( ps );
            }
        }

        // This value is used in the template.
        this.pipelineSize = ps;
        this.render();
    },
    
    render: function () {
        _.extend(this, this.model.toJSON());
        app.view.View.prototype.render.call(this);
    },

    updateProgressForSelectedUser: function (context, user) {
        var urlParams = $.param({
            userId: this.context.get("selectedUser")["id"],
            timePeriodId: this.context.get("selectedTimePeriod")["id"],
            shouldRollup: (this.context.get("showManagerOpportunities") ? 1 : 0)
        });
        this.model.fetch({
            data: urlParams
        });
    }
})
