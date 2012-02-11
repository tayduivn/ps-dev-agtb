(function(app) {
    var Controller = Backbone.View.extend({
        initialize: function() {
            this.context = app.context.getContext();
        },

        loadView: function(params) {
            var data = {};

            this.context.init(params, data);
        }
    });

    var module = {
        init: function(instance) {
            instance.controller = instance.controller || _.extend(module, new Controller({el: app.rootEl}));
        }
    };

    app.augment("controller", module);

})(SUGAR.App);