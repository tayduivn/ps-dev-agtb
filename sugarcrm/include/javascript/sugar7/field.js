(function (app) {

    /**
     * Field widgets to use Required labels with because they don't use select or input fields
     * @private
     */
    var _useRequiredLabels = {
        /**
         * It's nonsensical to make a bool field required since it is always has a value (TRUE or FALSE),
         * but it's possible to define it as required in vardefs.
         */
        "bool": true,
        /**
         * Only really needed on edit template where we use radio buttons.
         * For list-edit template, we don't use radio buttons but select2 widget.
         */
        "radioenum": 'edit'
    };

    app.events.on("app:init", function() {

        var _fieldProto = _.clone(app.view.Field.prototype);
        _.extend(app.view.Field.prototype, {

            /**
             * Template for the exclamation mark icon added when decorating errors
             */
            exclamationMarkTemplate: Handlebars.compile(
                '<span class="error-tooltip  add-on" rel="tooltip" data-original-title="{{arrayJoin this ", "}}"><i class="icon-exclamation-sign"></i></span>'
            ),

            /**
             * Handle validation errors
             * Set to edit mode and decorates the field
             * @param {Object} errors The validation error(s) affecting this field
             */
            handleValidationError: function(errors) {
                this.clearErrorDecoration();
                _.defer(function(field){
                    field._errors = errors;
                    field.setMode('edit');
                    field.decorateError(errors);
                }, this);

                this.$el.off("keydown.record");
                $(document).off("mousedown.record" + this.name);
            },

            /**
             * Remove the old view's css class (e.g. detail, edit)
             * currently maps the action directly to the css class name
             * but may be overridden in the future.
             *
             * @param {String} action the name of the action to remove
             * @protected
             */
            _removeViewClass: function(action) {
                // in case our getFieldElement has been overridden, use this.$el directly
                this.$el.removeClass(action);
            },

            /**
             * Add the new view's css class (e.g. detail, edit)
             * currently maps the action directly to the css class name
             * but may be overridden in the future.
             *
             * @param {String} action the name of the action to remove
             * @protected
             */
            _addViewClass: function(action) {
                // in case our getFieldElement has been overridden, use this.$el directly
                this.$el.addClass(action);
            },

            /**
             * Override _render to redecorate fields if field is on error state
             * and to add view action CSS class.
             */
            _render: function() {
                // This is hacky but tooltips are appended to body and when the field rerenders we lose control of
                // shown tooltips.
                $('body > .tooltip').remove();
                var isErrorState = this.$('.add-on.error-tooltip').length > 0;

                _fieldProto._render.call(this);

                this._addViewClass(this.action);
                if (isErrorState) {
                    this.decorateError(this._errors);
                }
                if(this.def.required){
                    this.clearRequiredLabel();
                    if(this.action === "edit"){
                        this.decorateRequired();
                    }
                }
            },
            /**
             * Default implementation of Required decoration
             */
            decorateRequired: function(){
                var useLabels = _useRequiredLabels[this.type];
                useLabels = _.isString(useLabels) ? (useLabels === this.tplName) : useLabels;
                if(useLabels){
                    this.setRequiredLabel();
                } else {
                    // Most fields use Placeholder
                    this.setRequiredPlaceholder();
                }

            },

            /**
             * Add Required placeholder for input, select kinds of fields
             * @param element (Optional) element to attach placeholder
             */
            setRequiredPlaceholder: function(element){
                var el = element || this.$(this.fieldTag).first();
                var old = el.attr("placeholder");
                var requiredPlaceholder = app.lang.get("LBL_REQUIRED_FIELD", this.module);
                var newPlaceholder = requiredPlaceholder;
                if(old){
                    // If there is an existing placeholder then add required label after it
                    newPlaceholder =  old + " (" + requiredPlaceholder + ")";
                }
                el.attr("placeholder", newPlaceholder);
            },

            /**
             * Add Required label to field's label for fields that don't support placeholders
             * @param element (Optional) any element that is enclosed by field's record-cell
             */
            setRequiredLabel: function(element){
                var ele = element || this.$el;
                var $label = ele.closest('.record-cell').find(".record-label");
                $label.append(' <span data-required="required">('+app.lang.get("LBL_REQUIRED_FIELD", this.module)+')</span>');
            },

            /**
             * Remove default Required label from field labels
             * @param element (Optional) any element that is enclosed by field's record-cell
             */
            clearRequiredLabel: function(element){
                var ele = element || this.$el;
                var $label = ele.closest('.record-cell').find('span[data-required]');
                $label.remove();
            },

            /**
             * {@inheritdoc}
             *
             * Override setMode to remove any stale view action CSS classes.
             * @override
             */
            setMode: function(name) {
                this._removeViewClass(this.action);

                _fieldProto.setMode.call(this,name);
            },
            /**
             * Decorate error gets called when this Field has a validation error.  This function applies custom error
             * styling appropriate for this field.
             * The field is put into 'edit' mode prior to this this being called.
             *
             * Fields should override/implement this when they need to provide custom error styling for different field
             * types (like e-mail, etc).  You can also override clearErrorDecoration.
             *
             * @param {Object} errors The validation error(s) affecting this field
             */
            decorateError: function(errors) {
                var ftag = this.fieldTag || '',
                    $ftag = this.$(ftag),
                    errorMessages = [],
                    $tooltip;

                // Add error styling
                this.$el.closest('.record-cell').addClass('error');
                this.$el.addClass('error');
                // For each error add to error help block
                _.each(errors, function (errorContext, errorName) {
                    errorMessages.push(app.error.getErrorString(errorName, errorContext));
                });
                $ftag.wrap('<div class="input-append error '+ftag+'">');
                $ftag.after(this.exclamationMarkTemplate(errorMessages));
                $tooltip = this.$('.error-tooltip');
                if (_.isFunction($tooltip.tooltip)) {
                    var tooltipOpts = { container:'body', placement:'top', trigger: 'click' };
                    $tooltip.tooltip(tooltipOpts);
                }
            },

            /**
             * Remove error decoration from field if it exists.
             */
            clearErrorDecoration: function() {
                var ftag = this.fieldTag || '',
                    $ftag = this.$(ftag);
                // Remove previous exclamation then add back.
                this.$('.add-on').remove();
                var isWrapped = $ftag.parent().hasClass('input-append');
                if (isWrapped) {
                    $ftag.unwrap();
                }
                this.$el.removeClass(ftag);
                this.$el.removeClass("error");
                this.$el.closest('.record-cell').removeClass("error");
            }
        });
    });

})(SUGAR.App);
