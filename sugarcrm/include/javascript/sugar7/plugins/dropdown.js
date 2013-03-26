/**
 * This plugin exists because twitter bootstraps dropdown plugin returns false and stops event propagation on
 * dropdown toggling.
 *
 * @dependency twitter bootstrap
 */
(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('dropdown', ['view', 'layout'], {

            events:{
                'mouseleave .dropdown-menu': 'hideMenu'
            },

            // NOTE: this is a workaround for bootstrap dropdowns lack of support for events
            // we manually turn this into a dropdown and get rid of its events and reapply our own
            toggleDropdownHTML: function($currentTarget) {
                this.closeOpenDrops();
                $currentTarget.attr("data-toggle", "dropdown").dropdown('toggle');
                $currentTarget.off();
                this.delegateEvents();
                $currentTarget.closest('.btn-group').closest('li.dropdown').toggleClass('open');
            },
            // closes open dropdowns
            hideMenu: function(event) {
                $('.open .dtoggle').dropdown('toggle').closest('li.dropdown.open').removeClass('open');
            },
            // clears extra drop decoration and closes open dropdowns
            closeOpenDrops: function(event) {
                // this is because of the way the dom layers we watch for the bare container fluid spots
                if (event && !$(event.target).hasClass('container-fluid')) {
                    event.stopPropagation();
                    return;
                }
                $('.dropdown.open').removeClass('open');

                this.hideMenu();
            }
        });
    });
})(SUGAR.App);
