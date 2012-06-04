({

/**
 * View that displays search results.
 * @class View.Views.ProfileView
 * @alias SUGAR.App.layout.ProfileView
 * @extends View.View
 */
    events: {},
    render: function() {
        var self = this, data, attributes;

        // TODO: Stubbing for now .. prolly will be passed in later ;=)
        attributes = {id: "117e315d-ea46-b2d8-efd7-4fcceec2d44d"};

        app.alert.show('fetch_contact_record', {level:'process', title:'Loading'});
        app.api.records("read", "Contacts", attributes, null, {
            success: function(data) {
                app.alert.dismiss('fetch_contact_record');
                if(data) {
                    self.updateModel(data);
                    app.view.View.prototype.render.call(self);
                } 
                self.renderSubnav(data);
            },
            error: function(xhr, error) {
                app.alert.dismiss('fetch_contact_record');
                app.error.handleHTTPError(xhr, error, self);
            }
        });
    },
    /**
     * Updates model for this contact.
     */
    updateModel: function(data) {
        var self = this, model;
        model = self.context.get('model');
        model.set(data, {silent: true});
    },
    /**
     * Renders subnav based on search message appropriate for query term.
     */
    renderSubnav: function(data) {
        var self = this, fullName = '';
        if (app.additionalComponents.subnav) {
            if(data) {
                // TODO: This is just horrible stubbing temp stuff here ;=)
                fullName = data.name ? data.full_name : data.first_name +' '+data.last_name;
                app.additionalComponents.subnav.renderStatic(fullName, 'Edit', self.onEditContactClicked);
            }
        }
    },
    onEditContactClicked: function(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        alert("TODO: Go to edit contact view...");
    }

})

