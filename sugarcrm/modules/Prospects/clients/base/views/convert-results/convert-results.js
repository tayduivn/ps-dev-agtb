({
    associatedModels: null,

    events:{
        'click .preview-list-item':'previewRecord'
    },

    initialize: function(options) {
        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, options);
        app.events.on("list:preview:decorate", this.decorateRow);
        this.associatedModels = app.data.createMixedBeanCollection();
    },

    bindDataChange: function() {
        this.model.on("change", this.populateResults);
    },

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
    },

     /**
     * Handle firing of the preview render request for selected row
     *
     * @param e
     */
    previewRecord: function(e) {
        var $el = this.$(e.currentTarget),
            data = $el.data(),
            model = app.data.createBean(data.module, {id:data.id});

        model.fetch({
            success: _.bind(function(model) {
                model.set("_module", data.module);
                app.events.trigger("preview:render", model, this.associatedModels);
            }, this)
        });
    },

    /**
     * Decorate a row in the list that is being shown in Preview
     * @param model Model for row to be decorated.  Pass a falsy value to clear decoration.
     */
    decorateRow: function(model){
        this.$("tr.highlighted").removeClass("highlighted current above below");
        if(model){
            var rowName = model.module+"_"+ model.get("id");
            var curr = this.$("tr[name='"+rowName+"']");
            curr.addClass("current highlighted");
            curr.prev("tr").addClass("highlighted above");
            curr.next("tr").addClass("highlighted below");
        }
    }
})
