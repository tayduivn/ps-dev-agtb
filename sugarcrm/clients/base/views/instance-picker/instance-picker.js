({
    events: {
        'click .invite-button': 'invite',
        'click .instances-container': 'persistMenu',
        'click .instance': 'selectInstance',
        'mouseenter .instance': 'showTooltip',
        'mouseleave .instance': 'hideTooltip'
    },
    tagName: "span",
    initialize: function(options){
        var self = this;
        app.events.on("app:login:success", this.render, this);
        app.events.on("app:logout", this.render, this);
        app.view.View.prototype.initialize.call(this, options);
        app.events.on("app:sync:complete", function() {
            self.getData();
        }, this);
    },
    _renderHtml: function() {
        this.isAuthenticated = app.api.isAuthenticated();
        app.view.View.prototype._renderHtml.call(this);
    },
    getData: function() {
        var self = this;
            this.currInstanceID = app.user.get('instance_id');
        app.api.call('read', app.api.buildURL('summer/office'), null, {
            success: function(o) {
                self.collections = o;
                self.render();
            }
        });
    },
    invite: function() {
        var self = this,
            emailAddr = this.$(".invitee-input").val();

        if( !emailAddr ) {
            return;
        }
        app.api.call('create', app.api.buildURL('summer/invite'), {email: emailAddr}, {
            success: function() {
                self.$(".invitee-input").val('');
                app.alert.show('invited',
                    {level: 'info', title: app.lang.getAppString('LBL_INVITED'),
                        messages: app.lang.getAppString('LBL_INSTANCE_INVITE_SENT') + ': ' + emailAddr, autoClose: true});
            },
            error: function(o) {
                app.alert.show('invalid_parameter',
                    {level: 'error', title: app.lang.getAppString('LBL_EMAIL_INVALID'),
                        messages: o.message, autoClose: true});
            }
        });
    },
    selectInstance: function(e) {
        // toString since app.user.get('instance_id') returns a string
        var id = this.$(e.currentTarget).data('id').toString();

        if( id === this.currInstanceID ) {
            app.alert.show('already_there',
                {level: 'info', title: app.lang.getAppString('LBL_INSTANCE_IN_USE'),
                    messages: app.lang.getAppString('LBL_INSTANCE_ACTIVE'), autoClose: true});
            return;
        }
        app.api.call('create', app.api.buildURL('summer/logout'), null, {
            success: function() {
                app.api.call('read', 'splash/rest/instances/' + id, null, {
                    success: function(o) {
                        if( o.error ) {
                            app.alert.show('switch_failed',
                                {level: 'error', title:'Failed',
                                    messages: app.lang.getAppString('LBL_INSTANCE_SWITCH_FAILED') + ': ' + o.error, autoClose: false});
                            return;
                        }
                        if( o.url ) {
                            window.location.href = o.url;
                        }
                    }
                });
            }
        });
    },
    persistMenu: function(e) {
        // This will prevent the dropup menu from closing when clicking anywhere on it
        e.stopPropagation();
    },
    showTooltip: function(e) {
        this.$(e.currentTarget).tooltip("show");
        //TODO: Update this when z-indexes are fixed in styleguide
        if( $(".tooltip").css("z-index") < 1030) {
            $(".tooltip").css("z-index", 1030);
        }
    },
    hideTooltip: function(e) {
        this.$(e.currentTarget).tooltip("hide");
    }
})