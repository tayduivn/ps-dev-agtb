/**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    events: {
        "click [name=save_button]": "saveTheme",
        "click [name=reset_button]": "resetTheme",
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
    resetTheme: function() {
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