(function(app) {
    /**
     * ACL. Checks ACL access to modules and fields
     *
     * @class Core.acl
     * @singleton
     * @alias SUGAR.App.acl
     */
    app.augment("acl", {
        /**
         * Acl hash for current user
         * @property {Object}
         */
        acls: {},
        action2acl: {
            "edit": "write",
            "detail": "read",
            "list": "read"
        },
        /**
         * Sets acls
         * @param {Object} acls
         */
        set: function(acls) {
            if (acls) {
                this.acls = acls;
            }
        },
        /**
         * Checks acls to see if the current user has access to module views and fields
         * @param {String} module
         * @param {String} action
         * @param {Object} model
         * @param {String} [fieldName]
         */
        hasAccess: function(module, action, model, fieldName) {
            //TODO Update this to get apps current user id
            //TODO Also add override for app full admins remember to add a test this means you
            // get current users ID
            var myID = "seed_sally_id";

            var hasAccess = true;
            var access = "yes";
            // if we have a field check field level access
            if (fieldName && this.acls && this.acls[module] && this.acls[module].fields[fieldName] && this.action2acl[action]) {
                access = this.acls[module].fields[fieldName][this.action2acl[action]];
            }
            // check if just a module view
            if (!fieldName && this.acls && this.acls[module] && this.acls[module][action]) {
                access = this.acls[module][action];
            }

            if (access == "no") {
                hasAccess = false;
            }

            if (access == "owner" && model && model.get('assigned_user_id') != myID) {
                hasAccess = false;
            }
            // if module admin they have full access
            if (this.acls && this.acls[module] && this.acls[module].admin && this.acls[module].admin == "yes") {
                hasAccess = true;
            }

            return hasAccess;
        }
    });
})(SUGAR.App);