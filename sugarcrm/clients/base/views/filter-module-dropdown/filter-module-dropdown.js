({
    tagName: "span",

    initialize: function(opts) {
        app.view.View.prototype.initialize.call(this, opts);
        // Select2 callbacks require us to _.bindAll(this).
        _.bindAll(this);

        this.layout.on("filter:change:module", this.handleChange);
    },

    _render: function() {
        var self = this;
        app.view.View.prototype._render.call(this);
        this.filterNode = this.$(".related-filter");
        this.filterList = [];

        this[(this.layout.layoutType === "record")? "getModuleListForSubpanels" : "getModuleListForRecords"]();
        this.filterNode.select2({
            data: this.filterList,
            multiple: false,
            minimumResultsForSearch: 7,
            formatSelection: this.formatSelection,
            formatResult: this.formatResult,
            dropdownCss: {width:'auto'},
            dropdownCssClass: 'search-related-dropdown',
            initSelection: this.initSelection
        });

        // Disable the module filter dropdown.
        if(this.layout.layoutType !== "record") {
            this.filterNode.select2("disable");
        }

        this.filterNode.off("change");
        this.filterNode.on("change", function(e) {
            var linkModule = e.val;
            if (self.layout.layoutType === "record" && linkModule !== "all_modules") {
                linkModule = app.data.getRelatedModule(self.module, linkModule);
            }
            self.layout.trigger("filter:change:module", linkModule, e.val);
        });
    },

    handleChange: function(linkModuleName, linkName, silent) {
        if (linkName !== "all_modules") {
            this.layout.trigger("filter:create:close");
            this.layout.trigger("subpanel:change", linkName);
        } else {
            this.layout.trigger("subpanel:change");
        }

        this.filterNode.select2("val", linkName || linkModuleName);
        if (!silent) {
            this.layout.trigger("filter:get", linkModuleName, linkName);
        }
    },

    getModuleListForSubpanels: function() {
        this.filterList.push({id: "all_modules", text: app.lang.get("LBL_TABGROUP_ALL")});

        var subpanels = this.pullSubpanelRelationships();
        _.each(subpanels, function(value, key){
            var module = app.data.getRelatedModule(this.module, value);
            if (app.acl.hasAccess("list", module)) {
                this.filterList.push({id:value, text:app.lang.get(key, this.module)});
            }
        }, this);
    },

    getModuleListForRecords: function() {
        this.filterList.push({id: this.module, text: app.lang.get('LBL_MODULE_NAME', this.module)});
    },

    pullSubpanelRelationships: function() {
        // Subpanels are retrieved from the global module and not the
        // subpanel module, therefore we use this.module instead of
        // this.currentModule.
        return app.metadata.getModule(this.module).layouts.subpanel.meta.subpanelList;
    },

    initSelection: function(el, callback) {
        var obj, data;
        if (el.val() !== "all_modules") {
            obj = _.findWhere(this.filterList, {id: el.val()});
            data = {id: obj.id, text: obj.text};
        } else {
            data = {id: "all_modules", text: (this.layout.layoutType === "record")? app.lang.get("LBL_TABGROUP_ALL") : this.module};
        }
        callback(data);
    },

    formatSelection: function(item) {
        var selectionLabel = app.lang.get("LBL_RELATED") + '<i class="icon-caret-down"></i>';

        if(this.layout.layoutType !== "record") {
            selectionLabel = app.lang.get("LBL_MODULE");
        }

        // Update the text for the selected module.
        this.$('.choice-related').html(item.text);
        return '<span class="select2-choice-type">' + selectionLabel + '</span>';
    },

    formatResult: function (option) {
        // TODO: Determine whether active filters should be highlighted in bold in this menu.
        return '<div><span class="select2-match"></span>'+ app.lang.get(option.text, "Filters") +'</div>';
    }
})
