({

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.ListView
     * @alias SUGAR.App.layout.ListView
     * @extends View.View
     */
    events:{
        'click [class*="orderBy"]':'setOrderBy',
        'click .preview-list-item':'previewRecord',
        'mouseenter .preview-list-item': 'showTooltip',
        'mouseleave .preview-list-item': 'hideTooltip',
        'mouseenter tr':'showActions',
        'mouseleave tr':'hideActions'
    },
    _renderHtml:function () {
        app.view.View.prototype._renderHtml.call(this);
        // off prevents multiple bindings for each render
        this.layout.off("list:search:fire", null, this);
        this.layout.off("list:paginate:success", null, this);
        this.layout.on("list:search:fire", this.fireSearch, this);
        this.layout.on("list:paginate:success", this.render, this);
        this.layout.off("list:filter:toggled", null, this);
        this.layout.on("list:filter:toggled", this.filterToggled, this);
        this.layout.off("list:alert:show", null, this);
        this.layout.on("list:alert:show", this.showAlert, this);
        this.layout.off("list:alert:hide", null, this);
        this.layout.on("list:alert:hide", this.hideAlert, this);

        // Dashboard layout injects shared context with limit: 5. 
        // Otherwise, we don't set so fetches will use max query in config.
        this.limit = this.context.get('limit') ? this.context.get('limit') : null;
    },
    showAlert: function(message) {
        this.$(".alert .container").html(message);
        this.$(".alert").removeClass("hide");
    },
    hideAlert: function() {
        this.$(".alert").addClass("hide");
    },
    filterToggled:function (isOpened) {
        this.filterOpened = isOpened;
    },
    fireSearch:function (term) {
        var options = {
            limit:this.limit || null,
            params:{
                q:term
            },
            fields:this.collection.fields || {}
        }
        //TODO: This should be handled automagically by the collection by checking its own tie to the context
        if (this.context.get('link')) {
            options.relate = true;
        }
        this.collection.fetch(options);
    },

    /**
     * Sets order by on collection and view
     * @param {Object} event jquery event object
     */
    setOrderBy:function (event) {
        var orderMap, collection, fieldName, nOrder, options, eventTarget, orderBy;
        var self = this;
        //set on this obj and not the prototype
        self.orderBy = self.orderBy || {};

        //mapping for css
        orderMap = {
            "desc":"_desc",
            "asc":"_asc"
        };

        //TODO probably need to check if we can sort this field from metadata
        collection = self.collection;
        eventTarget = self.$(event.target);
        fieldName = eventTarget.data('fieldname');

        // first check if alternate orderby is set for column
        orderBy = eventTarget.data('orderby');
        // if no alternate orderby, use the field name
        if (!orderBy) {
            orderBy = eventTarget.data('fieldname');
        }

        if (!collection.orderBy) {
            collection.orderBy = {
                field:"",
                direction:"",
                columnName:""
            };
        }

        nOrder = "desc";

        // if same field just flip
        if (orderBy === collection.orderBy.field) {
            if (collection.orderBy.direction === "desc") {
                nOrder = "asc";
            }
            collection.orderBy.direction = nOrder;
        } else {
            collection.orderBy.field = orderBy;
            collection.orderBy.direction = "desc";
        }
        collection.orderBy.columnName = fieldName;

        // set it on the view
        self.orderBy.field = orderBy;
        self.orderBy.direction = orderMap[collection.orderBy.direction];
        self.orderBy.columnName = fieldName;

        // Treat as a "sorted search" if the filter is toggled open
        options = self.filterOpened ? self.getSearchOptions() : {};

        // If injected context with a limit (dashboard) then fetch only that 
        // amount. Also, add true will make it append to already loaded records.
        options.limit = self.limit || null;
        options.success = function () {
            self.render();
        };
        if (this.context.get('link')) {
            options.relate = true;
        }

        // refetch the collection
        collection.fetch(options);
    },
    getSearchOptions:function () {
        var collection, options, previousTerms, term = '';
        collection = this.context.get('collection');

        // If we've made a previous search for this module grab from cache
        if (app.cache.has('previousTerms')) {
            previousTerms = app.cache.get('previousTerms');
            if (previousTerms) {
                term = previousTerms[this.module];
            }
        }
        // build search-specific options and return
        options = {
            params:{
                q:term
            },
            fields:collection.fields ? collection.fields : this.collection
        };
        if (this.context.get('link')) {
            options.relate = true;
        }
        return options;
    },
    previewRecord: function(e) {
        var self = this,
            el = this.$(e.target).closest("a"),
            module = el.attr("data-module"),
            id = el.attr("data-id"),
            model = app.data.createBean(module);

        model.set("id", id);
        model.fetch({
            success: function(model) {
                model.set("_module", module);

                if( _.isUndefined(self.context._callbacks) ) {
                    // Clicking preview on a related module, need the
                    // parent context instead
                    self.context.parent.trigger("togglePreview", model);
                }
                else {
                    self.context.trigger("togglePreview", model);
                }
            }
        });
    },
    showTooltip: function(e) {
        this.$(e.target).closest("a").tooltip("show");
    },
    hideTooltip: function(e) {
        this.$(e.target).closest("a").tooltip("hide");
    },
    showActions:function (e) {
        $(e.currentTarget).children("td").children("span").children(".btn-group").show();
    },
    hideActions:function (e) {
        $(e.currentTarget).children("td").children("span").children(".btn-group").hide();
    },
    bindDataChange:function () {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    }
})

