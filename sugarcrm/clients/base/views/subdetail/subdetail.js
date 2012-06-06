/**
 * View that displays a model pulled from the activities stream.
 * @class View.Views.SubdetailView
 * @alias SUGAR.App.layout.SubdetailView
 * @extends View.View
 */
({
    events: {
        'click .closeSubdetail': 'closeSubdetail'
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        app.events.register(
            /**
             * Fired when the user clicks on a item in the activity stream
             * Helps pushing the model to the subdetail view.
             *
             * <pre><code>
             * obj.on("app:view:activity:subdetail", callback);
             * </pre></code>
             * @event
             */
            "app:view:activity:subdetail",
            this
        );

        var self = this;
        app.events.on("app:view:activity:subdetail", function(model) {
            if (model) {
                // Fixes bug noticed when going from one module tab to another.
                if(!self.model) {
                    self.model = model;
                } else {
                    self.model.set(model);
                }
            }
        });

        this.fallbackFieldTemplate = "detail";
    },
    render: function() {
        this.$el.parent().addClass("tab-content").attr("id", "folded");
        //avoid to have an empty detail view
    },
    bindDataChange: function() {
        var self = this;
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
})
