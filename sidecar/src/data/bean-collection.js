(function(app) {

    /**
     * Represents a base class for all bean collection classes.
     */
    app.augment("BeanCollection", Backbone.Collection.extend({

        // Nothing here so far...

        toString: function() {
            return "coll:" + this.module + "/" + this.beanType;
        }

    }), false);

})(SUGAR.App);