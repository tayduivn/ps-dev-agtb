({

/**
 * View that displays a list of models pulled from the context's collection.
 * @class View.Views.TableView
 * @alias SUGAR.App.layout.TableView
 * @extends View.View
 */
    events: {
        'click [class*="orderBy"]': 'setOrderBy',
        'mouseenter tr': 'showActions',
        'mouseleave tr': 'hideActions'
    },
    render: function() {
        var self = this;
        app.view.View.prototype.render.call(self);
        // off prevents multiple bindings for each render
        self.layout.off("list:search:fire", null, this); 
        self.layout.off("list:paginate:success", null, this); 
        self.layout.on("list:search:fire", self.fireSearch, this); 
        self.layout.on("list:paginate:success", self.render, this); 
    },

    fireSearch: function(term) {
        var collection, options;
        collection = this.context.get('collection');
        options = {
            params: { 
                q: term,
            },
            fields: collection.fields ? collection.fields : app.controller.context.get('collection')
        };
        collection.fetch(options);
    },

    /**
     * Sets order by on collection and view
     * @param {Object} event jquery event object
     */
    setOrderBy: function(event) {
        var orderMap, collection, fieldName, nOrder; 
        //set on this obj and not the prototype
        this.orderBy = this.orderBy || {};

        //mapping for css
        orderMap = {
            "desc": "_desc",
            "asc": "_asc"
        };

        //TODO probably need to check if we can sort this field from metadata
        collection = this.context.get('collection');
        fieldName = this.$(event.target).data('fieldname');

        if (!collection.orderBy) {
            collection.orderBy = {
                field: "",
                direction: ""
            };
        }

        nOrder = "desc";

        // if same field just flip
        if (fieldName === collection.orderBy.field) {
            if (collection.orderBy.direction === "desc") {
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
    },

    showActions: function(e) {
        $(e.currentTarget).children("td").children("span").children(".btn-group").show();
    },
    hideActions: function(e) {
        $(e.currentTarget).children("td").children("span").children(".btn-group").hide();
    },
    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    }
})

