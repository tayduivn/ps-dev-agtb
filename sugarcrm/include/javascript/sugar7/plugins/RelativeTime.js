/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
(function(app) {

    if (!$.fn.liverelativedate) {
        return;
    }

    app.events.on('app:init', function() {

        /**
         * RelativeTime plugin keeps your relative time placeholders updated to
         * the minute.
         *
         * It uses `sugar.liverelativedate.js` jQuery plugin to keep all your
         * built relative time labels by the Handlebars helper
         * {@link Handlebars.helpers#relativeTime} updated.
         */
        app.plugins.register('RelativeTime', ['view', 'field'], {

            /**
             * When attaching the plugin, listen to the render event and make
             * all `[datatime]` elements refreshed live.
             *
             * @param {View.Component} component The component this plugin will
             *   be attached to.
             */
            onAttach: function(component) {
                component.on('render', function() {
                    component.$('[datetime]').liverelativedate();
                });
            },

            /**
             * Confirm that on render the existing live dates are detached
             * before we render again.
             *
             * We don't use `before('render')` because other components might
             * return false and block the render to be triggered, and would
             * make this plugin not working properly until next unblocked
             * render.
             *
             * @protected
             */
            _render: function() {
                this.$('[datetime]').liverelativedate('destroy');
                Object.getPrototypeOf(this)._render.call(this);
            },

            /**
             * When detaching the plugin, make sure all previous elements will
             * be removed from the liverelativetime list to be updated.
             *
             * @param {View.Component} component The component this plugin is
             *   attached to.
             */
            onDetach: function(component) {
                component.$('[datetime]').liverelativedate('destroy');
            }
        });
    });
})(SUGAR.App);
