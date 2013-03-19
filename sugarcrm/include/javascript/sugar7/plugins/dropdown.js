/**
 * This plugin exists because twitter bootstraps dropdown plugin returns false and stops event propagation on
 * dropdown toggling.
 *
 * @dependency twitter bootstrap
 */
(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('dropdown', ['view'], {

            events:{
                'mouseleave .dropdown-menu': 'hideMenu'
            },

            // NOTE: this is a workaround for bootstrap dropdowns lack of support for events
            // we manually turn this into a dropdown and get rid of its events and reapply our own
            toggleDropdownHTML: function($currentTarget) {
                $currentTarget.attr("data-toggle", "dropdown").dropdown('toggle');
                $currentTarget.off();
                this.delegateEvents();
                $currentTarget.closest('.btn-group').closest('li.dropdown').toggleClass('open');
            },
            // hides all open menus
            hideMenu: function(event) {
                $('.open .dtoggle').dropdown('toggle').closest('li.dropdown.open').removeClass('open');
            }
        });
    });
})(SUGAR.App);
