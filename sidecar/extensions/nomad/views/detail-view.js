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
            //find 'name' field, otherwise get the first field as header field
            var headerField, headerPanelInd, headerFieldInd;
            headerField = this.filterFields(function (field, fieldIndex, panel, panelIndex) {
                if (field.name == "name") {
                    headerField = field;
                    headerPanelInd = panelIndex;
                    headerFieldInd = fieldIndex;
                    return true;
                }
            })[0];
            if (!headerField) {
                headerField = this.meta.panels[0].fields[0];
                headerPanelInd = 0;
                headerFieldInd = 0;
            }

            //get four other fields to output
            var fields = [], i = 0;
            while (fields.length < 4) {
                if (!(headerFieldInd == i && headerPanelInd == 0)) fields.push(this.meta.panels[0].fields[i]);
                i++;
            }

            //get link type fields
            var linkFields = this.filterFields(function (field, fieldIndex, panel, panelIndex) {
                if (field.type == "link") return true;
            });

            //create custom data object
            var self = this,
                dataObj = {
                    viewObj: self,
                    customData: {
                        headerField: headerField,
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