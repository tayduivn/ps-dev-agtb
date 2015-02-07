({
    plugins: ['ErrorDecoration'],

    events: {
        'click [data-action=submit]': 'submit',
        'click [data-action=close]': 'close'
    },

    /**
     * @inheritDoc
     *
     * During initialize we listen to model validation and if it is valid we
     * {@link #send} the Feedback.
     */
    initialize: function(options) {
        options.model = app.data.createBean('Feedbacks');
        var fieldsMeta = _.flatten(_.pluck(options.meta.panels, 'fields'));
        options.model.fields = {};
        _.each(fieldsMeta, function(field) {
            options.model.fields[field.name] = field;
        });
        this._super('initialize', [options]);
        this.context.set('skipFetch', true);

        this.model.on('error:validation', function() {
            app.alert.show('send_feedback', {
                level: 'error',
                messages: app.lang.get('LBL_FEEDBACK_SEND_ERROR', this.module)
            });
        }, this);

        this.model.on('validation:success', this.send, this);

        // TODO Once the view renders the button, this is no longer needed
        this.button = $(options.button);

        this.button.on('show.bs.popover hide.bs.popover', _.bind(function(evt) {
            this.trigger(evt.type, this, evt.type === 'show' ? true : false);
        }, this));
    },

    /**
     * Initializes the popover plugin for the button given.
     *
     * @param {jQuery} button the jQuery button;
     * @private
     */
    _initPopover: function(button) {
        button.popover({
            title: app.lang.get('LBL_FEEDBACK', this.module),
            content: _.bind(function() { return this.$el; }, this),
            html: true,
            placement: 'top',
            trigger: 'manual',
            template: '<div class="popover feedback"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
        }).on('click.' + this.cid, _.bind(function() {
            this.toggle();
        }, this));
    },

    /**
     * @inheritDoc
     *
     * After `render` generate the popup with the content of this view using
     * the popover plugin.
     */
    _render: function() {
        this.button.popover('destroy');
        this._super('_render');
        this._initPopover(this.button);
    },

    /**
     * Close button on the feedback view is pressed.
     *
     * @param {Event} evt The `click` event.
     */
    close: function() {
        this.toggle(false);
    },

    /**
     * Toggle this view (show/hide) and allow force option.
     *
     * @param {boolean} [show] `true` to show, `false` to hide, `undefined`
     *   toggles the current state.
     */
    toggle: function(show) {
        if (_.isUndefined(show)) {
            this.button.popover('toggle');
            return;
        }
        this.button.popover(show ? 'show' : 'hide');
    },

    /**
     * @inheritDoc
     * During dispose destroy the popover.
     */
    _dispose: function() {
        if (this.button) {
            this.button.off('click.' + this.cid);
            this.button.popover('destroy');
        }
    },

    /**
     * Submit the form
     */
    submit: function() {
        this.model.doValidate();
    },

    /**
     * Sends the Feedback to google doc page.
     *
     * Populate the rest of the data into the model from different sources of
     * the app.
     */
    send: function() {

        var emails = app.user.get('email');
        var primary = _.filter(emails, function(email) { return email.primary_address; });
        var email = _.first(primary || _.first(emails)).email_address;

        this.model.set({
            name: app.user.get('full_name'),
            username: app.user.get('user_name'),
            email: email,
            phone: 'n/a',
            timezone: app.user.getPreference('timezone'),
            account_type: app.user.get('type'),
            role: app.user.get('roles').join(', ') || 'n/a',
            feedback_app_path: window.location.href,
            feedback_user_browser: navigator.userAgent + ' (' + navigator.language + ')',
            feedback_user_os: navigator.platform,
            feedback_sugar_version: _.toArray(_.pick(app.metadata.getServerInfo(), 'product_name', 'version')).join(' ')
        });

        var post_url = 'https://docs.google.com/forms/d/1iIdfeWma_OUUkaP-wSojZW2GelaxMOBgDq05A8PGHY8/formResponse';

        $.ajax({
            url: post_url,
            type: 'POST',
            data: {
                'entry.720195324': this.model.get('name'),
                'entry.767714183': this.model.get('email'),
                'entry.99686462': this.model.get('phone'),
                'entry.860101942': this.model.get('username'),
                'entry.98009013': this.model.get('account_type'),
                'entry.1589366838': this.model.get('timezone'),
                'entry.762467312': this.model.get('role'),
                'entry.968140953': this.model.get('feedback_text'),
                'entry.944905780': this.model.get('feedback_app_path'),
                'entry.1750203592': this.model.get('feedback_user_browser'),
                'entry.1115361778': this.model.get('feedback_user_os'),
                'entry.1700062722': this.model.get('feedback_csat'),
                'entry.1926759955': this.model.get('feedback_sugar_version')
            },
            dataType: 'script',
            crossDomain: true,
            cache: false,
            context: this,
            success: function() {
                app.alert.show('send_feedback', {
                    level: 'success',
                    messages: app.lang.get('LBL_FEEDBACK_SENT', this.module),
                    autoClose: true
                });
                this.model.unset('feedback_text');
                this.model.unset('feedback_csat');
                this.toggle(false);
            }
        });
    }
})
