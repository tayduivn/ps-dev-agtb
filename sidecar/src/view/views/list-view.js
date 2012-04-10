(function(app) {

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.ListView
     * @alias SUGAR.App.layout.ListView
     * @extends View.View
     */
    app.layout.ListView = app.layout.View.extend({
        events: {
            'click [class*="orderBy"]': 'setOrderBy'
        },
        bind: function(context) {
            var collection = context.get("collection");
            _.each(collection.models, function(model) {
                var tr = this.$el.find('tr[name="' + model.module + '_' + model.get("id") + '"]');
                _.each(model.attributes, function(value, field) {
                    var el = tr.find('input[name="' + field + '"],span[name="' + field + '"]');
                    if (el.length > 0) {
                        //Bind input to the model
                        el.on("change", function(ev) {
                            model.set(field, el.val());
                        });
                        //And bind the model to the input
                        model.on("change:" + field, function(model, value) {
                            if (el[0].tagName.toLowerCase() == "input")
                                el.val(value); else
                                el.html(value);
                        });
                    }
                }, this);
            }, this);
        },
        /**
         * Sets order by on collection and view
         * @param {Object} event jquery event object
         */
        setOrderBy: function(event) {
            //set on this obj and not the prototype
            this.orderBy = {};

            //mapping for css
            var orderMap = {
                "desc": "down",
                "asc": "up"
            }

            //TODO probably need to check if we can sort this field from metadata
            var collection = this.context.get('collection');
            var fieldName = this.$(event.target).data('fieldname');

            if (!collection.orderBy) {
                collection.orderBy = {
                    field: "",
                    direction: ""
                };
            }
            ;

            var nOrder = "desc";

            // if same field just flip
            if (fieldName == collection.orderBy.field) {
                if (collection.orderBy.direction == "desc") {
                    nOrder = "asc";
                }
                collection.orderBy.direction = nOrder;
            } else {
                collection.orderBy.field = fieldName;
                collection.orderBy.direction = "desc";
            }

            // set it on the view
            this.orderBy.field = fieldName;
            this.orderBy.direction = orderMap[collection.orderBy.direction];

            // refetch the collection
            collection.fetch();
        }
    });

})(SUGAR.App);