({
        extendsFrom: app.view.AlertView,
        className: '', //override default class

        events:{
            'click .cancel': 'cancel',
            'click .confirm': 'confirm'
        },

        LEVEL: {
            PROCESS: 'process',
            SUCCESS: 'success',
            WARNING: 'warning',
            INFO: 'info',
            ERROR: 'error',
            CONFIRMATION: 'confirmation'
        },

        initialize: function(options) {
            this.onConfirm = options.onConfirm;
            this.alertLevel = options.level;
        },

        render: function(options) {
            if(_.isUndefined(options)) {
                return this;
            }
            var template = this.getAlertTemplate(options.level, options.messages, options.title);
            this.$el.html(template);

            // set confirmation dialog as a modal
            if (options.level === this.LEVEL.CONFIRMATION) {
                this.$('.modal').modal({
                    'backdrop': 'static',
                    'show': false
                });
            }

            this.show(options.level);
        },

        show: function(level) {
            if (level === this.LEVEL.CONFIRMATION) {
                this.$('.modal').modal('show');
            } else {
                this.$el.show();
            }
        },

        close: function() {
            if (this.alertLevel === this.LEVEL.CONFIRMATION) {
                this.$('.modal').modal('hide');
            }
            this.$el.fadeOut().remove();
        },

        cancel: function() {
            this.$('.close').click(); //need to click close to call app.alert.dismiss()
        },

        confirm: function() {
            if (_.isFunction(this.onConfirm)) {
                this.onConfirm();
            }
            this.cancel();
        },

        /**
         * Get the HTML string for alert given alert level
         * @param level
         * @param messages
         * @param title (optional)
         * @return {String}
         */
        getAlertTemplate: function(level, messages, title) {
            var template,
                alertClasses = this.getAlertClasses(level),
                title = title ? title : this.getDefaultTitle(level);

            switch (level) {
                case this.LEVEL.PROCESS:
                    template = '<div class="alert alert-process">' +
                        '<strong>Loading</strong>' +
                        '<div class="loading">' +
                        '<span class="l1"></span><span class="l2"></span><span class="l3"></span>' +
                        '</div>' +
                        '</div>';
                    break;
                case this.LEVEL.SUCCESS:
                case this.LEVEL.WARNING:
                case this.LEVEL.INFO:
                case this.LEVEL.ERROR:
                    template = '<div class="alert {{alertClass}} alert-block">' +
                        '<a class="close">x</a>' +
                        '{{#if title}}<strong>{{title}}</strong>{{/if}}' +
                        ' {{#each messages}}{{{this}}}{{/each}}' +
                        '</div>';
                    break;
                case this.LEVEL.CONFIRMATION:
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
         * Get CSS classes given alert level
         * @param level
         * @return {String}
         */
        getAlertClasses: function(level) {
            switch (level) {
                case this.LEVEL.PROCESS:
                    return 'alert-process';
                case this.LEVEL.SUCCESS:
                    return 'alert-success';
                case this.LEVEL.WARNING:
                    return 'alert-warning';
                case this.LEVEL.INFO:
                    return 'alert-info';
                case this.LEVEL.ERROR:
                    return 'alert-danger';
                case this.LEVEL.CONFIRMATION:
                    return 'alert-warning span4';
                default:
                    return '';
            }
        },

        /**
         * Get the default title given alert level
         * @param level
         * @return {String}
         */
        getDefaultTitle: function(level) {
            switch (level) {
                case this.LEVEL.PROCESS:
                    return '';
                case this.LEVEL.SUCCESS:
                    return 'Success!';
                case this.LEVEL.WARNING:
                    return 'Warning!';
                case this.LEVEL.INFO:
                    return 'Notice!';
                case this.LEVEL.ERROR:
                    return 'Error!';
                case this.LEVEL.CONFIRMATION:
                    return 'Notice!';
                default:
                    return '';
            }
        },

    bindDataChange : function() {}
})