({
    tagName: "span",

    events: {
        "click .choice-filter": "handleEditFilter"
    },

    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);
        // Select2 callbacks require us to _.bindAll(this).
        _.bindAll(this);

        this.layout.on("filter:change:filter", this.handleChange, this);
        this.layout.on("filter:change:module", this.handleModuleChange, this);
        this.layout.on("filter:render:filter", this._renderHtml, this);
    },

    _renderHtml: function() {
        var self = this;
        app.view.View.prototype._renderHtml.call(this);
        this.filterNode = this.$(".search-filter");
        this.filterList = [];

        this.layout.filters.each(function(model){
            this.filterList.push({id:model.id, text:model.get("name")});
        }, this);

        if(this.layout.canCreateFilter()) {
            this.filterList.push({id: "create", text: app.lang.get("LBL_FILTER_CREATE_NEW")});
        }

        this.filterNode.select2({
            data: this.filterList,
            multiple: false,
            minimumResultsForSearch: 7,
            formatSelection: this.formatSelection,
            formatResult: this.formatResult,
            dropdownCss: {width:'auto'},
            dropdownCssClass: 'search-filter-dropdown',
            initSelection: this.initSelection
        });

        if (!this.enabled) {
            this.filterNode.select2("disable");
        }

        this.filterNode.off("change");
        this.filterNode.on("change", function(e) {
            var val = e.val;
            self.layout.trigger("filter:change:filter", val);
        });
    },

    /**
     * Handler for when the custom filter dropdown value changes.
     * @param  {string} id      The GUID of the filter to apply.
     */
    handleChange: function(id) {
        var filter = this.layout.filters.get(id) || this.layout.emptyFilter;
        if (id === "create") {
            this.$('.choice-filter').css("cursor", "text");
            this.layout.trigger("filter:create:open");
        } else {
            if (filter.get("editable") === false) {
                this.layout.trigger("filter:create:close");
                this.$('.choice-filter').css("cursor", "text");
            } else {
                this.$('.choice-filter').css("cursor", "pointer");
            }

            if (this.layout.createPanelIsOpen()) {
                this.layout.trigger("filter:create:open", filter);
            }
        }

        this.filterNode.select2("val", id);
    },

    initSelection: function(el, callback) {
        var obj, data, model, allRecordsText;
        if (el.val() !== "create") {
            model = this.layout.filters.get(el.val());
            if (el.val() !== "all_records") {
                data = {id: model.id, text: model.get("name")};
            } else {
                allRecordsText = model.get("name") || app.lang.get("LBL_FILTER_ALL_RECORDS");
                data = {id: "all_records", text: allRecordsText};
            }

            callback(data);
        }
    },

    formatSelection: function(item) {
        var filterLabel = app.lang.get("LBL_FILTER"),
            selectionLabel = filterLabel;

        // Update the text for the selected filter.
        this.$('.choice-filter').html(app.lang.get(item.text, "Filters"));

        if(this.enabled) {
            selectionLabel += '<i class="icon-caret-down"></i>';
        }
        return '<span class="select2-choice-type">' + selectionLabel +'</span>';
    },

    formatResult: function (option) {
        // TODO: Determine whether active filters should be highlighted in bold in this menu.
        return '<div><span class="select2-match"></span>'+ app.lang.get(option.text, "Filters") +'</div>';
    },

    /**
     * Handler for when the user selects a filter in the filter bar.
     */
    handleEditFilter: function() {
        var filterId = this.filterNode.val(),
            filterModel = this.layout.filters.get(filterId);

        if (filterModel.get("editable")) {
            this.layout.trigger("filter:create:open", filterModel);
        }
    },

    /**
     * Handler for when the user selects a module in the filter bar.
     */
    handleModuleChange: function(linkModuleName, linkName) {
        this.enabled = (linkName !== "all_modules");
    }
})
