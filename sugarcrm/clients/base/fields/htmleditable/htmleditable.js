({
    fieldSelector: '.htmleditable', //iframe or textarea selector

    /**
     * Render an editor for edit view or an iframe for others
     *
     * @private
     */
    _render: function() {
        this.app.view.Field.prototype._render.call(this);

        if (this.view.name === 'edit') {
            this._renderEdit();
        } else {
            this._renderView();
        }
    },

    /**
     * Render editor for edit view
     *
     * @private
     */
    _renderEdit: function() {
        this.$el.find(this.fieldSelector)
            .wysihtml5()
            .val(this.value);
    },

    /**
     * Render read-only view for other views
     *
     * @private
     */
    _renderView: function() {
        var self = this;
        this._setupIframeOnLoadEvent();
        this.app.on(this._getEventString('ready'), function() {
            self.$el.find(self.fieldSelector)
                .contents().find('body')
                .html(self.value);

            // remove event handler
            self.app.off(self._getEventString('ready'));
        });
    },

    /**
     * Setup onload event to the iframe so that content can be inserted into the iframe after it has been loaded.
     *
     * @private
     */
    _setupIframeOnLoadEvent: function() {
        this.$el.find(this.fieldSelector).attr('onload', 'SUGAR.App.trigger("' + this._getEventString('ready') +'");');
    },

    /**
     * Get a unique event key for this field.
     *
     * @param state
     * @return {String}
     * @private
     */
    _getEventString: function(state) {
        return this.cid + ':' + state;
    }
})