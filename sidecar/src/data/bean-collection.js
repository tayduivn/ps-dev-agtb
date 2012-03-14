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
        },
        /**
         * paginates current collection
         * @param {Object} options.page is the n page from the current to paginate to, options.add will append new records
         */
        paginate: function(options) {
            var fetchOptions = {};
            options = options || {};
            options.page = options.page || 1;

            // fix page number since our offset is already at the end of the collection subset
            if (options.page >= 0) {
                options.page--;
            }

            // can haz append?
            if (options.add && options.add === true) {
                fetchOptions.add = true;
            }

            // set callbacks
            if (options.success) {
                fetchOptions.success = options.success;
            }

            // set offset index
            if (app.config && app.config.maxQueryResult) {
                fetchOptions.offset = this.offset+(options.page*app.config.maxQueryResult);
            }

            // get new records
            this.fetch(fetchOptions);

        }
    }), false);

})(SUGAR.App);