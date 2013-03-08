({
    events: {
        'click .add-dashlet' : 'layoutClicked',
        'click .add-row.empty' : 'addClicked'
    },
    originalTemplate: null,
    columnOptions: [],
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.model.on("setMode", this.setMode, this);
        this.originalTemplate = this.template;
        this.setMode(this.model.mode);
        this.columnOptions = [];
        _.times(this.model.maxRowColumns, function(index) {
            var n = index + 1;
            this.columnOptions.push({
                index: n,
                label: (n > 1) ?
                    app.lang.get('LBL_DASHBOARD_ADD_' + n + '_COLUMNS', this.module) :
                    app.lang.get('LBL_DASHBOARD_ADD_' + n + '_COLUMN', this.module)
            });
        }, this);
    },
    addClicked: function(evt) {
        this.layout.addRow(1);
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
    },
    _dispose: function() {
        this.model.off("setMode", null, this);
        app.view.View.prototype._dispose.call(this);
    }
})
