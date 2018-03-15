({
    extendsFrom: 'BaseRelateField',

    /**
     * @inheritdoc
     */
    initialize: function (options) {
        // deleting for RLI create when there is no account_id.      
        if (options && options.def.filter_relate && !options.model.has('account_id')) {
            delete options.def.filter_relate;
        }

        this._super('initialize', [options]);
    }
})