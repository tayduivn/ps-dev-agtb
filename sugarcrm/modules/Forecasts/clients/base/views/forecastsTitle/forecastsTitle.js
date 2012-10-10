({
    fullName:'',

    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);

        // grab current app user model locally
        this.setFullNameFromUser(app.user);
    },

    setFullNameFromUser:function (user) {
        if(_.isFunction(user.get)) {
            this.fullName = user.get('full_name');
        } else {
            this.fullName = user.full_name;
        }
    },

    bindDataChange:function () {
        var self = this;
        //app.view.View.prototype.bindDataChange.call(this);
        this.context.forecasts.on('change:selectedUser', function (context, selectedUser) {
            this.setFullNameFromUser(selectedUser);
            this.render();
        }, this);
    }
})