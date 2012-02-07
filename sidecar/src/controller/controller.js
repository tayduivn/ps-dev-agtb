(function(app) {
    var Controller = Backbone.View.extend({
        initialize: function() {

        },

        loadView: function(){}
    });

    app.augment("controller", {
        init: function(instance) {
            instance.controller = instance.controller || new Controller({el: app.rootEl});
        }
    })

})(SUGAR.App);