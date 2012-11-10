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

    /**
     * Include salutation with full_name in Details view like the Contacts module (Bug58325)
     *
     * @param data Contact data being patched
     */
    app.user.addSalutationToFullName = function(data){
        debugger;
        var contactFields = app.metadata.getModule("Contacts").fields;
        if(_.isEmpty(data.name)){
            data.full_name = data.first_name + ' ' + data.last_name;
        }
        if(!_.isEmpty(data.salutation)){
            var salutation = app.lang.getAppListStrings(contactFields.salutation.options)[data.salutation];
            data.full_name = salutation + ' ' + data.full_name;
        }
    }

})(SUGAR.App);
