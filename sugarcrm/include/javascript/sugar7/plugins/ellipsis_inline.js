(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('ellipsis_inline', ['view'], {

            events:{
                'mouseenter .ellipsis_inline':'addTooltip',
                'click .ellipsis_inline a':'destroyTooltip'
            },

            /**
             * Calculate if the text is too long and needs a tooltip
             * @param {Event} event jquery event object
             */
            addTooltip:function (event) {
                if (_.isFunction(app.utils.handleTooltip)) {
                    app.utils.handleTooltip(event, this);
                    if (_.isUndefined(this.tooltips)) {
                        this.tooltips = [];
                    }
                    this.tooltips.push($(event.target));
                }
            },

            /**
             * When you click on a link the mouseleave event is not fired so manually remove the tooltip
             * @param {Event} event
             */
            destroyTooltip: function(event) {
                this.$(event.currentTarget).parent().tooltip('destroy');
            },

            /**
             * Destory all tooltips that have been created
             */
            onDetach: function() {
                if (this.tooltips) {
                    _.each(this.tooltips, function(tooltip) {
                        tooltip.tooltip('destroy');
                    });
                    this.tooltips = null;
                }
            }
        });
    });
})(SUGAR.App);
