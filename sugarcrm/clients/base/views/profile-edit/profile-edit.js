/**
 * View that displays edit view on a profile. 
 * @class View.Views.ProfileEditView
 * @alias SUGAR.App.layout.ProfileEditView
 * @extends View.View
 */
({
    events: {
        'click [name=save_button]': 'saveModel' // bottom save button
    },
    initialize: function(options) {
        this.options.meta = app.metadata.getView('Contacts', 'edit');
        app.view.View.prototype.initialize.call(this, options);
        this.template = app.template.get("edit");
        this.fallbackFieldTemplate = "edit"; // will use edit sugar fields
    },
    render: function() {
        var self = this, currentUserAttributes;

        if(app.user.isSupportPortalUser()) {
            currentUserAttributes = {id: app.user.get('id')}; 
            self.loadCurrentUser(currentUserAttributes, function(data) {
                if(data) {
                    self.setModelAndContext(data);
                    app.view.View.prototype.render.call(self);
                    self.renderSubnav(data);
                } 
            });
        } else {
            app.router.goBack();
            app.alert.show('not_portal_enabled_user', {level:'error', title:'Page Not Available', messages: "We're Sorry, but this feature is not available at this time.", autoClose: true});
        }
    },
    loadCurrentUser: function(currentUserAttributes, cb) {
        var self = this;
        app.alert.show('fetch_edit_contact_record', {level:'process', title:'Loading'});
        app.api.records("read", "Contacts", currentUserAttributes, null, {
            success: function(data) {
                app.alert.dismiss('fetch_edit_contact_record');
                cb(data);
            },
            error: function(xhr, error) {
                app.alert.dismiss('fetch_edit_contact_record');
                app.error.handleHttpError(xhr, error, self);
            }
        });
    },
    renderSubnav: function(data) {
        var self = this, fullName = '', subnavModel = null;
        if (self.context.get('subnavModel')) {
            fullName = data.name ? data.full_name : data.first_name +' '+data.last_name;
            self.context.get('subnavModel').set({
                'title': fullName,
                'meta': self.meta
            });
            
            // Bypass subnav click handler
            $('.save-profile').on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                self.saveModel();
            });
        }
    },
    setModelAndContext: function(data) {
        this.model = app.data.createBean("Contacts", data);
        this.context.set({
            'model': this.model,
            'module': 'Contacts'
        });
    },
    saveModel: function() {
        var self = this, options;
        app.alert.show('save_profile_edit_view', {level: 'process', title: 'Saving'});
        options = {
            success: function() {
                app.alert.dismiss('save_profile_edit_view');
                app.router.navigate('profile', {trigger: true});
            },
            error: function(xhr, textStatus, errorThrown) {
                app.alert.dismiss('save_profile_edit_view');
                app.error.handleHttpError(xhr, textStatus);
            },
            fieldsToValidate: self.getFields(this.model.module)
        };
        self.model.save(null, options);
    }

})
