({
    extendsFrom: 'ConvertResults',

    /**
     * Fetches the data for the leads model
     */
    populateResults: function() {
        if (!_.isEmpty(this.model.get('lead_id'))){
            var leads = app.data.createBean('Leads', { id: this.model.get('lead_id')});
            leads.fetch({
                success: _.bind(this.populateLeadCallback, this),
                error: function() {
                    app.logger.error.log("error");
                }
            });
        }
    },

    /**
     * Success callback for retrieving associated lead model
     * @param leadModel
     */
    populateLeadCallback: function (leadModel) {
        var rowTitle;

        this.associatedModels.reset();

        rowTitle = app.lang.get('LBL_CONVERTED_LEAD',this.module);

        leadModel.set('row_title', rowTitle);

        this.associatedModels.push(leadModel);

        app.view.View.prototype.render.call(this);
    }
})
