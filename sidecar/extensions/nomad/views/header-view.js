(function(app) {

    app.view.views.HeaderView = app.view.View.extend({
        events: {
            'click #moduleList li a': 'onModuleTabClicked',
            'click #createList li a': 'onCreateClicked',
            'click .cube': 'onHomeClicked',
            'click .create-entity': 'onAddClicked'
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
            //this.delegateEvents();
        },

        onCreateClicked:function(){
            $(document.body).removeClass('onR');
        },

        onModuleTabClicked:function(){
            $(document.body).removeClass('onL');
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
                this.$('#moduleList').append(tmpl({items:_.keys(app.metadata.getModuleList()),
                    userName:app.user.get('full_name'),
                    userId:app.user.get('id')}));
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