(function(app) {
    /**
     * Checks ACL for modules and fields.
     *
     * @class Core.Acl
     * @singleton
     * @alias SUGAR.App.acl
     */
    app.augment("acl", {

        /**
         * Dictionary that maps actions to permissions.
         * @property {Object}
         */
        action2permission: {
            "edit": "write",
            "detail": "read",
            "list": "read"
        },

        /**
         * Checks acls to see if the current user has access to action on a given module's field.
         *
         * @param {String} action Action name.
         * @param {Object} module Module name.
         * @param {String} ownerId(optional) ID of the record's owner (`assigned_user_id` attribute).
         * @param {String} field(optional) Name of the model field.
         * @return {Boolean} Flag indicating if the current user has access to the given action.
         */
        hasAccess: function(action, module, ownerId, field) {
            //TODO Also add override for app full admins remember to add a test this means you
            var hasAccess = true,
                access = "yes",
                acls = app.metadata.getAcls()[module];

            // module admins have full access
            if (acls && acls.admin !== "yes") {
                // Check field level access
                if (field && acls.fields[field] && this.action2permission[action]) {
                    access = acls.fields[field][this.action2permission[action]];
                }

                // check module acl
                if (!field && acls[action]) {
                    access = acls[action];
                }

                if (access === "no") {
                    hasAccess = false;
                }
                else if (access === "owner" && ownerId !== app.user.id) {
                    hasAccess = false;
                }
            }

            return hasAccess;
        },
        /**
         * Checks acls to see if the current user has access to action on a given model's field.
         *
         * @param {String} action Action name.
         * @param {Object} model Model instance.
         * @param {String} field(optional) Name of the model field.
         * @return {Boolean} Flag indicating if the current user has access to the given action.
         */
        hasAccessToModel: function(action, model, field) {
            model = model || new Backbone.Model();
            if (action=='edit' && !model.get('id')){
                action = 'create';
            }
            return this.hasAccess(action, model.module, model.get("assigned_user_id"), field);
        }

    });

})(SUGAR.App);
