/**
 * Base bean collection class.
 *
 * @class Data.BeanCollection
 * @alias SUGAR.App.BeanCollection
 * @extends Backbone.Collection
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
         * Options:
         *
         * - relate: boolean flag indicating that relationships should be fetched.
         * All other options are standard options outlined in the backbone docs.
         * User {@link Data.BeanCollection#paginate} for details about pagination options.
         *
         * Triggers <code>app:collection:fetch</code> event.
         * @param options(optional) fetch options
         */
        fetch: function(options) {
            options = options || {};
            this.fields = (options && options.fields) ? options.fields : null;
            var origSuccess = options.success,
                that = this;

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
         * Options:
         *
         * - page: page index (integer) from the current page to paginate to.
         * - relate: boolean flag indicating that relationships should be fetched.
         * - add: boolean flag indicating if new records should be appended to the collection.
         * - success: success callback.
         * - error: error callback.
         * All other options are standard options outlined in the backbone docs.
         * @param options(optional) fetch options
         */
        paginate: function(options) {
            options = options || {};
            options.page = options.page || 1;
            if(options && !options.fields) {
                options.fields = this.fields;
            }

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

}(SUGAR.App));
