({
    _render: function () {
        if (this.name === 'rst_source_definition') {
            this.view.$('[data-name=rst_source_definition].record-cell').addClass('hide');
        }
        this._super("_render", arguments);
    }
})

