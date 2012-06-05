/**
 * View that displays edit view on a profile. 
 * @class View.Views.ProfileEditView
 * @alias SUGAR.App.layout.ProfileEditView
 * @extends View.View
 */
({
    events: {
        'click [name=save_button]': 'saveModel'
    },
    initialize: function(options) {
        this.options.meta = this._meta;
        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = "edit"; // will use edit sugar fields
    },
    render: function() {
        var self = this, data, currentUserAttributes;

// -------- TODO: Stubbing for now .. prolly will be passed in later ;=)
        currentUserAttributes = {id: "1275a382-5f3b-37d6-3f3d-4fce979599ea"};

        app.alert.show('fetch_edit_contact_record', {level:'process', title:'Loading'});
        app.api.records("read", "Contacts", currentUserAttributes, null, {
            success: function(data) {
                app.alert.dismiss('fetch_edit_contact_record');
                if(data) {
                    self.setModel(data);
                    app.view.View.prototype.render.call(self);
                } 
                self.renderSubnav(data);
            },
            error: function(xhr, error) {
                app.alert.dismiss('fetch_edit_contact_record');
                app.error.handleHTTPError(xhr, error, self);
            }
        });
    },
    renderSubnav: function(data) {
        var self = this, fullName = '', subnavModel = null;
        if (self.context.get('subnavModel')) {
            fullName = data.name ? data.full_name : data.first_name +' '+data.last_name;
            self.context.get('subnavModel').set({
                'title': fullName,
                'meta': {
                    "buttons": self._meta.buttons 
                }
            });
            // Bypass subnav click handler
            $('.save-profile').on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                self.saveModel();
            });
        }
    },
    setModel: function(data) {
        var self = this, model;
        model = self.context.get('model');
        model.set(data, {silent: true});
        model.module = 'Contacts';
    },
    saveModel: function() {
        var self = this;
        alert("TODO: Create an api.contact in sugarapi.js");
        app.alert.show('save_profile_edit_view', {level: 'process', title: 'Saving'});

// TODO: ---- Create an api.contact since this isn't going to behave like the rest 
// of our modules/records .. will still need to utilize bean isValid, etc.
        this.model.save(null, {
            success: function() {
                app.alert.dismiss('save_profile_edit_view');
                alert("TODO here...");
                //self.app.router.navigate('profile', {trigger:true});
            },
            fieldsToValidate: this.getFields(this.model.module)
        });
    },
    // I assume this will eventually be in clients/base/views/profile-edit/profile-edit.php
    _meta: {
            "buttons": [
                {
                    "name": "save_button",
                    "type": "button",
                    "label": "Save",
                    "value": "save",
                    "class": "save-profile",
                    "primary": true
                },
                {
                    "name": "cancel_button",
                    "type": "button",
                    "label": "Cancel",
                    "value": "cancel",
                    "events": {
                        "click": "function(){ window.history.back(); }"
                    },
                    "primary": false
                }
            ],
            "templateMeta": {
                "maxColumns": "2",
                "widths": [
                    {
                        "label": "10",
                        "field": "30"
                    },
                    {
                        "label": "10",
                        "field": "30"
                    }
                ],
                "formId": "ProfileEditView",
                "formName": "ProfileEditView",
                "useTabs": false
            },
            "panels": [
                {
                    "label": "default",
                    "fields": [
                        {
                            // TODO: Add appropriate LBL to appstrings 
                            "label": "First name",
                            "name": "first_name",
                            "colspan": 2,
                            "type": "text"
                        },
                        {
                            "name": "last_name",
                            // TODO: Add appropriate LBL to appstrings 
                            "label": "Last name",
                            "colspan": 2,
                            "type": "text"
                        },
                        {
                            "label": "LBL_ACCOUNT",
                            "name": "account_name",
                            "colspan": 2,
                            "type": "text"
                        },
                        {
                            "name": "title",
                            // TODO: Add appropriate LBL to appstrings 
                            "label": "Title", 
                            "colspan": 2,
                            "type": "text"
                        },
                        {
                            "label": "LBL_PRIMARY_ADDRESS_STREET",
                            "name": "primary_address_street",
                            "colspan": 2,
                            "type": "text"
                        },
                        {
                            "label": "LBL_PRIMARY_ADDRESS_CITY",
                            "name": "primary_address_city",
                            "colspan": 2,
                            "type": "text"
                        },
                        {
                            "label": "LBL_PRIMARY_ADDRESS_STATE",
                            "name": "primary_address_state",
                            "colspan": 2,
                            "type": "text"
                        },
                        {
                            "label": "LBL_PRIMARY_ADDRESS_POSTALCODE",
                            "name": "primary_address_postalcode",
                            "colspan": 2,
                            "type": "text"
                        },
                        {
                            "name": "phone_home",
                            // TODO: Add appropriate LBL to appstrings for work/home/mobile phones - LBL_LIST_PHONE == 'Phone'
                            "label": "Home phone",
                            "colspan": 2,
                            "type": "text"
                        },
                        {
                            "name": "phone_work",
                            "label": "Work phone",
                            "colspan": 2,
                            "type": "text"
                        },
                        {
                            "name": "phone_mobile",
                            "label": "Mobile phone",
                            "colspan": 2,
                            "type": "text"
                        },
                    ]
                }
            ]
        }

})
