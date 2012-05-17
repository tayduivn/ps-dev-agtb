(function(app) {

    app.view.views.HeaderView = app.view.View.extend({
        events: {
            'click #moduleList li a': 'onModuleTabClicked',
            'click #createList li a': 'onCreateClicked',
            'click .cube': 'onHomeClicked',
            'click .icon-plus': 'onAddClicked'
        },
        initialize: function(options) {
            app.view.View.prototype.initialize.call(this, options);

            var self = this;
            app.events.on("app:view:change", function() {
                self.render();
            });
        },

        render: function() {
            if (!app.api.isAuthenticated()) {
                this.$el.addClass("hide");
            }
            else {
                this.$el.removeClass("hide");
                app.view.View.prototype.render.call(this);
                this._renderLeftList();
                this._renderRightList();
            }
        },

        onCreateClicked:function(){

        },

        onModuleTabClicked:function(){

        },

        onHomeClicked:function(e){
            e.preventDefault();
            $(document.body).toggleClass('onL');
        },

        onAddClicked:function(e){
            e.preventDefault();
            $(document.body).toggleClass('onR');
        },

        _renderLeftList:function () {
            var tmpl = app.template.get('left.menu');

            if (tmpl) {
                this.$('#moduleList').append(tmpl(_.keys(app.metadata.getModuleList())));
            }
        },

        _renderRightList: function() {
            var tmpl = app.template.get('right.menu');

            if (tmpl) {
                this.$('#createList').append(tmpl(_.keys(app.metadata.getModuleList())));
            }
        }

    });

})(SUGAR.App);