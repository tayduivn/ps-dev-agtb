({
    extendsFrom: 'PreviewView',

    /**
     * Track the original model passed in from the worksheet, this is needed becuase of how the base preview works
     */
    originalModel: undefined,

    _renderField: function(field, $fieldEl) {
        if (field.type === 'hidden') {
            $fieldEl.parents('.row-fluid').eq(0).hide();
        }
        this._super("_renderField", arguments);
    }
})
