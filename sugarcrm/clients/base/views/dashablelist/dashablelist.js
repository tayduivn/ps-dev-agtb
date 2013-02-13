({
    extendsFrom: 'ListView',
    plugins: ['Dashlet'],
    initDashlet: function(view) {
        var dashlet = JSON.parse(JSON.stringify(this.context.get("dashlet")));

        if(view === 'config') {
            this.createMode = true;
            this.action = 'edit';

            var metadata = app.metadata.getView(dashlet.module, 'list');

            var panel_module_metadata = _.find(this.meta.dashlet_config_panels, function(panel){
                    return panel.name === 'panel_module_metadata';
                }, this),
                display_column = _.find(panel_module_metadata.fields, function(field) {
                return field.name === 'display_columns';
            }, this);
            display_column.options = {
                '' : ''
            };
            if(metadata) {
                _.each(_.flatten(_.pluck(metadata.panels, 'fields')), function(field, index) {
                    display_column.options[field.name] = app.lang.get(field.label, dashlet.module);
                }, this);
            }
            this.meta.panels = this.meta.dashlet_config_panels;

            app.view.views.RecordView.prototype._renderPanels.call(this, this.meta.panels);
        } else {
            this.context.set("limit", dashlet.display_rows || 5);
            var collection = this.context.get("collection");
            collection.myItems = (dashlet.my_items === "1") ? true : false;
            collection.favorites = (dashlet.favorites === "1") ? true : false;


            //Filter
            /*
            collection.filter = {filter : [
                {
                    "$and" :[
                        {"name":{"$starts":"a"}}
                    ]
                }
            ]};
            */

            if(dashlet.auto_refresh && dashlet.auto_refresh > 0) {
                if(this.timerId) {
                    clearInterval(this.timerId);
                }
                this.timerId = setInterval(_.bind(function(){
                    this.context._dataFetched = false;
                    this.layout.loadData();
                }, this), dashlet.auto_refresh * 1000 * 60);
            }

            _.each(dashlet.display_columns, function(name, index){
                dashlet.display_columns[index] = {
                    name: name,
                    sortable: true
                };
            }, this);
            this.meta.panels[0].fields = dashlet.display_columns;
        }
    },
    _dispose: function() {
        if(this.timerId) {
            clearInterval(this.timerId);
        }
        app.view.views.ListView.prototype._dispose.call(this);
    }
})
