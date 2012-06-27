(function(app) {

    app.view.fields.RelateField = app.view.Field.extend({
        events: {
            'click input': 'onClick'
        },

        initialize: function(options) {
            app.view.Field.prototype.initialize.call(this, options);
        },

        createList:function(){
            this.relateLayout = app.view.createLayout({
                name: 'relate'
            });

            var meta = app.metadata.getModule(this.view.module).fields[this.name];
            var module = meta.module;

            var listView = app.view.createView({module: module,
                name: 'list',
                context: app.context.getContext({module: module}).prepare()
            });
            listView.context.set({view:listView});

            listView.setTemplateOption("partials", {
                'list-item': app.template.get("list-item-relate")
            });

            listView.on('menu:item:clicked',function(item){
                this.model.set(this.name,item.get('name'));
                this.model.set(meta.id_name,item.get('id'));

                this.hideMenu();
            },this);

            var searchboxView = app.view.createView({
                template: app.template.get('list-header-relate'),
                name: 'list-header',
                context: app.context.getContext({module: module})
            });

            searchboxView.on('menu:cancel:clicked',function(){
                this.hideMenu();
            },this);

            this.relateLayout.addComponent(searchboxView);
            this.relateLayout.addComponent(listView);
        },

        hideMenu:function(){
            this.relateLayout.dispose();

            $(app.controller.layout.el).show();
        },

        onClick: function(e) {
            e.preventDefault();
            this.createList();

            $(app.controller.layout.el).hide();

            this.relateLayout.$el.appendTo('#content');
            this.relateLayout.render();
            this.relateLayout.getComponent('list').loadData();
        }
    });

})(SUGAR.App);