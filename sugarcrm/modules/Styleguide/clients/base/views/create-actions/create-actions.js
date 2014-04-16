({
    extendsFrom: 'CreateView',
    showHelpText: false,
    showErrorDecoration: false,
    events: {
        'click a[name=show_help_text]:not(.disabled)': 'toggleHelpText',
        'click a[name=display_error_state]:not(.disabled)': 'toggleErrorDecoration'
    },

    _render: function() {
        this._super('_render');
        if (this.showErrorDecoration) {
            _.each(this.fields, function(field) {
                if (!_.contains(['button','rowaction','actiondropdown'], field.type)) {
                    if (field.type === 'email') {
                        var errors = {email: ['primary@example.info']};
                        field.decorateError(errors);
                    } else {
                        field.setMode('edit');
                        field.decorateError('You did a bad, bad thing.');
                    }
                }
            });
        }
    },

    _renderHtml: function() {
        this._super('_renderHtml');
        if (!this.showHelpText) {
            _.each(this.fields, function(field) {
                field.def.help = null;
                field.options.def.help = null;
            });
        }
    },

    toggleHelpText: function() {
        this.showHelpText = !this.showHelpText;
        this.render();
    },

    toggleErrorDecoration: function() {
        this.showErrorDecoration = !this.showErrorDecoration;
        this.render();
    }
})
