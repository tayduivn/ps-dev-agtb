/**
 * Represents a collection of relationships for a bean.
 *
 * @class RelationCollection
 */
(function(app) {

    app.augment("RelationCollection", Backbone.Collection.extend({
        model: app.Relation,

        constructor: function(models, options) {
            this.name = options.name;
            this.relationship = options.relationship;
            this.bean = options.bean;
        },

        parse: function(resp, xhr) {
            // TODO: We need to override parse method to properly build instances of Relation class
            // The shape of the response depends where it comes from: offline storage or REST API
            return resp;
        },

        /**
         * Returns string representation of a relationship collection useful for debugging.
         * <pre>
         * rel-coll:[relationship-name]-[length]
         * </pre>
         *
         * @return {String} string representation of this relation collection.
         */
        toString: function() {
            return "rel-coll:" + this.name + "-" + this.length;
        }

    }));

})(SUGAR.App);

