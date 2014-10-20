({
    /**
     * {@inheritdocs}
     *
     * Sets up the file field to edit mode
     *
     * @param {View.Field} field
     * @private
     */
    _renderField: function(field) {
        app.view.View.prototype._renderField.call(this, field);
        app.alert.show('txtConfigLog', {level: 'process', title: 'Loading', autoclose: false});
        url = app.api.buildURL(this.module + '/logGetConfig');
        app.api.call('READ', url, {},{
            success: function(data)
            {
                field.model.set('comboLogConfig',data['records'][0]['cfg_value']);
                app.alert.dismiss('txtConfigLog');
            }
        });
    }
})