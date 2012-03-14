/**
 * Represents instance of a relationship between two beans.
 *
 * @class Relation
 */
(function(app) {

    app.augment("Relation", Backbone.Model.extend({

        /**
         * Returns string representation of a relationship useful for debugging.
         * <pre><code>
         * rel:[relationship-name]-[id1]-[id2]
         * </code><pre>
         *
         * @return {String} string representation of this relation.
         */
        toString: function() {
            return "rel:" + this.id;
        }

    }));

})(SUGAR.App);

