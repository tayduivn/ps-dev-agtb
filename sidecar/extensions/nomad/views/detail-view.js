(function(app) {

    app.view.views.DetailView = app.view.View.extend({
        events: {
            "click #backRecord": function () {
                app.router.goBack();
            },
            "click #record-action .phone": function () {
                app.nomad.callPhone(this.getFieldsDataArray(this.phoneFields));
            },
            "click #record-action .email": function () {
                app.nomad.sendEmail(this.getEmails());
            },
            "click #record-action .message": function () {
                app.nomad.sendSms(this.getFieldsDataArray(this.phoneFields));
            },
            "click #record-action .link": function () {
                var urls = this.getFieldsDataArray(this.urlFields);
                //debugger;
            },
            "click #record-action .map": function () {
                var addressObj = this.getFieldsDataHash(this.addressFields);
                //debugger;
            }
        },

        initialize: function (options) {
            app.view.View.prototype.initialize.call(this, options);

            //iterate over all fields and get the needed ones
            var view = this;
            var headerField, image, fields = [], phones = [], urls = [], addressFields = [];
            _.each(this.meta.panels, function (panel, panelIndex) {
                _.each(panel.fields, function (field, fieldIndex) {

                    if (field.name == "name" && !headerField) {         //find header field (first 'name' type)
                        headerField = field;
                    } else if (field.type == "image" && !image) {       //find first image
                        image = field;
                    } else if (field.type == "phone") {                 //find all phones
                        phones.push(field);
                    } else if (field.type == "url") {                   //find all urls
                        urls.push(field);
                    } else if (field.type == "email") {                 //if email - do nothing

                    } else if (field.name.indexOf("address") > -1) {    //find address fields
                        addressFields.push(field);
                    } else if (field.name.indexOf("email") == 0) {      //find fields which name starts from 'email'
                        field.type = "email_temp";
                    } else if (fields.length < 4) {                     //find four other fields to output
                        fields.push(field);
                    }

                });
            });

            //find link fields
            var linkFields = app.nomad.getLinks(this.model);

            //save founded fields
            this.headerField = headerField;
            this.imageField = image;
            this.linkFields = linkFields;
            this.otherFields = fields;

            this.addressFields = addressFields;
            this.phoneFields = phones;
            this.urlFields = urls;

        },

        /**
         * Renders the view onto the page.
         *
         * Overrides default views method to pass custom data object as the context.
         * @protected
         */
        _renderSelf: function () {

            //create custom data object
            var data = {
                    viewObj: this,
                    headerField: this.headerField,
                    image: this.imageField,
                    fields: this.otherFields,
                    links: this.linkFields
                };

            //pass custom data object as the context
            this._renderWithContext(data);
        },

        /**
         * Returns array of fields data (from model), specified by array of fields metadata.
         * @param fields
         * @return {Array}
         */
        getFieldsDataArray: function (fields) {
            var view = this;
            var value, data = [];
            _.each(fields, function (field, index) {
                value = view.model.get(field.name);
                if (value) data.push({
                    name: field.label,
                    value: value
                });
            });
            return data;
        },

        /**
         * Returns hash of fields data (from model), specified by array of fields metadata.
         * @param fields
         * @return {Object}
         */
        getFieldsDataHash: function (fields) {
            var view = this;
            var value, data = {};
            _.each(fields, function (field, index) {
                value = view.model.get(field.name);
                if (value) data[field.label] = value;
            });
            return data;
        },

        /**
         * Returns array of emails from model.
         * @return {Array}
         */
        getEmails: function () {
            var view = this;
            var email, emailsArray = [];
            _.each(this.model.get('email'), function (email, index) {
                if (email.email_address) emailsArray.push(email.email_address);
            });
            return emailsArray;
        }
    });

})(SUGAR.App);