({
    extendsFrom: 'ListView',
    plugins: ['Dashlet'],
    _dataFetched: false, // flag to determine if we tried to get records already
    type : "list",
    initDashlet: function (view) {
        var module = this.context.get("module"),
            filterDef = [];

        //If we are displaying the configuration view instead of the dashlet itself.
        if(this.meta.config) {
            module = this.settings.get("module") || module;
            var metadata = app.metadata.getView(module, 'list');

            var panel_module_metadata = _.find(this.dashletConfig.dashlet_config_panels, function(panel){
                    return panel.name === 'panel_module_metadata';
                }, this),
                display_column = _.find(panel_module_metadata.fields, function (field) {
                    return field.name === 'display_columns';
                }, this);
            display_column.options = {
                '': ''
            };
            if (metadata) {
                _.each(_.flatten(_.pluck(metadata.panels, 'fields')), function (field, index) {
                    display_column.options[field.name] = app.lang.get(field.label, module);
                }, this);
            }
            this.meta.panels = this.dashletConfig.dashlet_config_panels;
        } else {
            this.context.set("skipFetch", false);
            this.context.set("limit", this.settings.get("display_rows") || 5);
            var collection = this.context.get("collection");

            // set up filters for conditions
            if (this.settings.get("my_items") === "1") {
                filterDef.push({'$owner': ''});
            }

            if (this.settings.get("favorites") === "1") {
                filterDef.push({'$favorite': ''});
            }

            // and collapse them with an $and clause if necessary
            collection.filterDef = (_.size(filterDef) > 1) ? {'$and': filterDef} : filterDef;

            // and bind a flag to the context so we know we have tried to get data
            collection.once("reset", function () {
                this._dataFetched = true;
            }, this);
            var auto_refresh = parseInt(this.settings.get("auto_refresh"), 10);

            if (auto_refresh) {
                if (this.timerId) {
                    clearInterval(this.timerId);
                }
                this.timerId = setInterval(_.bind(function () {
                    this.context._dataFetched = false;
                    this.layout.loadData();
                }, this), auto_refresh * 1000 * 60);
            }

            var metadata = app.metadata.getView(module, 'list'),
                display_column = [];
            _.each(this.settings.get("display_columns"), function (name, index) {
                var field = _.find(_.flatten(_.pluck(metadata.panels, 'fields')), function (field) {
                    return field.name === name;
                }, this);
                display_column.push(_.extend({
                    name: name,
                    sortable: true
                }, field || {}));
            }, this);
            this.meta.panels = [{
                fields: display_column
            }];

            // add css class based on module
            this.$el.addClass(module.toLocaleLowerCase());
        }
    },
    _dispose: function () {
        if (this.timerId) {
            clearInterval(this.timerId);
        }
        app.view.invokeParent(this, {type: 'view', name: 'list', method: '_dispose'});
    }
})
