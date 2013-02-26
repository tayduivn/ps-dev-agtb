/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    /**
     * extendsFrom: This needs to be app.view.AlertView since it's extending a Sidecar specific view class.  This is a
     * special case, as the normal method is for it to be a string.
     */
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
            var template = this.getAlertTemplate(options.level, options.messages, options.title, options.showCancel);
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
         * @param showCancel (optional) boolean flag
         * @return {String}
         */
        getAlertTemplate: function(level, messages, title, showCancel) {
            var template,
                alertClasses = this.getAlertClasses(level),
                title = title ? title : this.getDefaultTitle(level),
                showCancel = showCancel ? showCancel : true;

            switch (level) {
                case this.LEVEL.PROCESS:
                    template = '<div class="alert alert-process">' +
                        '<strong>{{title}}</strong>' +
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
                        '{{#if showCancel}}<a class="btn cancel">' + app.lang.get('LBL_CANCEL_BUTTON_LABEL') + '</a>{{/if}}' +
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
                    return app.lang.getAppString('LBL_ALERT_TITLE_LOADING');
                case this.LEVEL.SUCCESS:
                    return app.lang.getAppString('LBL_ALERT_TITLE_SUCCESS');
                case this.LEVEL.WARNING:
                    return app.lang.getAppString('LBL_ALERT_TITLE_WARNING');
                case this.LEVEL.INFO:
                    return app.lang.getAppString('LBL_ALERT_TITLE_NOTICE');
                case this.LEVEL.ERROR:
                    return app.lang.getAppString('LBL_ALERT_TITLE_ERROR');
                case this.LEVEL.CONFIRMATION:
                    return app.lang.getAppString('LBL_ALERT_TITLE_WARNING');
                default:
                    return '';
            }
        },

    bindDataChange : function() {}
})
