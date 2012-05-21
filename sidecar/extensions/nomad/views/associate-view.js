(function(app) {

    app.view.views.AssociateView = app.view.views.ListView.extend({

        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);

            // This view behaves like the list view
            this.meta = app.metadata.getView(this.module, "list");
            this.fallbackFieldTemplate = "list";
            
            this.parentModule = this.context.get("toModule");
            this.parentId = this.context.get("toId");
            this.link = this.context.get("viaLink");
            this.parentBean = app.data.createBean(this.parentModule, { id: this.parentId });

            // Flag indicating if the list is multi-select or not
            this.multiselect = app.data.canHaveMany(this.parentModule, this.link);

            app.logger.debug("Record(s) are to be associated with " +
                this.parentBean + " via link " + this.link);
            app.logger.debug("Multiselect: " + this.multiselect);
        },

        onSaveClicked: function() {

            // Loop through all selected items in the list
            // For each such item do the following:

            // Grab them from this.collection
            //var selectedIndex = 0; //
            //var selectedBean = this.collection.at(selectedIndex);
            // Create a related bean from it
            //selectedBean = app.data.createRelatedBean(this.parentBean, selectedBean, this.link);
            // Save it
            //selectedBean.save(null, {
            //    relate: true,
            //    success: function() {/* navigate back */}
            //});
        },

        onCancelClicked: function() {

        }


    });

})(SUGAR.App);