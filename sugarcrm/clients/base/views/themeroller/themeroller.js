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
/**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    events: {
        "click [name=save_button]": "saveTheme",
        "click [name=refresh_button]": "loadTheme",
        "click [name=reset_button]": "toggleModal",
        "click #modal-confirm-reset #buttonYes": "resetTheme",
        "click #modal-confirm-reset #buttonNo": "toggleModal",
        "click #modal-confirm-reset .close": "toggleModal",
        "blur input": "previewTheme"
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.customTheme = "default";
        this.loadTheme();
    },
    _renderHtml: function() {
        if (this.lessVars && this.lessVars.rel && this.lessVars.rel.length > 0) {
            _.each(this.lessVars.rel, function(obj, key) {
                this.lessVars.rel[key].relname = this.lessVars.rel[key].value;
                this.lessVars.rel[key].relname = this.lessVars.rel[key].relname.replace('@', '');
            }, this);
        }

        app.view.View.prototype._renderHtml.call(this);
        _.each(this.$('.hexvar[rel=colorpicker]'), function(obj, key) {
            $(obj).blur(function() {
                $(this).parent().parent().find('.swatch-col').css('backgroundColor', $(this).val());
            });
        }, this);
        this.$('.hexvar[rel=colorpicker]').colorpicker();
        this.$('.rgbavar[rel=colorpicker]').colorpicker({format: 'rgba'});
    },
    loadTheme: function() {
        var params = {
            platform: app.config.platform,
            themeName: this.customTheme
        };
        var url = app.api.buildURL('theme', '', {}, params);
        var self = this;
        app.api.call('read', url, {}, {success: function(data) {
            self.lessVars = data;
            self.render();
            self.previewTheme();
        }});
    },
    saveTheme: function() {
        var self = this,
            params = {
                platform: app.config.platform,
                themeName: this.customTheme
            };
        self.showMessage('Saving theme....');

        // get the value fron each input
        this.$('input').each(function() {
            var $this = $(this);
            params[$this.attr("name")] = $this.hasClass('bgvar') ? '"' + $this.val() + '"' : $this.val();
        });
        // generate the URL
        var url = app.api.buildURL('theme', '', {}, {});
        // save the theme
        app.api.call('create', url, params, {success: function(data) {
            self.showMessage('Done', 3000);
        }});
    },
    toggleModal: function() {
        this.$('#modal-confirm-reset').toggleClass('hide');
    },
    resetTheme: function() {
        this.toggleModal();

        var self = this,
            params = { "reset": true,
                platform: app.config.platform,
                themeName: this.customTheme
            };
        self.showMessage('Restoring default theme....');

        var url = app.api.buildURL('theme', '', {}, {});
        app.api.call('create', url, params, {success: function(data) {
            self.showMessage('Done', 3000);
            self.loadTheme();
        }});
    },
    previewTheme: function() {
        var params = {};
        this.$('input').each(function() {
            var $this = $(this);
            params[$this.attr("name")] = $this.hasClass('bgvar') ? '"' + $this.val() + '"' : $this.val();
        });
        this.context.set("colors", params);
    },
    showMessage: function(message, timer) {

        ajaxStatus = new SUGAR.ajaxStatusClass() || null;

        if (ajaxStatus) {
            if (timer) {
                ajaxStatus.flashStatus(message, timer);
                window.setTimeout('ajaxStatus.hideStatus();', timer);
            } else {
                ajaxStatus.showStatus(message);
            }
        } else {
            console.log(message);
        }
    }
})