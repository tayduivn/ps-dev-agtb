({
    events: {
        'click .add-dashlet' : 'layoutClicked'
    },
    originalTemplate: null,
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.model.on("setMode", this.setMode, this);
        this.originalTemplate = this.template;
        this.setMode(this.model.mode);
    },
    layoutClicked: function(evt) {
        var columns = $(evt.currentTarget).data('value');
        this.layout.addRow(columns);
    },
    setMode: function(model) {
        if(model === 'edit') {
            this.template = this.originalTemplate;
        } else {
            this.template = app.template.empty;
        }
        this.render();
    }
})
