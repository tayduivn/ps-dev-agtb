(function (app) {
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
             * Override _render to redecorate fields if field is on error state
             */
            _render: function() {
                // This is hacky but tooltips are appended to body and when the field rerenders we lose control of
                // shown tooltips.
                $('body > .tooltip').remove();
                var isErrorState = this.$('.add-on.error-tooltip').length > 0;

                _fieldProto._render.call(this);

                if (isErrorState) {
                    this.decorateError(this._errors);
                }
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
                    var tooltipOpts = { container:'body', placement:'top' };
                    if (ftag.match(/select/i)) tooltipOpts.trigger = 'click';
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