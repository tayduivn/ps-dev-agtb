({
    events: {
        'mouseenter .invitee-item': 'showInviteButton',
        'mouseleave .invitee-item': 'hideInviteButton',
        'click .invite-user': 'inviteUser'
    },

    initialize: function(options) {
        app.events.on("app:login:success", this.render, this);
        app.events.on("app:logout", this.render, this);
        app.view.View.prototype.initialize.call(this, options);

        var self = this;
            this.inviteCollection = app.data.createBeanCollection();

        app.api.call('read', app.api.buildURL('google/recommend'), null, {
            success: function(obj) {
                self.data = obj.invites;
                _.each(obj.invites, function(val){
                    var model = app.data.createBean();
                    model.set(val);

                    if( !model.get("image_uri") ) {
                        model.set("image_uri", app.config.siteUrl + "/styleguide/assets/img/profile.png");
                    }
                    self.inviteCollection.add(model);
                });
                self.loaded = true;

                self.render();
                self.bindDataChange();
            }
        });
    },

    inviteUser: function(e) {
        var self = this,
            emailAddr = this.$(e.currentTarget).data()['email'];

        app.api.call('create', app.api.buildURL('summer/invite'), {email: emailAddr}, {
            success: function() {
                app.alert.show('invited',
                    {
                        level: 'info',
                        title: app.lang.getAppString('LBL_INVITED'),
                        messages: app.lang.getAppString('LBL_INSTANCE_INVITE_SENT') + ': ' + emailAddr,
                        autoClose: true
                    });

                var model = _.first(self.inviteCollection.where({email: emailAddr}));
                if( model ) {
                    self.inviteCollection.remove(model);
                }
            }
        });
    },

    bindDataChange: function() {
        if( this.inviteCollection ) {
            this.inviteCollection.on("remove", this.render, this);
            this.inviteCollection.on("reset", this.render, this);
        }
    },

    showInviteButton: function(e) {
        this.$(e.currentTarget).find(".invite-user").show();
    },

    hideInviteButton: function(e) {
        this.$(e.currentTarget).find(".invite-user").hide();
    }
})
