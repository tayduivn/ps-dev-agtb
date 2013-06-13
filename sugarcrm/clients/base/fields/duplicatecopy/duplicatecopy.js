({
    'events' : {
        'click input[data-action=copy]' : 'copy'
    },

    /**
     * Copies value from current model to primary record
     */
    copy: function() {
        var primaryRecord = this.context.get('primaryRecord');
        if (_.isUndefined(primaryRecord) || _.isUndefined(primaryRecord.id)) {
            return;
        }
        if (!_.isUndefined(this.def.id_name)) {
            primaryRecord.set(this.def.id_name, this.model.get(this.def.id_name));
        }
        primaryRecord.set(this.name, this.model.get(this.name));
    },

    /**
     * {@inheritdoc}
     */
    bindDomChange: function() {},

    /**
     * {@inheritdoc}
     */
    handleValidationError: function (errors) {},

    /**
     * {@inheritdoc}
     */
    clearErrorDecoration: function() {}
})
