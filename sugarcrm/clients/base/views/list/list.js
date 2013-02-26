/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    extendsFrom: 'EditableView',

    /**
     * View that displays a list of models pulled from the context's collection.
     * @class View.Views.ListView
     * @alias SUGAR.App.layout.ListView
     * @extends View.View
     */
    events: {
        'click [class*="orderBy"]':'setOrderBy',
        'mouseenter .rowaction': 'showTooltip',
        'mouseleave .rowaction': 'hideTooltip',
        'mouseenter tr':'showActions',
        'mouseleave tr':'hideActions',
        'mouseenter .ellipsis_inline':'addTooltip'
    },

    defaultLayoutEvents: {
        "list:search:fire": "fireSearch",
        "list:paginate:success": "paginateSuccess",
        "list:filter:toggled": "filterToggled",
        "list:alert:show": "showAlert",
        "list:alert:hide": "hideAlert",
        "list:sort:fire": "sort"
    },

    defaultContextEvents: {},
    
    // Model being previewed (if any)
    _previewed: null,
    
    addTooltip: function(event){
        if (_.isFunction(app.utils.handleTooltip)) {
            app.utils.handleTooltip(event, this);
        }
    },

    initialize: function(options) {
        //Grab the list of fields to display from the main list view (assuming initialize is being called from a subclass)
        var listViewMeta = this.filterFields(JSON.parse(JSON.stringify(app.metadata.getView(options.module, 'list') || {})));

        //Extend from an empty object to prevent polution of the base metadata
        options.meta = _.extend({}, listViewMeta, JSON.parse(JSON.stringify(options.meta || {})));
        options.meta.type = options.meta.type || 'list';

        _.each(options.meta.panels, function(panel) {
            this.populatePanelMetadata(panel, options);
        }, this);

        app.view.View.prototype.initialize.call(this, options);

        this.fallbackFieldTemplate = 'list-header';
        this.action = 'list';

        this.attachEvents();
        
        //When clicking on eye icon, we need to trigger preview:render with model&collection
        this.context.on("list:preview:fire", function(model) {
            app.events.trigger("preview:render", model, this.collection, true);
        }, this);
        
        //When switching to next/previous record from the preview panel, we need to update the highlighted row
        app.events.on("list:preview:decorate", this.decorateRow, this);
        app.events.on("list:filter:fire", this.filterList, this);

        // Dashboard layout injects shared context with limit: 5. 
        // Otherwise, we don't set so fetches will use max query in config.
        this.limit = this.context.has('limit') ? this.context.get('limit') : null;
    },

    /**
     * Takes the defaultListEventMap and listEventMap and binds the events. This is to allow views that
     * extend ListView to specify their own events.
     */
    attachEvents: function() {
        this.layoutEventsMap = _.extend(this.defaultLayoutEvents, this.layoutEvents); // If undefined nothing will be added.
        this.contextEventsMap = _.extend(this.defaultContextEvents, this.contextEvents);

        if (this.layout) {
            _.each(this.layoutEventsMap, function(callback, event) {
                this.layout.on(event, this[callback], this);
            }, this);
        }

        if (this.context) {
            _.each(this.contextEventsMap, function(callback, event) {
                this.context.on(event, this[callback], this);
            }, this);
        }
    },

    paginateSuccess: function() {
        //When fetching more records, we need to update the preview collection
        app.events.trigger("preview:collection:change", this.collection);
        if(!this.disposed) this.render();
    },

    sort: function() {
        //When sorting the list view, we need to close the preview panel
        app.events.trigger("preview:close");
    },

    filterFields: function(viewMeta){
        var self = this, fieldsRemoved = 0;
        this.hiddenFields = this.hiddenFields || [];
        // TODO: load stored field prefs
        // no prefs so use viewMeta as default and assign hidden fields
        _.each(viewMeta.panels, function(panel){
             for (var count = 0; count < panel.fields.length; count ++) {
                 fieldMeta = panel.fields[count];
                if (fieldMeta.default === false) {
                    self.hiddenFields.push(fieldMeta);
                    panel.fields.splice(count, 1);
                    // we need to recheck the last one because of the splice
                    count--;
                }
            };
        });
        return viewMeta;

    },
    filterList: function(filterDef, isNewFilter, scope) {
        var self = this;

        this.collection.fetch({
            relate: !!this.context.get('link'), // Double bang for boolean coercion.
            filter: filterDef,
            success: function() {
                if(isNewFilter) {
                    var method = "update";
                    if(scope.currentFilter === "all_records") {
                        method = "delete";
                    }
                    // We're dealing with a new collection that may not have the current preview record in the collection.
                    // Closing the preview will keep it from getting out of sync
                    app.events.trigger("preview:close");
                    var url = app.api.buildURL('Filters/' + self.options.module + '/used');
                    app.api.call(method, url, {filters: [scope.currentFilter]}, {});
                }
            }
        });
    },
    populatePanelMetadata: function(panel, options) {
        var meta = options.meta;
        if(meta.selection) {
            switch (meta.selection.type) {
                case "single":
                    panel = this.addSingleSelectionAction(panel, options);
                    break;
                case "multi":
                    panel = this.addMultiSelectionAction(panel, options);
                    break;
                default:
                    break;
            }
        }
        if(meta && meta.rowactions) {
            panel = this.addRowActions(panel, options);
        }
        return panel;
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
            params:{},
            fields:this.collection.fields || {}
        };
        if(term) {
            options.params.q = term;
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
        eventTarget = self.$(event.currentTarget);
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
            // Hide loading message
            app.alert.dismiss('loading_' + self.cid);

            self.layout.trigger("list:sort:fire", collection, self);
            if(!self.disposed) self.render();
        };
        if (this.context.get('link')) {
            options.relate = true;
        }

        // Display Loading message
        app.alert.show('loading_' + self.cid, {level:'process', title:app.lang.getAppString('LBL_LOADING')});

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
            params:{},
            fields:collection.fields ? collection.fields : this.collection
        };
        if (term) {
            options.params.q = term;
        }
        if (this.context.get('link')) {
            options.relate = true;
        }
        return options;
    },
    /**
     * Decorate a row in the list that is being shown in Preview
     * @param model Model for row to be decorated.  Pass a falsy value to clear decoration.
     */
    decorateRow: function(model){
        // If there are drawers, make sure we're updating only list views on active drawer.
        if(_.isUndefined(app.drawer) || app.drawer.isActive(this.$el)){
            this._previewed = model;
            this.$("tr.highlighted").removeClass("highlighted current above below");
            if(model){
                var rowName = model.module+"_"+ model.get("id");
                var curr = this.$("tr[name='"+rowName+"']");
                curr.addClass("current highlighted");
                curr.prev("tr").addClass("highlighted above");
                curr.next("tr").addClass("highlighted below");
            }
        }
    },
    addSingleSelectionAction: function(panel, options) {
        var meta = options.meta,
            module = options.module,
            singleSelect = [{
                'type' : 'selection',
                'name' : meta.selection.name || module + '_select',
                'sortable' : false,
                'label' : meta.selection.label || ''
            }];

        panel.fields = singleSelect.concat(panel.fields);
        return panel;
    },
    addMultiSelectionAction: function(panel, options) {
        var meta = options.meta,
            multiSelect = [{
            'type' : 'fieldset',
            'fields' : [{
                'type' : 'actionmenu',
                'buttons' : []
            }],
            'value' : false,
            'sortable' : false
        }];
        if (!_.isUndefined(meta.selection.actions)) {
            multiSelect[0].fields[0].buttons = meta.selection.actions;
        }
        panel.fields = multiSelect.concat(panel.fields);
        return panel;
    },
    addRowActions: function(panel, options) {
        var meta = options.meta,
            rowActions = {
            'type' : 'fieldset',
            'fields' : [{
                'type' : 'rowactions',
                'label' : meta.rowactions.label || '',
                'css_class' : meta.rowactions.css_class,
                'buttons' : []
            }],
            'value' : false,
            'sortable' : false
        };
        if (!_.isUndefined(meta.rowactions.actions)) {
            rowActions.fields[0].buttons = meta.rowactions.actions;
        }
        panel.fields = panel.fields.concat(rowActions);

        return panel;
    },
    showTooltip: function(e) {
        this.$(e.currentTarget).tooltip("show");
    },
    hideTooltip: function(e) {
        this.$(e.currentTarget).tooltip("hide");
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
