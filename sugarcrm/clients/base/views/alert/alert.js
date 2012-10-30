({
    type: {
        PROCESS: 'process',
        SUCCESS: 'success',
        WARNING: 'warning',
        INFO: 'info',
        ERROR: 'error',
        CONFIRMATION: 'confirmation'
    },

    /**
     * Displays an alert message and returns alert instance.
     * @param {Object} options
     * @return {Backbone.View} Alert instance
     * @method
     */
    show: function (options) {
        var level, title, msg, thisAlert, autoClose, autoCloseAfter;
        if (!options) {
            return false;
        }

        level = options.level ? options.level : 'info';
        title = options.title ? options.title : null;
        msg = (_.isString(options.messages)) ? [options.messages] : options.messages;
        autoClose = options.autoClose ? options.autoClose : false;
        autoCloseAfter = options.autoCloseAfter ? options.autoCloseAfter : 9000;

        try {
            thisAlert = this.getAlerts(level, title, msg, options.onConfirm);
            this.$el.prepend(thisAlert.el);
            thisAlert.show();

            if (autoClose) {
                setTimeout(function () {
                    thisAlert.close();
                }, autoCloseAfter);
            }
            return thisAlert;

        } catch (e) {
            app.logger.error("Failed to render '" + this.name + "' view.\n" + e.message);
            return null;
            // TODO: trigger app event to render an error message
        }
    },

    /**
     * Get the alert view given alert type.
     * @param type
     * @param title
     * @param messages
     * @param onConfirm
     * @return {Object}
     */
    getAlerts: function(type, title, messages, onConfirm) {
        var self = this,
            Alert =  Backbone.View.extend({
                events:{
                    'click .close': 'close',
                    'click .cancel': 'close',
                    'click .confirm': 'confirm'
                },
                initialize: function() {
                    this.render();
                },
                close: function() {
                    if (type === self.type.CONFIRMATION) {
                        this.$('.modal').modal('hide');
                    }
                    this.$el.fadeOut().remove();
                },
                render: function() {
                    var template = self.getAlertTemplate(type, messages, title);
                    this.$el.html(template);

                    if (type === self.type.CONFIRMATION) {
                        this.$('.modal').modal({
                            'backdrop': 'static',
                            'show': false
                        });
                    }
                },
                show: function() {
                    if (type === self.type.CONFIRMATION) {
                        this.$('.modal').modal('show');
                    } else {
                        this.$el.show();
                    }
                },
                confirm: function() {
                    if (_.isFunction(onConfirm)) {
                        onConfirm();
                    }
                    this.close();
                }
            }),
            alert = new Alert();

        return alert;
    },

    /**
     * Get the HTML string for alert given alert type
     * @param type
     * @param messages
     * @param title (optional)
     * @return {String}
     */
    getAlertTemplate: function(type, messages, title) {
        var template,
            alertClasses = this.getAlertClasses(type),
            title = title ? title : this.getDefaultTitle(type);

        switch (type) {
            case this.type.PROCESS:
                template = '<div class="alert alert-process">' +
                                '<strong>Loading</strong>' +
                                '<div class="loading">' +
                                    '<span class="l1"></span><span class="l2"></span><span class="l3"></span>' +
                                '</div>' +
                            '</div>';
                break;
            case this.type.SUCCESS:
            case this.type.WARNING:
            case this.type.INFO:
            case this.type.ERROR:
                template = '<div class="alert {{alertClass}} alert-block">' +
                                '<a class="close">x</a>' +
                                '{{#if title}}<strong>{{title}}</strong>{{/if}}' +
                                ' {{#each messages}}{{{this}}}{{/each}}' +
                            '</div>';
                break;
            case this.type.CONFIRMATION:
                template = '<div class="alert {{alertClass}} alert-block modal">' +
                                '<a class="close">×</a>' +
                                '{{#if title}}<strong>{{title}}</strong>{{/if}}' +
                                ' {{#each messages}}{{{this}}}{{/each}}' +
                                '<a class="btn cancel">' + app.lang.get('LBL_CANCEL_BUTTON_LABEL') + '</a>' +
                                '<a class="btn btn-primary pull-right confirm">' + app.lang.get('LBL_CONFIRM_BUTTON_LABEL') + '</a>' +
                            '</div>';
                break;
            default:
                template = '';
        }

        return Handlebars.compile(template)({
            alertClass: alertClasses,
            title: title,
            messages: messages
        });
    },

    /**
     * Get CSS classes given alert type
     * @param type
     * @return {String}
     */
    getAlertClasses: function(type) {
        switch (type) {
            case this.type.PROCESS:
                return 'alert-process';
            case this.type.SUCCESS:
                return 'alert-success';
            case this.type.WARNING:
                return 'alert-warning';
            case this.type.INFO:
                return 'alert-info';
            case this.type.ERROR:
                return 'alert-danger';
            case this.type.CONFIRMATION:
                return 'alert-warning span4';
            default:
                return '';
        }
    },

    /**
     * Get the default title given alert type
     * @param type
     * @return {String}
     */
    getDefaultTitle: function(type) {
        switch (type) {
            case this.type.PROCESS:
                return '';
            case this.type.SUCCESS:
                return 'Success!';
            case this.type.WARNING:
                return 'Warning!';
            case this.type.INFO:
                return 'Notice!';
            case this.type.ERROR:
                return 'Error!';
            case this.type.CONFIRMATION:
                return 'Notice!';
            default:
                return '';
        }
    }
})