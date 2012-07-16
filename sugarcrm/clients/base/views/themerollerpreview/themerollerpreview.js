/**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    initialize: function(options) {

        app.view.View.prototype.initialize.call(this, options);
        this.customTheme = "default";
        this.context.on("change", this.reloadIframeBootstrap, this);
    },
    reloadIframeBootstrap: function() {
        var self = this;
        var params = {
                    preview: new Date().getTime(),
                    platform: app.config.platform,
                    themeName: this.customTheme
                };
        _.extend(params, this.context.attributes.colors);
        var cssLink = app.api.buildURL('bootstrap.css', '', {}, params);
        $('iframe#previewTheme').hide();
        self.$(".ajaxLoading").show();
        $.get(cssLink)
            .success(function(data) {
                $('iframe#previewTheme').contents().find('style').html(data);
                self.$(".ajaxLoading").hide();
                $('iframe#previewTheme').show();
            });
        $('iframe').contents().find('body').css("backgroundColor", "transparent");
    }
})