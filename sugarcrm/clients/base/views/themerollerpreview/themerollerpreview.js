/**
 * View that displays edit view on a model
 * @class View.Views.EditView
 * @alias SUGAR.App.layout.EditView
 * @extends View.View
 */
({
    initialize: function(options) {

        app.view.View.prototype.initialize.call(this, options);
        this.platform = "portal";
        this.customTheme = "default";
        this.context.on("change", this.reloadIframeBootstrap, this);
    },
    reloadIframeBootstrap: function() {
        var self = this;
        var params = {
                    preview: new Date().getTime(),
                    platform: this.platform,
                    custom: this.customTheme
                };
        _.extend(params, this.context.attributes.colors);
        var cssLink = app.api.buildURL('bootstrap.css', '', {}, params);
        console.log(cssLink);
        $('iframe').contents().find('link[rel=stylesheet]').attr("href", cssLink);
        $('iframe').contents().find('body').css("backgroundColor", "transparent");
    }
})