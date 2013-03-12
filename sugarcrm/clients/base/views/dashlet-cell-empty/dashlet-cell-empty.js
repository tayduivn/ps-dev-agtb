({
    events: {
        'click .widget.empty' : 'addClicked'
    },
    originalTemplate: null,
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.model.on("setMode", this.setMode, this);
        this.originalTemplate = this.template;
        this.setMode(this.model.mode);
    },
    addClicked: function(evt) {
        var self = this;
        app.drawer.open({
            layout: 'dashletselect',
            context: {
                module: 'Home',
                model: new app.Bean(),
                forceNew: true
            }
        }, function(model) {
            if(!model) return;

            self.layout.addDashlet({
                name: model.get("name"),
                view: model.get("type"),
                context: {
                    module: model.get("module") || null,
                    model: model.get("model") || null,
                    modelId: model.get("modelId") || null,
                    dashlet: model.attributes
                }
            });
        });
    },
    setMode: function(type) {
        if(type === 'edit') {
            this.template = this.originalTemplate;
        } else if(type === 'drag') {
            this.template = app.template.getView(this.name + '.drop') || this.originalTemplate;
        } else {
            this.template = app.template.getView(this.name + '.empty') || app.template.empty;
        }
        this.render();
    },
    _dispose: function() {
        this.model.off("setMode", null, this);
        app.view.View.prototype._dispose.call(this);
    }
})
