(function(app) {
    /**
     * View that displays footer for current app
     * @class View.Views.FooterView
     * @alias SUGAR.App.layout.FooterView
     * @extends View.View
     */
    app.view.views.FooterView = app.view.View.extend({
        events: {
            'click #tour': 'systemTour',
            'click #print': 'print',
            'click #top': 'top'
        },
        initialize: function(options) {
            app.events.on("app:sync:complete", function() {
                this.render();
            }, this);
            app.view.View.prototype.initialize.call(this, options);
        },
        render: function() {
            if (!app.api.isAuthenticated()) return;

            app.view.View.prototype.render.call(this);
        },
        systemTour: function() {
            this.$('#systemTour').modal('show');
        },
        print: function() {
            window.print();
        },
        top: function() {
            scroll(0,0);
        }
    });

}(SUGAR.App));
