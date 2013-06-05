({
    extendsFrom: 'EditablelistbuttonField',
    getSaveSuccess: function() {
        app.view.invokeParent(this, {type: 'field', name: 'editablelistbutton', method: 'getSaveSuccess', args: []});

        if (!_.isEmpty(this.model.get('quote_id'))) {
            app.alert.show('save_rli_quote_notice', {
                level: 'info',
                messages: app.lang.get('SAVE_RLI_QUOTE_NOTICE', 'RevenueLineItems'),
                autoClose: true
            });
        }
    }
})