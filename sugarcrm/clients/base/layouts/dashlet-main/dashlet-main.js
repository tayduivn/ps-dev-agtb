({
    tagName: "ul",
    bindDataChange: function() {
        if(this.model) {
            this.model.on("change:metadata", this.setMetadata, this);
            this.model.on("change:layout", this.setWidth, this);
            this.model.on("applyDragAndDrop", this.applyDragAndDrop, this);
            this.model.on("setMode", function(mode) {
                this.model._previousMode = this.model.mode;
                this.model.mode = mode;
            }, this);
            this.model.trigger('setMode', this.context.get("create") ? 'edit' : 'view');
        }
    },
    setMetadata: function() {
        if(!this.model.get("metadata")) return;
        //Clean all components
        _.each(this._components, function(component) {
            component.dispose();
        }, this);
        this._components = [];
        this.$("#dashlets").remove();

        var components = app.utils.deepCopy(this.model.get("metadata")).components;
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
    setWidth: function() {
        var metadata = this.model.get("metadata"),
            $el = this.$el.children();

        _.each(metadata.components, function(component, index){
            $el.get(index).className = $el.get(index).className.replace(/span\d+\s*/, '');
            $($el.get(index)).addClass("span" + component.width);
        }, this);
    },
    _placeComponent: function(comp, def) {
        this.$el.attr({
            id : 'dashlets',
            'class': 'row-fluid'
        }).append(comp.el);
    },
    applyDragAndDrop: function() {
        var self = this;
        this.$('.widget:not(.empty)').draggable({
            revert: 'invalid',
            handle: 'h4',
            appendTo: this.$el,
            cursorAt: {
                left: 150,
                top: 16
            },
            start: function(event, ui) {
                $(this).css({visibility: 'hidden'});
                self.model.trigger("setMode", "drag");
            },
            stop: function() {
                self.model.trigger("setMode", self.model._previousMode);
                self.$(".widget.ui-draggable").attr("style", "");
            },
            helper: function() {
                var $clone = $(this).clone();
                $clone
                    .addClass('helper')
                    .css({opacity: 0.8})
                    .width($(this).width());
                $clone.find('.btn-toolbar').remove();
                return $clone;
            }
        });

        this.$('.widget-container').droppable({
            activeClass: 'ui-droppable-active',
            hoverClass: 'active',
            tolerance: 'pointer',
            accept: function() {
                return self.$(this).find('.widget[data-action=droppable]').length === 1;
            },
            drop: function(event, ui) {
                var sourceIndex = ui.draggable.parents(".widget-container:first").data('index')(),
                    targetIndex = self.$(this).data('index')();
                self.switchComponent(targetIndex, sourceIndex);
            }
        });
    },
    getCurrentComponent: function(metadata, tracekey) {
        var position = tracekey.split(''),
            component = metadata.components;
        _.each(position, function(index){
            component = component.rows ? component.rows[index] : component[index];
        }, this);

        var layout = this;
        _.each(position, function(index){
            layout = layout._components[index];
        }, this);
        return {
            metadata: component,
            layout: layout
        };
    },
    switchComponent: function(target, source) {
        var metadata = this.model.get("metadata");
        var targetComponent = this.getCurrentComponent(metadata, target),
            sourceComponent = this.getCurrentComponent(metadata, source);

        //Swap the metadata
        _.each(sourceComponent.metadata, function(value, key) {
            if(key !== 'width') {
                targetComponent.metadata[key] = value;
                delete sourceComponent.metadata[key];
            }
        }, this);
        this.model.set("metadata", app.utils.deepCopy(metadata), {silent: true});
        this.model.trigger("change:layout");
        if(this.model._previousMode === 'view') {
            //Autosave for view mode
            this.model.save();
        }

        //Swap the view components
        var targetDashlet = _.first(targetComponent.layout._components),
            sourceDashlet = _.first(sourceComponent.layout._components);
        targetComponent.layout._components.splice(0,1,sourceDashlet);
        sourceComponent.layout._components.splice(0,1,targetDashlet);

        //Swap the DOM
        var cloneEl = targetComponent.layout.$el.children(":first").get(0);
        targetComponent.layout.$el.append(sourceComponent.layout.$el.children(":not(.helper)").get(0));
        sourceComponent.layout.$el.append(cloneEl);

    },
    _dispose: function() {
        this.model.off("change", null, this);
        this.model.off("applyDragAndDrop", null, this);
        this.model.off("setMode", null, this);
        app.view.Layout.prototype._dispose.call(this);
    }
})
