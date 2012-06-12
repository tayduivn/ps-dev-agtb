({
    events: {},
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
    },
    render: function() {
        var self = this, attributes = {};
        if (!app.api.isAuthenticated()) return;

        if(this.context.get('errorType')) {
            attributes = this.getErrorAttributes();
            this.model.set(attributes);
        }
        app.view.View.prototype.render.call(this);
    },
    // TODO: Put magic in app strings.
    getErrorAttributes: function() {
        var attributes = {};
        if(this.context.get('errorType') ==='404') {
            attributes = {
                title: 'HTTP: 404 Not Found',
                type: '404',
                message: "We're sorry but the resource you asked for cannot be found."
            };
        } else if(this.context.get('errorType') ==='500') {
            attributes = {
                title: 'HTTP: 500 Internal Server Error',
                type: '500',
                message: "There was an error on the server. Please contact technical support."
            };
        } else {
            // TODO: Obviously, this won't work .. what to do in this case?
            attributes = {
                title: 'Unknown Error',
                type: 'Unknown',
                message: "Unknown error."
            };
        }
        return attributes;
    }
})
