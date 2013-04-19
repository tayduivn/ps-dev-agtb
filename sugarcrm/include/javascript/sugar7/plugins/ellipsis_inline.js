(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('ellipsis_inline', ['view'], {

            events:{
                'mouseenter .ellipsis_inline':'addTooltip'
            },

            /**
             * Manually toggle the dropdown on cell click
             * @param {Object}(optional) event jquery event object
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
