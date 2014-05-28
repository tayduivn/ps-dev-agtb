//FILE SUGARCRM flav=ent ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.ThemerollerView
 * @alias SUGAR.App.view.views.BaseThemerollerView
 * @extends View.View
 */
({
    events: {
        "click [name=save_button]": "saveTheme",
        "click [name=refresh_button]": "loadTheme",
        "click [name=reset_button]": "resetTheme",
        "blur input": "previewTheme"
    },
    initialize: function(options) {
        this._super('initialize', [options]);
        this.context.set('skipFetch', true);
        this.customTheme = "default";
        this.loadTheme();
    },
    parseLessVars: function() {
        if (this.lessVars && this.lessVars.rel && this.lessVars.rel.length > 0) {
            _.each(this.lessVars.rel, function(obj, key) {
                this.lessVars.rel[key].relname = this.lessVars.rel[key].value;
                this.lessVars.rel[key].relname = this.lessVars.rel[key].relname.replace('@', '');
            }, this);
        }
    },
    _renderHtml: function() {
        if (!app.acl.hasAccess('admin', 'Administration')) {
            return;
        }
        this.parseLessVars();
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
        this.themeApi('read', {}, _.bind(function(data) {
            this.lessVars = data;
            if (this.disposed) {
                return;
            }
            this.render();
            this.previewTheme();
        }, this));
    },
    saveTheme: function() {
        var self = this;
        // get the value from each input
        var colors = this.getInputValues();

        this.showMessage('LBL_SAVE_THEME_PROCESS');
        this.themeApi('create', colors, function() {
            app.alert.dismissAll();
        });
    },
    resetTheme: function() {
        var self = this;
        app.alert.show('reset_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('LBL_RESET_THEME_MODAL_INFO'),
            onConfirm: function () {
                self.showMessage('LBL_RESET_THEME_PROCESS');
                self.themeApi('create', {"reset": true}, function(data) {
                    app.alert.dismissAll();
                    self.loadTheme();
                });
            }
        });
    },
    previewTheme: function() {
        var colors = this.getInputValues();
        this.context.set("colors", colors);
    },
    themeApi: function(method, params, successCallback) {
        var self = this;
        _.extend(params, {
            platform: 'portal',
            themeName: self.customTheme
        });
        var paramsGET   = (method==='read') ? params : {},
            paramsPOST  = (method==='read') ? {} : params;
        var url = app.api.buildURL('theme', '', {}, paramsGET);
        app.api.call(method, url, paramsPOST,
            {
                success: successCallback ,
                error: function(error) {
                    app.error.handleHttpError(error);
                }
            },
            { context: self }
        );
    },
    getInputValues: function() {
        var colors = {};
        this.$('input').each(function() {
            var $this = $(this);
            colors[$this.attr("name")] = $this.hasClass('bgvar') ? '"' + $this.val() + '"' : $this.val();
        });
        return colors;
    },
    showMessage: function(messageKey) {
        app.alert.show('themeProcessing', {level: 'process', title: app.lang.getAppString(messageKey), closeable: true, autoclose: true});
    }
})
