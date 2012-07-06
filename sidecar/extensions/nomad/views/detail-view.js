(function(app) {

    app.view.views.DetailView = app.view.View.extend({
        events: {
            "click #backRecord": function () {
                app.router.goBack();
            },
            "click #record-action .phone": function () {
                var phones = this.getFieldsDataArray(this.phoneFields);
                app.nomad.callPhone(phones);
            },
            "click #record-action .email": function () {
                //app.nomad.sendEmail(this.getEmails());
                var emails = this.getFieldsDataArray(this.emailFields);
                app.nomad.sendEmail(emails);
            },
            "click #record-action .message": function () {
                var phones = this.getFieldsDataArray(this.phoneFields);
                app.nomad.sendSms(phones);
            },
            "click #record-action .link": function () {
                var urls = this.getFieldsDataArray(this.urlFields);
                app.nomad.openUrl(urls);
            },
            "click #record-action .map": function () {
                var addresses = this.getAddresses();
                app.nomad.openAddress(addresses);
            },
            "click .card": function (e) {
                this.$('div').find('a').first().trigger('click');
            }
        },

        initialize: function (options) {
            app.view.View.prototype.initialize.call(this, options);
            var fieldsNumber = 4;                                   //number of fields displayed on summary view

            //for testing purposes
            //this.model.set({"opportunity_role": "Influencer"});

            var self = this;
            var headerField, image, fields = [], phones = [], urls = [], emails = [], addressFields = [];

            //add specific relationship fields to the "other" fields collection
            var link = this.context.get("link");
            if (link) {
                var parentModule = this.context.get("parentModule"),
                    relFieldNames = app.data.getRelationshipFields(parentModule, link);
                if (relFieldNames && relFieldNames.length) {
                    fields = _.map(relFieldNames, function(fieldName) {
                        return app.metadata.getModule(self.module).fields[fieldName];
                    });
                    if (fields.length > fieldsNumber) fields.length = fieldsNumber;
                }
            }

            //iterate over all fields and get the needed ones
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

                    } else if (field.name &&
                              (field.name.indexOf("address_") > -1) &&
                              (field.name.indexOf("email") == -1)) {    //find address (not email addresses) fields
                        addressFields.push(field);
                    } else if (field.name && field.name.indexOf("email") == 0) {      //find fields which name starts from 'email'
                        field.type = "singleemail";
                        emails.push(field);
                    } else if (fields.length < fieldsNumber) {          //find four other fields to output
                        fields.push(field);
                    }

                });
            });

            //find link fields
            var linkFields = app.nomad.getLinks(this.model);

            //save founded fields
            this.headerField =   headerField;
            this.imageField =    image;
            this.linkFields =    linkFields;
            this.otherFields =   fields;

            //this.addressFields = addressFields;
            this.emailFields =   emails;
            this.phoneFields =   phones;
            this.urlFields =     urls;

            //group address fields by "group" properties defined in vardefs
            this.addressFieldsGroups = _.groupBy(addressFields, function(field) { return self.model.fields[field.name].group });
        },

        /**
         * Renders the view onto the page passing custom data object into the template.
         *
         * Overrides default views method to pass custom data object as the context.
         * @protected
         */
        _renderHtml: function () {
            var isPhoneBtn =    !!this.phoneFields.length,
                isEmailBtn =    !!this.emailFields.length,
                isUrlBtn =      !!this.urlFields.length,
                isAddressBtn =  !!(_.keys(this.addressFieldsGroups).length),
                isActions =     isPhoneBtn || isEmailBtn || isUrlBtn || isAddressBtn;

            //create custom data object
            var data = {
                    viewObj:        this,
                    headerField:    this.headerField,
                    image:          this.imageField,
                    fields:         this.otherFields,
                    links:          this.linkFields,

                    //action buttons settings
                    isPhoneBtn:         isPhoneBtn,
                    isPhoneNotEmpty:    this.isDataDefined(this.phoneFields),
                    isEmailBtn:         isEmailBtn,
                    isEmailNotEmpty:    this.isDataDefined(this.emailFields),
                    isMessageBtn:       isPhoneBtn,
                    isMessageNotEmpty:  this.isDataDefined(this.phoneFields),
                    isUrlBtn:           isUrlBtn,
                    isUrlNotEmpty:      this.isDataDefined(this.urlFields),
                    isAddressBtn:       isAddressBtn,
                    isAddressNotEmpty:  this.isDataDefined(this.addressFieldsGroups),
                    isActions:          isActions
                };

            //pass custom data object as the context
            app.view.View.prototype._renderHtml.call(this, data);
        },

        /**
         * Returns array of fields data (from model), specified by array of fields metadata.
         * Returned array is like: [{field-label: field.value}, ...]
         * @param fields
         * @return {Array}
         */
        getFieldsDataArray: function (fields) {
            var view = this;
            var label, value, vardef, dataObj;
            return _.map(fields, function(field, index) {
                dataObj = {};
                value = view.model.get(field.name);
                vardef = app.metadata.getModule(view.module).fields[field.name];
                if (value) {
                    label = app.lang.get(
                        field.label ||
                        vardef.label ||
                        vardef.vname ||
                        field.name,
                        view.module);
                    dataObj[label] = value;
                    return dataObj;
                }
            });
        },

        /**
         * Generate array of objects of type: [{"Primary Address": { street: "1234 Vicente", city: "Sunnyvale", ...} }, ...]
         * this.addressFieldsGroups expected to be like: {"primary_address": [field1, field2, ...], ...}
         * @return {Array}
         */
        getAddresses: function () {
            var key, addressObj, valueObj, view = this;
            //iterate over address fields group
            return _.map(this.addressFieldsGroups, function (group, addressName) {
                valueObj = {};
                //iterate over fields in a group
                _.each(group, function(field, index) {
                    key = field.name;
                    key = key.replace(addressName + "_", "");           //remove prefix
                    valueObj[key] = view.model.get(field.name);         //save field value to the object
                });
                addressObj = {};
                addressName = app.lang.get(addressName, view.module);   //localize address name
                addressObj[addressName] = valueObj;                     //return address object to the mapped (result) array
                return addressObj;
            });
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
                if (value) data[field.name] = value;
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
        },

        /**
         * Check if array or object has at least one non-falsy item
         * @param data
         * @return {Boolean}
         */
        isDataDefined: function (data) {
            return _.any(data);
        }
    });

})(SUGAR.App);