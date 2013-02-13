({
    extendsFrom: 'HomeDashletRowLayout',
    tagName: 'ul',
    className: 'rows row-fluid',
    initialize: function(options) {
        app.view.layouts.HomeDashletRowLayout.prototype.initialize.call(this, options);

    },
    _placeComponent: function(comp, def) {
        var span = 'widget-container span' + (def.width || 12);
        this.$el.append($("<li>", {class: span}).append(comp.el));
    },
    setMetadata: function(meta) {
        meta.components = meta.components || [];
        _.each(meta.components, function(component, index){
            if(!(component.view || component.layout)) {
                meta.components[index] = _.extend({}, {
                    layout: {
                        type: 'dashlet',
                        index: this.index + '' + index,
                        empty: true,
                        components: [
                            {
                                view: 'dashlet-cell-empty'
                            }
                        ]
                    }
                }, component);
            } else {
                if(component.context) {
                    _.extend(component.context, {
                        forceNew: true
                    })
                }
                meta.components[index] = {
                    layout: {
                        type: 'dashlet',
                        index: this.index + '' + index,
                        label: component.name,
                        components: [
                            component
                        ]
                    },
                    width: component.width
                };
            }
        }, this);

        return meta;
    }
})
