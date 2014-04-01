({
    extendsFrom: 'CreateView',
    showHelpText: false,

    _renderHtml: function() {
        this._super('_renderHtml');

        if (!this.showHelpText) {
            _.each(this.fields, function(field){
                field.def.help = null;
                field.options.def.help = null;
            });
        }
    }
})
