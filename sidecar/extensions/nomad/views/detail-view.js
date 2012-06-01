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
                app.nomad.sendEmail(this.getEmails());
            },
            "click #record-action .comment": function () {
                app.nomad.sendSms(this.getPhones());
            }
        },

        /**
         * Renders the view onto the page.
         *
         * Overrides default views method to pass custom data object as the context.
         * @protected
         */
        _renderSelf: function () {
            //iterate over all fields and get the needed ones
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
            var number, numbersArray = [];
            _.each(this.phoneFields, function (phoneField, index) {
                number = view.model.get(phoneField.name);
                if (number) numbersArray.push({
                    name: phoneField.name,
                    number: number
                });
            });
            return numbersArray;
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