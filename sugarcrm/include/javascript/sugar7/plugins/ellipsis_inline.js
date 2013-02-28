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
                }
            }
        });
    });
})(SUGAR.App);
