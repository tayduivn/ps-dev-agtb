(function(app) {
    var Controller = Backbone.View.extend({
        initialize: function() {
            _.bindAll(this);
            this.context = app.context.getContext();
        },

        loadView: function(params) {
            this.data = {};
            this.layout = null;

            this.data = this.getData(params);
            this.layout = this.getLayout(params);
            this.context.init(params, this.data);

            // Render the layout
            this.layout.render();

            // Render the rendered layout to the main element
            this.$el.html(this.layout.$el);
        },

        getData: function(opts) {
            var data;

            if (opts.id) {
                data = SUGAR.App.dataManager.fetchBean(opts.module, opts.id);
            } else if (opts.url) {
                // TODO: Make this hit a custom url
            } else {
                data = SUGAR.App.dataManager.fetchBeans(opts.module)
            }

            return data;
        },

        getLayout: function(opts) {
            return SUGAR.App.Layout.get({
                layout: opts.layout,
                module: opts.module
            });
        }
    });

    var module = {
        init: function(instance) {
            instance.controller = instance.controller || _.extend(new Controller({el: app.rootEl}), module);
        }
    };

    app.augment("controller", module);
})(SUGAR.App);