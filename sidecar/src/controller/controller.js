(function(app) {
    var Controller = Backbone.View.extend({
        initialize: function() {

        },

        loadView: function() {}
    });

    var module = {
        init: function(instance) {
            instance.controller = instance.controller || _.extend(module, new Controller({el: app.rootEl}));
        }
    };

    app.augment("controller", module);

})(SUGAR.App);