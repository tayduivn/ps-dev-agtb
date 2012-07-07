/**
 * Portal specific user extensions.
 */
(function(app) {
    app.user = _.extend(app.user);

    /**
     * Helper to determine if current user is a support portal user (essentially a Contact with portal enabled);
     * For example, we only show the profile and profile/edit pages if so.
     *
     * @return {Boolean} true if user is of type: support_portal, otherwise false (and user is a "normal user").
     */
    app.user.isSupportPortalUser = function() {
        return this.get('type') === 'support_portal';
    };

})(SUGAR.App);
