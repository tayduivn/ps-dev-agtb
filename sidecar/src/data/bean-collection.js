/**
 * Base bean collection class.
 *
 * @class BeanCollection
 * @alias SUGAR.App.BeanCollection
 */
(function(app) {

    app.augment("BeanCollection", Backbone.Collection.extend({

        /**
         * Returns string representation useful for debugging:
         * <code>coll:[module-name]/[bean-type]-[length]</code>
         * @return {String}
         */
        toString: function() {
            return "coll:" + this.module + "/" + this.beanType + "-" + this.length;
        }

    }), false);

})(SUGAR.App);