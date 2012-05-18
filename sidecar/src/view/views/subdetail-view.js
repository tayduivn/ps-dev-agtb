(function(app) {

    /**
     * View that displays a model pulled from the activities stream.
     * @class View.Views.SubdetailView
     * @alias SUGAR.App.layout.SubdetailView
     * @extends View.View
     */
    app.view.views.SubdetailView = app.view.View.extend({
        events: {
            'click .closeSubdetail': 'closeSubdetail'
        },
        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);
            this.fallbackFieldTemplate = "detail";
        },
        render: function() {
            this.$el.parent().parent().addClass("tab-content").attr("id", "folded");
            //avoid to have an empty detail view
        },
        bindDataChange: function() {
            if (this.model) {
                this.model.on("change", function() {
                        app.view.View.prototype.render.call(this);
                    }, this
                );
            }
        },
        // Delegate events
        closeSubdetail: function() {
            this.model.clear();
            this.$el.empty();
            $("li.activity").removeClass("on");
        }
    });

})(SUGAR.App);
