/**
 * View that displays a model pulled from the activities stream.
 * @class View.Views.SubdetailView
 * @alias SUGAR.App.layout.SubdetailView
 * @extends View.View
 */
({
    events: {
        'click [data-toggle=tab]': 'closeSubdetail'
    },
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = "detail";
    },
    render: function() {
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
        $('.nav-tabs').find('li').removeClass('on');
        this.$el.empty();
    }
})
