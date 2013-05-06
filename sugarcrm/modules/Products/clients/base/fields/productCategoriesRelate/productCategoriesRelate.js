({
    extendsFrom: 'RelateField',
    /**
     * used to control whether link is shown or not, based on whether the user has access, in the template.
     */
    hasAccess: false,

    buildRoute: function (module, idName) {
        if (app.acl.hasAccess("admin", "ProductCategories")) {
            this.hasAccess = true;
            app.view.invoke(this, 'field', 'relate', 'buildRoute', {args:[module, idName]});
        } else {
            this.hasAccess = false;
        }
    }
})
