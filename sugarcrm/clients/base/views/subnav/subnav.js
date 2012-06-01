({

/**
 * View that displays the subnav bar
 * @class View.Views.SubnavView
 * @alias SUGAR.App.layout.SubnavView
 * @extends View.View
 */

    /**
     * Subnav will be visible for those layouts
     */
    showForLayouts: ["detail", "edit"],
    showStaticForLayouts: ["search"],

    /**
     * Listens to the app:view:change event and show or hide the subnav
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        var self = this;

        app.events.on("app:view:change", function(viewName) {

            if ($.inArray(viewName, self.showForLayouts) !== -1) {
                self.$el.find('span').each(function() { $(this).empty(); });
                self.show();
                self.bindDataChange();
            } else if ($.inArray(viewName, self.showStaticForLayouts) !== -1) {
                // Caller will calls renderStatic so don't show or bindDataChange
                self.$el.find('span').each(function() { $(this).empty(); });
            } else {
                // If view not in showForLayouts white list, we unbind previously bound change events.
                // This makes sense, and, further, is required for static subnav view to work properly.
                self.hide();
                self.unbind();
            }
        });
    },
    /**
     * Renders Subnav view
     */
    render: function() {
        if (!app.api.isAuthenticated()) return;
        app.view.View.prototype.render.call(this);
        return this;
    },
    /**
     * Provides a way to display static text in subnav. No listeners are attached
     * to model changes so the subnav title stays "sticky" (e.g. see search results)
     */
    renderStatic: function(t) {
        var self = this, StaticSubnav, ctx, subnav;

        if (!app.api.isAuthenticated()) return;
        app.view.View.prototype.render.call(self);
        ctx = { title: t };
        StaticSubnav = Backbone.View.extend({
            template: '<div class="btn-toolbar pull-left"><h1>{{title}}</h1></div>',
            initialize: function() {
                this.render();
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
        return this;
    },
    hide: function() {
        this.$el.hide();
    },
    show: function() {
        this.$el.show();
    },
    bindDataChange: function() {
        var self = this;
        if (app.controller.context.attributes.model) {
            app.controller.context.attributes.model.on("change", function() {
                self.render();
                }, this
            );
        }
    },
    unbind: function() {
        var self = this;
        if (app.controller.context.attributes.model) {
            app.controller.context.attributes.model.off("change");
        }
    }

})

