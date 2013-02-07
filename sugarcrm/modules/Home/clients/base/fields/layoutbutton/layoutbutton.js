({
    events: {
        'click .btn.layout' : 'layoutClicked'
    },
    extendFrom: 'ButtonField',
    getFieldElement: function() {
        return this.$el;
    },
    _render: function() {
        app.view.fields.ButtonField.prototype._render.call(this);
        this.$("[rel=tooltip]").tooltip({placement: 'bottom'});
    },
    _loadTemplate: function() {
        app.view.Field.prototype._loadTemplate.call(this);
        if(this.action !== 'edit') {
            this.template = app.template.empty;
        }
    },
    format: function(value) {
        var metadata = this.model.get("metadata");
        if(metadata) {
            return (metadata.components) ? metadata.components.length : 1;
        }
        return value;
    },
    layoutClicked: function(evt) {
        var value = $(evt.currentTarget).data('value');
        this.setLayout(value);
    },
    setLayout: function(value) {
        var span = 12 / value;
        if(this.value) {
            var setComponent = function() {
                var metadata = this.model.get("metadata");

                _.each(metadata.components, function(component){
                    component.width = span;
                }, this);

                if(metadata.components.length > value) {
                    _.times(metadata.components.length - value, function(index){
                        metadata.components[value - 1].rows = metadata.components[value - 1].rows.concat(metadata.components[value + index].rows);
                    },this);
                    metadata.components.splice(value);
                } else {
                    _.times(value - metadata.components.length, function(index) {
                        metadata.components.push({
                            rows: [],
                            width: span
                        });
                    }, this);
                }
                this.model.set("metadata", metadata, {silent: true});
                this.model.trigger("change:layout");
            };
            if(value < this.value) {
                app.alert.show('resize_confirmation', {
                    level: 'confirmation',
                    messages: 'Are you sure to change the layout? The layout can be disorganized.',
                    onConfirm: _.bind(setComponent, this)
                });
            } else {
                setComponent.call(this);
            }
        } else {
            //new data
            var metadata = {
                components: []
            };
            _.times(value, function(index) {
                metadata.components.push({
                    rows: [],
                    width: span
                });
            }, this);

            this.model.set("metadata", metadata, {silent: true});
            this.model.trigger("change:layout");
        }
    },
    bindDomChange: function() {

    }
})
