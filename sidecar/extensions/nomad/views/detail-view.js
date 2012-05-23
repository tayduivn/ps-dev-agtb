(function(app) {

    app.view.views.DetailView = app.view.View.extend({
        events: {
            "click #backRecord": function () {
                app.router.goBack();
            }
        },

        /**
         * Renders the view onto the page.
         *
         * Overrides default views method to pass custom data object as the context.
         * @protected
         */
         _render: function () {
            //iterate over all fields and get the needed ones
            //TODO: if it can be the case, when there is no 'name' field,
            //we need to iterate firstly to try to find 'name' field,
            //if it not exists - get first non-image and non-link field as header
            //and only after that get other four fields to output
            var headerField, image, linkFields = [], fields = [];
            _.each(this.meta.panels, function (panel, panelIndex) {
                _.each(panel.fields, function (field, fieldIndex) {

                    if (!headerField && field.name == "name") {         //find header field (first 'name' type)
                        headerField = field;
                    } else if (!image && field.type == "image") {       //find first image
                        image = field;
                    } else if (field.type == "link") {                  //find links fields
                        linkFields.push(field);
                    } else if (fields.length < 4) {                     //find four other fields to output
                        fields.push(field);
                    }

                });
            });

            //create custom data object
            var self = this,
                dataObj = {
                    viewObj: self,
                    customData: {
                        headerField: headerField,
                        image: image,
                        fields: fields,
                        links: linkFields
                    }
                };

            //pass custom data object as the context
            this._renderWithContext(dataObj);
        },

        /**
         * Iterate over all the fields and get needed ones, depending on filterFunc result
         * @param filterFunc
         * @return {*}
         */
        filterFields: function (filterFunc) {
            var filteredFields = [];
            _.each(this.meta.panels, function (panel, panelIndex) {
                _.each(panel.fields, function (field, fieldIndex) {
                    if (filterFunc(field, fieldIndex, panel, panelIndex)) filteredFields.push(field);
                });
            });
            return filteredFields;
        }

    });

})(SUGAR.App);