({
    extendsFrom: 'ProfileactionsView',
    setCurrentUserData: function() {
        this.fullName = app.user.get("full_name");
        this.userName = app.user.get("user_name");
        var picture = app.user.get("picture");
        this.pictureUrl = picture ? app.api.buildFileURL({
            module: "Contacts",
            id: app.user.get("id"),
            field: "picture"
        }) : '';

        this.render();
    }
})
