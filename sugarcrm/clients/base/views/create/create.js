({
    extendsFrom: 'RecordView',

    /**
     * Initialize the view and prepare the model with default button metadata
     * for the current layout.
     */
    initialize: function(options) {
        _.extend(this.events, {
            'click [name=save_button]': 'save',
            'click [name=cancel_button]': 'cancel'
        })

        options.context.set('create', true);
        app.view.views.RecordView.prototype.initialize.call(this, options);
    },

    _renderHtml: function() {
        app.view.views.RecordView.prototype._renderHtml.call(this);
        this.setTitle(app.lang.get('LBL_CREATE_BUTTON_LABEL', this.module) + ' ' + this.moduleSingular);
    },

    save: function() {
        //TODO: handle save
        console.log('save');
    },

    cancel: function() {
        //TODO: handle cancel
        console.log('cancel');
    }
})