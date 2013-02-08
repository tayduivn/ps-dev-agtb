({
    bindDataChange: function() {
        if(this.model) {
            this.model.on("change:metadata", this.setMetadata, this);
        }
    },
    setMetadata: function() {
        if(!this.model.get("metadata")) return;
        //Clean all components
        //TODO: Verify it will cleanup sencondary change also
        _.each(this._components, this.removeComponent, this);
        var components = JSON.parse(JSON.stringify(this.model.get("metadata"))).components;
        _.each(components, function(component, index) {
            this._addComponentsFromDef([{
                layout: {
                    type: 'dashlet-row',
                    width: component.width,
                    components: component.rows,
                    index: index + ''
                }
            }]);
        } , this);
        this.loadData();
        this.render();
    },
    _placeComponent: function(comp, def) {
        if(this.$("#dashlets").length == 0) {
            this.$el.attr(
                {
                    id : 'dashboard',
                    class: 'dashboard'
                }).append(
                    $("<ul></ul>", {
                        id : 'dashlets',
                        class: 'cols row-fluid'
                    })
                );
        }
        this.$("#dashlets").append(comp.el);
    }
})
