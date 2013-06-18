({
    extendsFrom: 'ProfileactionsView',
    /**
     * Sets the current user's information like full name, user name, avatar, etc.,
     * using portal's user module which is currently the Contacts module.
     * @protected
     */
    setCurrentUserData: function() {
        this.fullName = app.user.get("full_name");
        this.userName = app.user.get("portal_name");
        var picture = app.user.get("picture");
        this.pictureUrl = picture ? app.api.buildFileURL({
            module: "Contacts",
            id: app.user.get("id"),
            field: "picture"
        }) : '';

        this.render();
    }
})
