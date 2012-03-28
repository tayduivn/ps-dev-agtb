/**
 * Base bean collection class.
 *
 * @class BeanCollection
 * @alias SUGAR.App.BeanCollection
 */
(function(app) {

    app.augment("BeanCollection", Backbone.Collection.extend({

        constructor: function(models, options) {
            if (options && options.link) {
                this.link = options.link;
                delete options.link;
            }
            Backbone.Collection.prototype.constructor.call(this, models, options);
        },

        _prepareModel: function(model, options) {
            model = Backbone.Collection.prototype._prepareModel.call(this, model, options);
            if (model) model.link = this.link;
            return model;
        },

        /**
         * Fetches beans.
         *
         * Triggers <code>app:collection:fetch</code> event.
         * @param options(optional) standard options for fetch as outlined in the backbone docs
         */
        fetch: function(options) {
            options = options || {};
            var origSuccess = options.success;
            var that = this;
            options.success = function(args) {
                that.trigger(
                    /**
                     * Fired when the collection fetch operataion succeeds.
                     * @event
                     */
                    "app:collection:fetch"
                );
                if (origSuccess) {
                    origSuccess(args);
                }
            };
            return Backbone.Collection.prototype.fetch.call(this, options);
        },

        /**
         * Returns string representation useful for debugging:
         * <code>coll:[module-name]-[length]</code>  or
         * <code>coll:[related-module-name]/[id]/[module-name]-[length]</code> if it's a collection of related beans.
         * @return {String} string representation of this collection.
         */
        toString: function() {
            return "coll:" + (this.link ?
                (this.link.bean.module + "/" + this.link.bean.id + "/") : "") +
                this.module + "-" + this.length;
        },

        /**
         * Paginates a collection.
         *
         * Fetch options:
         *
         * - page: page index (integer) from the current page to paginate to.
         * - add: boolean flag indicating if new records should be appended to the collection.
         * - success: success callback.
         * - error: error callback.
         * @param options(optional) fetch options
         */
        paginate: function(options) {
            options = options || {};
            options.page = options.page || 1;

            // fix page number since our offset is already at the end of the collection subset
            options.page--;

            if (app.config.maxQueryResult) {
                options.offset = this.offset + (options.page * app.config.maxQueryResult);
            }

            this.fetch(options);
        },

        /**
         * Gets the current page of collection being displayed depending on the offset.
         * @return {Number} current page number.
         */
        getPageNumber: function() {
            var pageNumber = 1;
            if (this.offset && app.config.maxQueryResult) {
                pageNumber = Math.ceil(this.offset / app.config.maxQueryResult);
            }
            return pageNumber;
        }
    }), false);

})(SUGAR.App);