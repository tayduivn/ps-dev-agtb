({
    extendsFrom: 'HomeDashletRowLayout',
    tagName: 'ul',
    className: 'rows',
    _placeComponent: function(comp, def) {
        var span = 'widget-container span' + (def.width || 12);
        this.$el.append($("<li>", {class: span}).append(comp.el));
    },
    setMetadata: function(meta) {
        meta.components = meta.components || [];
        _.each(meta.components, function(component, index){
            if(!(component.view || component.layout)) {
                meta.components[index] = _.extend({}, {
                    view: 'dashlet-cell-empty'
                }, component);
            } else {
                meta.components[index] = {
                    layout: {
                        type: 'dashlet',
                        index: this.index + '' + index,
                        label: component.name,
                        components: [
                            component
                        ]
                    }
                };
            }
        }, this);

        return meta;
    }
})
