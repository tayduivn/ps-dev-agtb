(function(app) {

    /**
     * Overrides View::_renderHtml() to enable bootstrap widgets after the element has been added to the DOM
     */
    var __superViewRender__ = app.view.View.prototype._renderHtml;
    app.view.View.prototype._renderHtml = function() {

        __superViewRender__.call(this);

        // do this if greater than 768px page width
        if ($(window).width() > 768) {
            this.$("[rel=tooltip]").tooltip({ placement: "bottom" });
        }
        //popover
        this.$("[rel=popover]").popover();
        this.$("[rel=popoverTop]").popover({placement: "top"});

        if ($.fn.timeago) {
            $("span.relativetime").timeago({
                logger: SUGAR.App.logger,
                date: SUGAR.App.date,
                lang: SUGAR.App.lang,
                template: SUGAR.App.template
            });
        }
    };

    /**
     * Overrides View::initialize() to remove the bootstrap widgets element from all the page
     * The widget is actually bind to an element that will be removed from the DOM when the view changes. So we need to
     * manually remove elements automatically created by the widget.
     */
    var __superViewInit__ = app.view.View.prototype.initialize;
    app.view.View.prototype.initialize = function(options) {
        __superViewInit__.call(this, options);
        $('.popover, .tooltip').remove();
    };

    /**
     * Overrides Field::_render() to fix placeholders on IE and old browsers
     */
    var __superFieldRender__ = app.view.SupportPortalField.prototype._render;
    app.view.SupportPortalField.prototype._render = function() {

        __superFieldRender__.call(this);

        this.$("input:visible[placeholder!='']").placeholder();
    };



})(SUGAR.App);