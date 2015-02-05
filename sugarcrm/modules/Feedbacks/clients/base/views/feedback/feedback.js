({
    events: {
        'click [data-action=submit]': 'send',
        'click [data-action=close]': 'close'
    },
    initialize: function(options) {
        options.model = app.data.createBean('Feedbacks');
        var fieldsMeta = _.flatten(_.pluck(options.meta.panels, 'fields'));
        options.model.fields = {};
        _.each(fieldsMeta, function(field) {
            options.model.fields[field.name] = field;
        });
        this._super('initialize', [options]);
        this.context.set('skipFetch', true);
    },
    _render: function() {
        var email = app.user.get('email');
        if (_.first(email)) {
            email = email[0].email_address;
        }
        this.model.set({
            name: app.user.get('full_name'),
            username: app.user.get('user_name'),
            email: email,
            phone: 'n/a',
            timezone: app.user.getPreference('timezone'),
            account_type: app.user.get('type'),
            role: app.user.get('roles').join(', ') || 'n/a',
            feedback_app_path: window.location.href,
            feedback_user_browser: navigator.userAgent + " (" + navigator.language + ")",
            feedback_user_os: navigator.platform,
            //feedback_sugar_version: app.metadata.getServerInfo().product_name + " " + app.metadata.getServerInfo().version + " " + app.metadata.getServerInfo().fts.type,
            feedback_sugar_version: _.toArray(_.pick(app.metadata.getServerInfo(), 'product_name', 'version')).join(' '),
        });
        this._super('_render');
    },
    close: function() {
        if (this.layout.$popover) {
            this.layout.$popover.popover('hide');
            this.layout.trigger('feedback:close');
        }
    },
    send: function() {
        var self = this,
            post_url = 'https://docs.google.com/forms/d/1iIdfeWma_OUUkaP-wSojZW2GelaxMOBgDq05A8PGHY8/formResponse';
        if(this.model.get('feedback_text')==undefined || this.model.get('feedback_csat')==undefined) {
            app.alert.show('send_feedback', {
                level: 'error',
                messages: app.lang.get('LBL_FEEDBACK_SEND_ERROR', 'Feedbacks'),
                autoClose: false
            });
            return false;
        }
        $.ajax({
            url: post_url,
            type:"POST",
            data:{
                "entry.720195324": this.model.get('name'),
                "entry.767714183": this.model.get('email'),
                "entry.99686462": this.model.get('phone'),
                "entry.860101942": this.model.get('username'),
                "entry.98009013": this.model.get('account_type'),
                "entry.1589366838": this.model.get('timezone'),
                "entry.762467312": this.model.get('role'),
                "entry.968140953": this.model.get('feedback_text'),
                "entry.944905780": this.model.get('feedback_app_path'),
                "entry.1750203592": this.model.get('feedback_user_browser'),
                "entry.1115361778": this.model.get('feedback_user_os'),
                "entry.98009013": this.model.get('account_type'),
                "entry.1700062722": this.model.get('feedback_csat'),
                "entry.1926759955": this.model.get('feedback_sugar_version'),
            },
            dataType: "script",
            crossDomain: true,
            cache: false,
            success: function() {
                app.alert.show('send_feedback', {
                    level: 'success',
                    messages: app.lang.get('LBL_FEEDBACK_SENT', 'Feedbacks'),
                    autoClose: true
                });
                self.model.unset('feedback_text');
                self.model.unset('feedback_csat');
                self.closeFeedback();
            }
        });
    }
})
