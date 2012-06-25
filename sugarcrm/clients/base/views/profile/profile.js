({

/**
 * View that displays search results.
 * @class View.Views.ProfileView
 * @alias SUGAR.App.layout.ProfileView
 * @extends View.View
 */
    events: {},
    initialize: function(options) {
        this.options.meta   = app.metadata.getView('Contacts', 'detail');
        app.view.View.prototype.initialize.call(this, options);
        this.template = app.template.get("detail");
        this.fallbackFieldTemplate = "detail"; // will use detail sugar fields
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
        app.alert.show('fetch_contact_record', {level:'process', title:'Loading'});
        app.api.records("read", "Contacts", currentUserAttributes, null, {
            success: function(data) {
                app.alert.dismiss('fetch_contact_record');
                cb(data);
            },
            error: function(xhr, error) {
                app.alert.dismiss('fetch_contact_record');
                app.error.handleHttpError(xhr, error, self);
            }
        });
    },
    /**
     * Updates model for this contact.
     */
    setModelAndContext: function(data) {
        this.model = app.data.createBean("Contacts", data);
        this.context.set({
            'model': this.model,
            'module': 'Contacts'
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
        }
    }
})

