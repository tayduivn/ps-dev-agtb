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
        set: function(acls) {
            if (acls) {
                this.acls = acls;
            }
        },
        hasModuleAccess: function(module, action, model) {

        },
        hasFieldAccess: function(module, fieldName, action, model) {
            //TODO Update this to get apps current user id
            // get current users ID
            var myID = "seed_sally_id";

            var hasAccess = true;
            var access = "yes";

            if (this.acls && this.acls[module] && this.acls[module].fields[fieldName] && this.action2acl[action]) {
                access = this.acls[module].fields[fieldName][this.action2acl[action]];
            }

            if (access == "no") {
                hasAccess = false;
            }

            if (access == "owner" && model && model.get('assigned_user_id') != myID) {
                hasAccess = false;
            }

            return hasAccess;
        }
    });
})(SUGAR.App);