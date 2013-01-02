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
(function(app) {
    /**
     * View that displays errors.
     * @class View.Views.AlertView
     * @extends View.View
     */
    app.view.views.AlertView = app.view.View.extend({

        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);
        },
        /**
         * Displays an alert message and returns alert instance.
         * @param {Object} options
         * @return {Backbone.View} Alert instance
         * @method
         */
        show: function(options) {
            var level, title, msg, thisAlert, autoClose, alertClass, ctx, AlertView;
            if (!options) {
                return false;
            }

            level     = options.level ? options.level : 'info';
            title     = options.title ? options.title : null;
            msg       = (_.isArray(options.messages)) ? options.messages : [options.messages] ;
            autoClose = options.autoClose ? options.autoClose : false;

            // "process" is the loading indicator .. I didn't name it ;=)
            alertClass = (level === "process" || level === "success" || level === "warning" || level === "info" || level === "error") ? "alert-" + level : "";

            ctx = {
                alertClass:  alertClass,
                title:       title,
                messages:    msg,
                autoClose:   autoClose
            };
            try {
                AlertView = Backbone.View.extend({
                    events : {
                        'click .close' : 'close'
                    },
                    template: "<div class=\"alert {{alertClass}} alert-block {{#if autoClose}}timeten{{/if}}\">" +
                        "<a class=\"close\" data-dismiss=\"alert\">x</a>{{#if title}}<strong>{{str title}}</strong>{{/if}}" +
                        "{{#each messages}} <span>{{str this}}</span><br>{{/each}}</div>",
                    loadingTemplate: "<div class=\"alert {{alertClass}}\">" +
                        "<strong>{{str title}}</strong>\n" +
                        "<div class=\"loading\"><span class=\"l1\"></span><span class=\"l2\"></span><span class=\"l3\"></span></div>" +
                        "<a class=\"close\" data-dismiss=\"alert\">x</a></div>",
                    initialize: function() {
                        this.render();
                    },
                    close: function() {
                        this.$el.remove();
                    },
                    render: function() {
                        var tpl = (level === 'process') ?
                            Handlebars.compile(this.loadingTemplate) :
                            Handlebars.compile(this.template);

                        this.$el.html(tpl(ctx));
                    }
                });
                thisAlert = new AlertView();
                this.$el.prepend(thisAlert.el).show();

                if(autoClose) {
                    setTimeout(function(){$('.timeten').fadeOut().remove();},9000);
                }
                return thisAlert;

            } catch (e) {
                app.logger.error("Failed to render '" + this.name + "' view.\n" + e.message);
                return null;
                // TODO: trigger app event to render an error message
            }
        }
    });
})(SUGAR.App);
