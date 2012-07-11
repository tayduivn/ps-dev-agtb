/**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    events: {
        "click [name=save_button]" : "saveTheme",
        "click [name=reset_button]" : "resetTheme",
        "blur input" : "previewTheme"
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.platform = "portal";
        this.customTheme = "default";
        this.loadTheme();
    },
    _renderHtml: function() {
        if (this.lessVars && this.lessVars.rel && this.lessVars.rel.length>0) {
            _.each(this.lessVars.rel, function (obj, key) {
                this.lessVars.rel[key].relname = this.lessVars.rel[key].value;
                this.lessVars.rel[key].relname = this.lessVars.rel[key].relname.replace('@', '');
            }, this);
        }

        app.view.View.prototype._renderHtml.call(this);
        _.each(this.$('.hexvar[rel=colorpicker]'), function(obj, key) {
            $(obj).blur(function() {
                $(this).parent().parent().find('.swatch-col').css('backgroundColor', $(this).val() );
            });
        }, this);
        this.$('.hexvar[rel=colorpicker]').colorpicker();
        this.$('.rgbavar[rel=colorpicker]').colorpicker({format: 'rgba'});
    },
    loadTheme: function() {
        var params = {
            platform: this.platform,
            custom: this.customTheme
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
                platform: this.platform,
                custom: this.customTheme
            };
        // get the value fron each input
        this.$('input').each(function() {
            var $this = $(this);
            params[$this.attr("name")] = $this.hasClass('bgvar') ? '"' + $this.val() + '"' : $this.val();
        });
        // generate the URL
        var url = app.api.buildURL('theme', '', {}, {});
        // save the theme
        app.api.call('create', url, params, {success: function(data) {
            alert('saved');
        }});
    },
    resetTheme: function() {
        var self = this,
            params = { "reset": true,
                platform: this.platform,
                custom: this.customTheme
            };
        var url = app.api.buildURL('theme', '', {}, {});
        app.api.call('create', url, params, {success: function(data) {
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
    }
})