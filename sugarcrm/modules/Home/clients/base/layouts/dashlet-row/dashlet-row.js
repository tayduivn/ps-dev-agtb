({
    tagName: 'li',
    initialize: function(options) {
        this.index = options.meta.index;
        options.meta = this.setMetadata(options.meta);
        app.view.Layout.prototype.initialize.call(this, options);
        this.model.on("setMode", this.setMode, this);
        this.setMode(this.model.mode);
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
            layout: {
                type: 'dashlet',
                index: this.index + '' + meta.components.length,
                empty: true,
                components: [
                    {
                        view: 'dashlet-row-empty'
                    }
                ]
            }
        };
        meta.components.push(addRowDashlet);
        if(meta.css_class) meta.css_class += ' ';
        meta.css_class = 'span' + (meta.width || 12);
        return meta;
    },
    _placeComponent: function(comp, def, prepend) {
        var $body = this.$el.children(".dashlet-row");
        if($body.length === 0) {
            $body = $("<ul></ul>").addClass("dashlet-row");
            this.$el.append($body);
        }
        var $container = $("<div></div>", {class: 'rows'}).append(comp.el),
            $el = $("<li></li>", {class: 'row-fluid'}).append($container);

        if(prepend) {
            $body.children("li:last").before($el)
        } else {
            $body.append($el);
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
        _.each(this._components, function(component){
            component.index++;
        }, this);
        this._addComponentsFromDef([{
            layout: {
                type : 'dashlet-cell',
                index: this.index + '' + (this._components.length - 1),
                components: components
            }
        }]);
        this.render();
        this.setMode(this.model.mode);
    },
    setMode: function(type) {
        if(type === 'edit' || (this.model._previousMode === 'edit' && type === 'drag')) {
            this.$el.children(".dashlet-row").children("li").addClass("well");
        } else {
            this.$el.children(".dashlet-row").children("li").removeClass("well");
        }
    }
})
