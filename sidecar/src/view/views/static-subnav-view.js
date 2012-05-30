(function(app) {

    /**
     * View that displays the subnav bar NOT bound to model with a simple left aligned title. 
     * @class View.Views.StaticSubnavView
     * @alias SUGAR.App.view.views.StaticSubnavView
     * @extends View.View
     */
    app.view.views.StaticSubnavView = app.view.View.extend({

        instance: null,

        /**
         * options.title is required and will be the left aligned title.
         */
        initialize: function(options) {
            var self     = this;
            app.view.View.prototype.initialize.call(self, options);
        },
        /** 
         * Shows a static subnav just below main navbar.
         * @param {String} t - title to display
         */
        display: function(t) {
            var self = this, StaticSubnav, ctx, subnav;

            if (!app.api.isAuthenticated()) return;
            app.view.View.prototype.render.call(self);
            ctx = { title: t };

            StaticSubnav = Backbone.View.extend({
                template: '<div class="btn-toolbar pull-left"><h1>{{title}}</h1></div>',
                initialize: function() {
                    this.render();
                },
                empty: function() {
                    this.$el.find('h1').text('');
                },
                render: function() {
                    var tpl = Handlebars.compile(this.template);
                    this.$el.html(tpl(ctx));
                }
            });
            subnav = new StaticSubnav();
            this.$el.html(subnav.el);
            this.$el.show();
            this.instance = subnav;
        },
        // Header view needs this when a tab is clicked. It simply removes any text
        // in the Subnav so when next Subnav shows there's no flicker showing old text
        empty: function() {
            if(this.instance) {
                this.instance.empty();
            }
        }
    });

}(SUGAR.App));
