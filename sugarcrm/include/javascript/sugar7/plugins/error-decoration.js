(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('error-decoration', ['view'], {

            /**
             * We need to add those events to the view to show/hide the tooltip that contains the error message
             */
            events:{
                'focus input':'showTooltip',
                'blur input':'hideTooltip',
                'focus textarea':'showTooltip',
                'blur textarea':'hideTooltip'
            },
            showTooltip:function (e) {
                _.defer(function () {
                    var $addon = this.$(e.currentTarget).next('.add-on');
                    if ($addon && _.isFunction($addon.tooltip)) {
                        $addon.tooltip('show');
                    }
                }, this);
            },
            hideTooltip:function (e) {
                var $addon = this.$(e.currentTarget).next('.add-on');
                if ($addon && _.isFunction($addon.tooltip)) $addon.tooltip('hide');
            },

            /**
             * Remove validation error decoration from fields
             *
             * @param fields Fields to remove error from
             */
            clearValidationErrors:function (fields) {
                fields = fields || _.toArray(this.fields);
                if (fields.length > 0) {
                    _.defer(function () {
                        _.each(fields, function (field) {
                            if (_.isFunction(field.clearErrorDecoration)) {
                                field.isErrorState = false;
                                field.clearErrorDecoration();
                            }
                        });
                    }, fields);
                }
                _.defer(function() {
                    this.$('.error').removeClass('error');
                    this.$('.error-tooltip').remove();
                }, this);
            }
        });
    });
})(SUGAR.App);