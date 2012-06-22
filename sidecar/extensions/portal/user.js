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
/*
current_user: {timezone:null, datepref:m/d/Y, timepref:H:i, type:support_portal,…}
account_ids: [b8b6d47a-b7b3-0290-c073-4fe4b90bc2c9]
0: "b8b6d47a-b7b3-0290-c073-4fe4b90bc2c9"
datepref: "m/d/Y"
full_name: "Ruben Pick"
id: "10a846d9-8b14-9f30-16d0-4fe4b952968d"
timepref: "H:i"
timezone: null
type: "support_portal"
user_id: "4ee508e9-4578-d13b-da2c-4fe4be78e573"
user_name: "support_portal"
*/
        return this.get('type') === 'support_portal';
    };

})(SUGAR.App);
