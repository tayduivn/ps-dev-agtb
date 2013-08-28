(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('tooltip', ['view'], {
            events: {
                'mouseenter [rel="tooltip"]': 'onShowRecordViewTooltip',
                'mouseleave [rel="tooltip"]': 'onHideRecordViewTooltip'
            },

            onAttach: function() {
                this.on('render', function() {
                    _.each(this.fields, function(field) {
                        field.before('render', this.unbindTooltip, this);
                    }, this);
                }, this);
            },

            /**
             * List of DOM elements for tip box.
             * Keep this dom in order to remove safe on dispose.
             *
             * @property
             */
            _tooltipElements: [],

            /**
             * Creates tooltips for all controls on the record view and shows them
             * @param {Window.Event} mouse event.
             */
            onShowRecordViewTooltip: function(e) {
                if (_.isFunction(this.$(e.currentTarget).tooltip)) {
                    this.$(e.currentTarget).tooltip().tooltip('show');
                    this._tooltipElements.push(this.$(e.currentTarget).data('tooltip').$tip);
                }
            },

            /**
             * Hides tooltips for all controls on the record view and destroy them
             * @param {Window.Event} mouse event.
             */
            onHideRecordViewTooltip: function(e) {
                if (_.isFunction(this.$(e.currentTarget).tooltip)) {
                    this.$(e.currentTarget).tooltip('hide');
                }
            },

            unbindTooltip: function() {
                if (_.isFunction(this.$el.tooltip)) {
                    this.$('[rel=tooltip]').tooltip('destroy');
                    _.each(this._tooltipElements, function($el) {
                        $el.remove();
                    }, this);
                    this._tooltipElements = [];
                }
            },

            /**
             * Destory all tooltips that have been created
             */
            onDetach: function() {
                this.unbindTooltip();
            }
        });
    });
})(SUGAR.App);
