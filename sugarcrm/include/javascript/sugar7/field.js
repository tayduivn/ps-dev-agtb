(function(app) {
    app.events.on("app:init", function() {

        _.extend(app.view.Field.prototype, {
            /**
             * Decorate error gets called when this Field has a validation error.  This function applies custom error
             * styling appropriate for this field.
             * The field is put into 'edit' mode prior to this this being called.
             *
             * Fields should override/implement this when they need to provide custom error styling for different field
             * types (like e-mail, etc).  Make sure to implement clearErrorDecoration too.
             *
             * @param {Object} errors The validation error(s) affecting this field
             */
            decorateError: function(errors) {
                var ftag, self = this;

                // need to add error styling to parent view element
                ftag = this.fieldTag || '';
                self.$('.help-block').html('');
                // Remove previous exclamation then add back.
                self.$('.add-on').remove();
                // Add error styling
                self.$el.addClass('input-append');
                self.$el.addClass(ftag);
                // For each error add to error help block
                _.each(errors, function(errorContext, errorName) {
                    self.$('.help-block').append(app.error.getErrorString(errorName, errorContext));
                });
                $('<span class="add-on"><i class="icon-exclamation-sign"></i></span>').insertBefore(self.$('.help-block'));
            },

            /**
             * Remove error decoration from field if it exists.
             * Fields should override this with the decorateError function as needed.
             */
            clearErrorDecoration: function() {
                var ftag;
                this.$('.help-block').html('');
                // Remove previous exclamation then add back.
                this.$('.add-on').remove();
                this.$el.removeClass('input-append');
                ftag = this.fieldTag || '';
                this.$el.removeClass(ftag);
            }
        });
    });

})(SUGAR.App);
