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
         * Fetches beans
         *
         * Overloaded fetch to trigger app:collection:fetch
         * @param options(optional) standard options for fetch as outlined in the backbone docs
         */
        fetch: function(options) {
            options = options || {};
            var origSuccess = options.success;
            var that = this;
            options.success = function(args) {
                that.trigger("app:collection:fetch");
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
         * Paginates current collection.
         * @param {Object} options(optional) options.page is the n page from the current to paginate to, options.add will append new records
         */
        paginate: function(options) {
            var fetchOptions = {};
            options = options || {};
            options.page = options.page || 1;

            // fix page number since our offset is already at the end of the collection subset
            options.page--;

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
                fetchOptions.offset = this.offset + (options.page * app.config.maxQueryResult);
            }

            // get new records
            this.fetch(fetchOptions);

        },
        /**
         * gets current page of collection being displayed depending on offset
         * @return {Number} current ceil of offfset/maxQuery result default 1
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