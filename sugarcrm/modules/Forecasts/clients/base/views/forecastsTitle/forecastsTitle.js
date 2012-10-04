({
    fullName:'',

    initialize : function(options) {
        app.view.View.prototype.initialize.call(this, options);

        // grab current app user model locally
        var currentUser = app.user;
        this.fullName = currentUser.get('full_name');
    },

    bindDataChange: function() {
        var self = this;
        app.view.View.prototype.bindDataChange.call(this);

        this.context.forecasts.on('change:selectedUser', function(context, selectedUser) {
            self.fullName = selectedUser.full_name;
            this.render();
        }, this);
    }
})