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
        });

        app.view.views.RecordView.prototype.initialize.call(this, options);
    },

    _renderHtml: function() {
        app.view.views.RecordView.prototype._renderHtml.call(this);
        this.setTitle(app.lang.get('LBL_CREATE_BUTTON_LABEL', this.module) + ' ' + this.moduleSingular);
    },

    save: function() {
        if (!this.$('[name=save_button]').hasClass('disabled')) {
            this.model.save({}, {
                success: _.bind(function() {
                    //TODO: close pushdown modal instead
                    app.navigate(this.context, this.model, 'record');
                }, this)
            });
        }
    },

    cancel: function() {
        //TODO: close pushdown modal
        console.log('cancel');
    },

    bindDataChange: function() {
        app.view.views.RecordView.prototype.bindDataChange.call(this);

        this.model.on("change", function() {
            var $saveButton = this.$('[name=save_button]');
            if (this.model.isValid(undefined, true)) {
                $saveButton
                    .removeClass("disabled")
                    .addClass('btn-primary');
            } else {
                $saveButton
                    .addClass("disabled")
                    .removeClass('btn-primary');
            }
        }, this);
    }

})