(function(app) {

    app.view.views.DetailView = app.view.View.extend({
        events: {
            "click #backRecord": function () {
                app.router.goBack();
            },
            "click #record-action .phone": function () {
                app.nomad.callPhone(this.getPhones());
            },
            "click #record-action .message": function () {
                debugger;
                app.nomad.sendEmail(this.getEmails());
            },
            "click #record-action .comment": function () {
                debugger;
                app.nomad.sendSms(this.getPhones());
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
            var view = this;
            var headerField, image, fields = [], phones = [];
            _.each(this.meta.panels, function (panel, panelIndex) {
                _.each(panel.fields, function (field, fieldIndex) {

                    if (!headerField && field.name == "name") {         //find header field (first 'name' type)
                        headerField = field;
                    } else if (field.type == "phone") {                 //find all phones
                        phones.push(field);
                    } else if (field.type == "email") {                 //if email - do nothing

                    } else if (field.name.indexOf("email") == 0) {      //find fields which name starts from 'email'
                        field.type = "email_temp";
                    } else if (!image && field.type == "image") {       //find first image
                        image = field;
                    } else if (fields.length < 4) {                     //find four other fields to output
                        fields.push(field);
                    }

                });
            });

            this.phoneFields = phones;

            //find link fields
            var linkFields = _.filter(this.model.fields, function (field, key) {
                if (field.type == 'link') return true;
            });

            //create custom data object
            var self = this,
                dataObj = {
                    viewObj: self,
                    headerField: headerField,
                    image: image,
                    fields: fields,
                    links: linkFields
                };

            //pass custom data object as the context
            this._renderWithContext(dataObj);
        },

        getPhones: function () {
            var view = this;
            var phone, phonesArray = [];
            _.each(this.phoneFields, function (phoneField, index) {
                phone = view.model.get(phoneField.name);
                if (phone) phonesArray.push(phone);
            });
            return phonesArray;
        },

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