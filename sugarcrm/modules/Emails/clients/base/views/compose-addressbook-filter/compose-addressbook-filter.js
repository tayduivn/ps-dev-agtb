({
    moduleFilterList: [],
    moduleFilterNode: null,
    currentModule:    "all",
    currentSearch:    "",

    events: {
        "keyup .search-name": "throttledSearch",
        "paste .search-name": "throttledSearch"
    },

    initialize: function(options) {
        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, options);
    },

    _render: function() {
        app.view.View.prototype._render.call(this);
        this.updateModuleList();
        this.moduleFilterNode.select2("val", this.currentModule);
    },

    /**
     * Sets up the Select2 element for the module list, which includes storing the element, adding the requisite
     * events, and injecting the acceptable options into the list.
     */
    updateModuleList: function() {
        this.moduleFilterNode = this.$(".search-filter");
        this.moduleFilterList = [
            {id: "all", text: app.lang.get("LBL_TABGROUP_ALL")},
            {id: "accounts", text: app.lang.get("LBL_MODULE_NAME", "Accounts")},
            {id: "contacts", text: app.lang.get("LBL_MODULE_NAME", "Contacts")},
            {id: "leads", text: app.lang.get("LBL_MODULE_NAME", "Leads")},
            {id: "prospects", text: app.lang.get("LBL_MODULE_NAME", "Prospects")},
            {id: "users", text: app.lang.get("LBL_MODULE_NAME", "Users")}
        ];

        this.moduleFilterNode.select2({
            data:                    this.moduleFilterList,
            multiple:                false,
            minimumResultsForSearch: 7,
            formatSelection:         this.formatModuleSelection,
            formatResult:            this.formatResult,
            dropdownCss:             {width: "auto"},
            dropdownCssClass:        "search-filter-dropdown",
            initSelection:           this.initSelection,
            escapeMarkup: function(m) { return m; },
            width: 'off'
        });

        this.moduleFilterNode.off("change");
        this.moduleFilterNode.on("change", this.handleModuleSelection);
    },

    /**
     * Performs a search once the user has entered a term.
     */
    throttledSearch: _.debounce(function(e) {
        var newSearch = this.$(e.currentTarget).val();

        if (this.currentSearch !== newSearch) {
            this.currentSearch = newSearch;
            this.filterDataSetAndSearch();
        }
    }, 400),

    /**
     * Initialize the module selection with the value for "all" modules.
     *
     * @param el
     * @param callback
     */
    initSelection: function(el, callback) {
        if (el.is(this.moduleFilterNode)) {
            var model = _.findWhere(this.moduleFilterList, {id: el.val()});
            callback({id: model.id, text: model.text});
        }
    },

    /**
     * Function used to render the current selection. Format the module selection to display the name of the selected
     * module.
     *
     * @param item
     * @return {String}
     */
    formatModuleSelection: function(item) {
        // update the text for the selected module
        this.$(".choice-filter").html(item.text);

        return '<span class="select2-choice-type">' + app.lang.get("LBL_MODULE") + '<i class="icon-caret-down"></i></span>';
    },

    /**
     * Function used to render a result that the user can select.
     *
     * @param option
     * @return {String}
     */
    formatResult: function (option) {
        return '<div><span class="select2-match"></span>' + option.text + '</div>';
    },

    /**
     * Handler for when the module filter dropdown value changes, either via a click or manually calling jQuery's
     * .trigger("change") event.
     *
     * @param {obj} e jQuery Change Event Object
     * @param {string} overrideVal (optional) ID passed in when manually changing the filter dropdown value
     */
    handleModuleSelection: function(e, overrideVal) {
        var module = overrideVal || e.val || this.currentModule || "all";

        // only perform a search if the module is in the approved list
        if (!_.isEmpty(_.findWhere(this.moduleFilterList, {id: module}))) {
            this.currentModule = module;
            this.$(".choice-filter").css("cursor", "pointer");
            this.filterDataSetAndSearch();
        }
    },

    /**
     * Filters the data set by triggering an event that makes a call to the MailRecipient API.
     */
    filterDataSetAndSearch: function() {
        this.moduleFilterNode.select2("val", this.currentModule);
        this.context.trigger("compose:addressbook:search", this.currentModule, this.currentSearch);
    }
})
