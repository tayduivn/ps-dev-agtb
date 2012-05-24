(function(app) {

    app.view.fields.RelateField = app.view.Field.extend({
        events: {
            'click input': 'onClick'
        },
        initialize: function(options) {
            app.view.Field.prototype.initialize.call(this, options);

            var module = app.metadata.getModule(this.view.module).fields[this.name].module;

            this.menuView = app.view.createView({module: module,
                name: 'list',
                template: app.template.get('list.menu'),
                context: app.context.getContext({module: module}).prepare()
            });
            this.menuView.context.set({view:this.menuView});

            this.menuView.setListItemHelper(Handlebars.helpers.listMenuItem);

            this.menuView.on('menu:item:clicked',function(item){
                alert(item.get('name'));
                $(this.menuView.el).remove();
                $(app.controller.el).show();
            },this);

            this.menuView.on('menu:cancel:clicked',function(){
                $(this.menuView.el).remove();
                $(app.controller.el).show();
            },this);

        },
        onClick: function() {
            $(this.menuView.el).appendTo(document.body);
            this.menuView.context.loadData();

            $(app.controller.el).hide();
        }
    });

})(SUGAR.App);