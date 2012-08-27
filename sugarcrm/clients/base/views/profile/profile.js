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
            app.alert.show('not_portal_enabled_user', {level:'error', title: app.lang.getAppString('LBL_PORTAL_PAGE_NOT_AVAIL'), messages: app.lang.getAppString('LBL_PORTAL_NOT_ENABLED_MSG'), autoClose: true});
        }
    },
    loadCurrentUser: function(currentUserAttributes, cb) {
        var self = this;
        app.alert.show('fetch_contact_record', {level:'process', title:app.lang.getAppString('LBL_PORTAL_LOADING')});
        app.api.records("read", "Contacts", currentUserAttributes, null, {
            success: function(data) {
                app.alert.dismiss('fetch_contact_record');
                cb(data);
                self.$('.modelNotLoaded').hide();
                self.$('.modelLoaded').show();
            },
            error: function(error) {
                app.alert.dismiss('fetch_contact_record');
                app.error.handleHttpError(error, self);
            }
        });
    },
    /**
     * Updates model for this contact.
     */
    setModelAndContext: function(data) {
        this.model = app.data.createBean("Contacts", data);
        this.model.isNotEmpty = true;
        this.context.set({
            'model': this.model,
            'module': 'Contacts',
            _dataFetched: true
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

