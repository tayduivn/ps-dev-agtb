(function(app) {
    app.events.on('app:init', function() {
        app.plugins.register('tooltip', ['layout', 'view'], {
            onAttach: function() {
                if (!_.isFunction($.fn.tooltip)) {
                    return;
                }

                this.events = _.extend({}, this.events, {
                    'mouseenter [rel="tooltip"]': 'onShowRecordViewTooltip',
                    'mouseleave [rel="tooltip"]': 'onHideRecordViewTooltip',
                    'click [rel="tooltip"]': 'unbindTooltip'
                });

                this.on('render', function() {
                    _.each(this.fields, function(field) {
                        field.before('render', this.unbindTooltip, this);
                    }, this);
                }, this);
            },

            /**
             * Creates tooltips for all controls on the record view and shows them
             * @param {Window.Event} mouse event.
             */
            onShowRecordViewTooltip: function(e) {
                this.$(e.currentTarget).tooltip({}).tooltip('show');
            },
            /**
             * Hides tooltips for all controls on the record view and destroy them
             * @param {Window.Event} mouse event.
             */
            onHideRecordViewTooltip: function(e) {
                this.$(e.currentTarget).tooltip('hide');
            },

            /**
             * Destroy the jQuery tooltip plugin.
             */
            unbindTooltip: function() {
                this.$('[rel=tooltip]').tooltip('destroy');
            },

            /**
             * Destory all tooltips that have been created
             */
            onDetach: function() {
                if (!_.isFunction($.fn.tooltip)) {
                    return;
                }
                this.unbindTooltip();
            }
        });
    });
})(SUGAR.App);

(function($) {
    $(function() {
        if (!Modernizr.touch) {
            return;
        }
        /**
         * {@inheritDoc}
         * Deactivate tooltip plugin on touch devices.
         */
        $.fn.tooltip = function() {
            return this;
        };
    });
})(jQuery);
