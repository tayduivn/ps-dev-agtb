({
    fieldSelector: '.htmleditable', //iframe or textarea selector

    /**
     * Render an editor for edit view or an iframe for others
     *
     * @private
     */
    _render: function() {
        this.app.view.Field.prototype._render.call(this);

        this._getHtmlEditableField().attr('name', this.name);
        if (this._isEditView()) {
            this._renderEdit();
        } else {
            this._renderView();
        }
    },

    /**
     * Populate the editor or textarea with the value from the model
     */
    bindDataChange: function() {
        var self = this;
        this.model.on('change:' + this.name, function(model, value) {
            if (self._isEditView()) {
                self._setEditorContent(value);
            } else {
                self._setIframeContent(value);
            }
        });
    },

    /**
     * Render editor for edit view
     *
     * @private
     */
    _renderEdit: function() {
        var self = this;
        var wysiHtml5Editor = this._getWysiHtml5Editor();

        // Update the model value when user focuses away from the editor
        wysiHtml5Editor.editor.on('blur:composer', function() {
            self.model.set(self.name, self._getEditorContent());
        });

        this._setEditorContent(this.value);
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
            self._setIframeContent(self.value);

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
        this._getHtmlEditableField().attr('onload', 'SUGAR.App.trigger("' + this._getEventString('ready') +'");');
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
    },

    /**
     * Is this an edit view?  If the field contains a textarea, it will assume that it's in an edit view.
     *
     * @return {Boolean}
     * @private
     */
    _isEditView: function() {
        return (this._getHtmlEditableField().prop('tagName') === 'TEXTAREA');
    },

    /**
     * Either gets or initializes the wysihtml5 editor.
     *
     * @return {wysihtml5}
     * @private
     */
    _getWysiHtml5Editor: function() {
        var $textarea = this._getHtmlEditableField();
        if ($textarea.data('wysihtml5') === undefined) {
            $textarea.wysihtml5(); //initialize wysihtml5 editor
        }
        return $textarea.data('wysihtml5');
    },

    /**
     * Finds textarea or iframe html element in the field template
     *
     * @return {textarea}
     * @private
     */
    _getHtmlEditableField: function() {
        return this.$el.find(this.fieldSelector);
    },

    /**
     * Gets wysihtml5 editor content
     *
     * @return {*}
     * @private
     */
    _getEditorContent: function() {
        return this._getWysiHtml5Editor().editor.getValue();
    },

    /**
     * Sets wysihtml5 editor content
     *
     * @param content
     * @private
     */
    _setEditorContent: function(content) {
        if (this._isEditView()) {
            this._getWysiHtml5Editor().editor.setValue(content);
        }
    },

    /**
     * Sets iframe content
     *
     * @param content
     * @private
     */
    _setIframeContent: function(content) {
        this._getHtmlEditableField()
            .contents().find('body')
            .html(content);
    }
})