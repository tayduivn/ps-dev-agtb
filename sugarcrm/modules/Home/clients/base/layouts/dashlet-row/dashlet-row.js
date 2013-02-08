({
    tagName: 'li',
    initialize: function(options) {
        this.index = options.meta.index;
        options.meta = this.setMetadata(options.meta);
        app.view.Layout.prototype.initialize.call(this, options);
    },
    setMetadata: function(meta) {
        meta.components = meta.components || [];
        _.each(meta.components, function(component, index){
            meta.components[index] = {
                layout: {
                    type : 'dashlet-cell',
                    index : this.index + '' + index,
                    components: component
                }
            };
        }, this);

        var addRowDashlet = {
            view: 'dashlet-row-empty'
        };
        meta.components.push(addRowDashlet);
        if(meta.css_class) meta.css_class += ' ';
        meta.css_class = 'span' + (meta.width || 12);
        return meta;
    },
    _placeComponent: function(comp, def, prepend) {
        var $container = $("<ul></ul>", {class: 'rows'}).append(comp.el),
            $el = $("<li></li>", {class: 'row-fluid'}).append($container);
        if(prepend) {
            this.$el.children("li:last").before($el)
        } else {
            this.$el.append($el);
        }
    },
    addComponent: function(component, def) {
        if(this.prependComponent) {
            if (!component.layout) component.layout = this;
            this._components.splice(this._components.length - 1, 0, component);
            this._placeComponent(component, def, true);
            this.prependComponent = false;
        } else {
            app.view.Layout.prototype.addComponent.call(this, component, def);
        }
    },
    addRow: function(columns) {
        var span = 12 / columns,
            components = [];
        _.times(columns, function() {
            components.push({
                width: span
            });
        });


        var metadata = this.model.get("metadata");
        var position = this.index.split(''),
            component = metadata.components;
        _.each(position, function(index){
            component = component.rows ? component.rows[index] : component[index];
        }, this);
        component.rows.push(JSON.parse(JSON.stringify(components)));
        this.model.set("metadata", metadata, {silent: true});
        this.model.trigger("change:layout");
        
        this.prependComponent = true;
        this._addComponentsFromDef([{
            layout: {
                type : 'dashlet-cell',
                components: components
            }
        }]);

        this.render();
    }
})
