/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
(function(app) {
    /**
     * Shows and hide tooltips using jquery event delegation.
     */
    app.events.on('app:init', function() {

        /**
         * Checks if we should show a tooltip for the given element.
         *
         * @param {DOM} target The DOM element that has the tooltip.
         * @return {boolean} `true` if we should show a tooltip.
         * @private
         */
        var _shouldShowTooltip = function(target) {
            var $target = $(target);
            if (!$target.is(":hover")) return false;

            return ($target.attr('rel') === 'tooltip' || target.offsetWidth < target.scrollWidth);
        }

        /**
         * Checks if a tooltip exists on the target and if it is currently
         * displayed.
         *
         * @param {DOM} target The DOM element that has the tooltip.
         * @return {boolean} `true` if we should hide the tooltip.
         * @private
         */
        var _shouldHideTooltip = function(target) {
            var tooltip = app.utils.tooltip.get(target);
            if (!tooltip) return false;

            return tooltip.tip().hasClass('in');
        }

        /**
         * Initializes and shows a tooltip on the element that triggered the
         * event.
         *
         * @param event The `mouseenter` event.
         */
        var onMouseEnter = function(event) {
            var element = event.currentTarget;
            if (_shouldShowTooltip(element)) {
                var $element = $(element);
                app.utils.tooltip.initialize($element, {trigger: 'manual'}, $element.data('placement'));
                app.utils.tooltip.show(element);
            }
        }

        /**
         * Destroys the tooltip on the element that triggered the event.
         *
         * @param event The `mouseleave` event.
         */
        var onMouseLeave = function(event) {
            var element = event.currentTarget;
            if (_shouldHideTooltip(element)) {
                app.utils.tooltip.destroy($(element));
            }
        }

        /**
         * Destroys the tooltip on the clicked element.
         *
         * @param event The `click` event.
         */
        var onClick = function(event) {
            var element = event.currentTarget;
            if (!_shouldHideTooltip(element)) return;

            var tooltip = app.utils.tooltip.get(element);
            if (tooltip && tooltip.options && tooltip.options.trigger.indexOf('click') === -1) {
                app.utils.tooltip.destroy($(element));
            }
        }

        $('html').on({
            'mouseenter': function (event) {
                _.delay(onMouseEnter, 200, event);
            },
            'mouseleave': onMouseLeave,
            'click': onClick
        }, '.ellipsis_inline, [rel=tooltip]');

        // Where to put that ?
        //app.accessibility.run($tooltips, 'click');
    });
})(SUGAR.App);

(function($) {
    $(function() {
        if (!Modernizr.touch) {
            return;
        }
        /**
         * @inheritdoc
         * Deactivates tooltip plugin on touch devices.
         */
        $.fn.tooltip = function() {
            return this;
        };
    });
})(jQuery);