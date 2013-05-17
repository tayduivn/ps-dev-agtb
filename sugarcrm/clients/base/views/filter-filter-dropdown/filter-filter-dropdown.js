({
    tagName: "span",

    events: {
        "click .choice-filter": "handleEditFilter"
    },

    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);

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
            var isAllRecords = model.id !== "all_records" ? false : true;
            this.filterList.push({id:model.id, text: this.getTranslatedSelectionText(isAllRecords, model.get("name"))});
        }, this);

        if(this.layout.canCreateFilter()) {
            this.filterList.push({id: "create", text: app.lang.get("LBL_FILTER_CREATE_NEW")});
        }

        this.filterNode.select2({
            data: this.filterList,
            multiple: false,
            minimumResultsForSearch: 7,
            formatSelection: _.bind(this.formatSelection, this),
            formatResult: _.bind(this.formatResult, this),
            dropdownCss: {width:'auto'},
            dropdownCssClass: 'search-filter-dropdown',
            initSelection: _.bind(this.initSelection, this),
            escapeMarkup: function(m) { return m; },
            width: 'off'
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
            this.$('.choice-filter').css("cursor", "not-allowed");
            this.layout.trigger("filter:create:open", app.data.createBean('Filters'));
        } else {
            if (filter.get("editable") === false) {
                this.layout.trigger("filter:create:close");
                this.$('.choice-filter').css("cursor", "not-allowed");
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
        var data,
            model,
            val = el.val();

        if (val !== "create") {
            model = this.layout.filters.get(val);

            if (model) {
                if (val !== "all_records") {
                    data = {id: model.id, text: this.getTranslatedSelectionText(false, model.get("name"))};
                } else {
                    data = {id: "all_records", text: this.getTranslatedSelectionText(true, model.get("name"))};
                }
            } else {
                data = {id: "all_records", text: app.lang.get("LBL_FILTER_ALL_RECORDS")};
            }

            callback(data);
        }
    },

    formatSelection: function(item) {
        var filterLabel = app.lang.get("LBL_FILTER"),
            selectionLabel = filterLabel;

        // Update the text for the selected filter.
        this.$('.choice-filter').html(item.text);

        if(this.enabled) {
            selectionLabel += '<i class="icon-caret-down"></i>';
        }
        return '<span class="select2-choice-type">' + selectionLabel +'</span>';
    },

    formatResult: function (option) {
        // TODO: Determine whether active filters should be highlighted in bold in this menu.
        return '<div><span class="select2-match"></span>'+ option.text +'</div>';
    },

    /**
     * Handler for when the user selects a filter in the filter bar.
     */
    handleEditFilter: function() {
        var filterId = this.filterNode.val(),
            filterModel = this.layout.filters.get(filterId);

        if (filterModel && filterModel.get("editable") !== false) {
            this.layout.trigger("filter:create:open", filterModel);
        }
    },

    /**
     * Handler for when the user selects a module in the filter bar.
     */
    handleModuleChange: function(linkModuleName, linkName) {
        this.enabled = (linkName !== "all_modules");
    },

    /**
     * Translates the selection text's labels
     * @param isAllRecords
     * @param label
     * @returns {*}
     * @private
     */
    getTranslatedSelectionText: function(isAllRecords, label) {
        var translatedText, moduleName;

        if (isAllRecords) {
            moduleName = app.lang.get('LBL_MODULE_NAME', this.layout.layout.currentModule);
            translatedText = app.lang.get(label, null, {'moduleName': moduleName});
        }
        else {
            translatedText = app.lang.get(label, 'Filters');
        }
        return translatedText;
    },

    unbind: function() {
        this.filterNode.select2('destroy');
        app.view.View.prototype.unbind.call(this);
    }
})
