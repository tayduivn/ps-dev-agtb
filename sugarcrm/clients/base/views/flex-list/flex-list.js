/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

/**
 * @class View.FlexListView
 * @alias SUGAR.App.view.views.FlexListView
 * @extends View.ListView
 */
({
    extendsFrom: 'ListView',
    className: 'flex-list-view',
    // Model being previewed (if any)
    _previewed: null,
    initialize: function (options) {
        app.view.views.ListView.prototype.initialize.call(this, options);
        this.template = app.template.getView('flex-list');
        this.events = _.clone(this.events);
        _.extend(this.events, {
            'mouseenter .rowaction': 'showTooltip',
            'mouseleave .rowaction': 'hideTooltip'
        });
        //Store left column fields
        this.leftColumns = [];
        //Store right column fields
        this.rightColumns = [];
        this.addActions();
        //Store default and available(+visible) field names
        this._fields = this.parseFields();

        this.addPreviewEvents();

        $(window).on("resize.flexlist-" + this.cid, _.bind(this.resize, this));
    },
    addPreviewEvents: function () {
        //When clicking on eye icon, we need to trigger preview:render with model&collection
        this.context.on("list:preview:fire", function (model) {
            app.events.trigger("preview:render", model, this.collection, true);
        }, this);


        //When switching to next/previous record from the preview panel, we need to update the highlighted row
        app.events.on("list:preview:decorate", this.decorateRow, this);
        if (this.layout) {
            this.layout.on("list:sort:fire", function () {
                //When sorting the list view, we need to close the preview panel
                app.events.trigger("preview:close");
            }, this);
            this.layout.on("list:paginate:success", function () {
                //When fetching more records, we need to update the preview collection
                app.events.trigger("preview:collection:change", this.collection);
                // If we have a model in preview, redecorate the row as previewed
                if (this._previewed) {
                    this.decorateRow(this._previewed);
                }
            }, this);
        }
    },
    /**
     * Parse fields to identificate default and available(+visible) fields
     * @return {Object} field names classed by default / available / visible
     */
    parseFields: function () {
        var catalog = {
            'default': [], //Fields visible by default
            'available': [], //Fields hidden by default
            'visible': [], //Fields user wants to see,
            'options': []
        };
        // TODO: load field prefs and store names in this._fields.available.visible
        // no prefs so use viewMeta as default and assign hidden fields
        _.each(this.meta.panels, function (panel) {
            _.each(panel.fields, function (fieldMeta, i) {
                if (fieldMeta['default'] === false) {
                    catalog.available.push(fieldMeta);
                } else {
                    catalog['default'].push(fieldMeta);
                    catalog.visible.push(fieldMeta);
                }
                catalog.options.push(_.extend({
                    selected: (fieldMeta['default'] !== false)
                }, fieldMeta));
            }, this);
        }, this);
        return catalog;
    },
    /**
     * Add actions to left and right columns
     */
    addActions: function () {
        var meta = this.meta;
        if (_.isObject(meta.selection)) {
            switch (meta.selection.type) {
                case 'single':
                    this.addSingleSelectionAction();
                    break;
                case 'multi':
                    this.addMultiSelectionAction();
                    break;
                default:
                    break;
            }
        }
        if (meta && _.isObject(meta.rowactions)) {
            this.addRowActions();
        }
    },
    /**
     * Add single selection field to left column
     */
    addSingleSelectionAction: function () {
        var _generateMeta = function (name, label) {
            return {
                'type': 'selection',
                'name': name,
                'sortable': false,
                'label': label || ''
            };
        };
        var def = this.meta.selection;
        this.leftColumns.push(_generateMeta(def.name || this.module + '_select', def.label));
    },
    /**
     * Add multi selection field to left column
     */
    addMultiSelectionAction: function () {
        var _generateMeta = function (buttons) {
            return {
                'type': 'fieldset',
                'fields': [
                    {
                        'type': 'actionmenu',
                        'buttons': buttons || []
                    }
                ],
                'value': false,
                'sortable': false
            };
        };
        var buttons = this.meta.selection.actions;
        this.leftColumns.push(_generateMeta(buttons));
    },
    /**
     * Add fieldset of rowactions to the right column
     */
    addRowActions: function () {
        var _generateMeta = function (label, css_class, buttons) {
            return {
                'type': 'fieldset',
                'fields': [
                    {
                        'type': 'rowactions',
                        'label': label || '',
                        'css_class': css_class,
                        'buttons': buttons || []
                    }
                ],
                'value': false,
                'sortable': false
            };
        };
        var def = this.meta.rowactions;
        this.rightColumns.push(_generateMeta(def.label, def.css_class, def.actions));
    },
    /**
     * Decorate a row in the list that is being shown in Preview
     * @param model Model for row to be decorated.  Pass a falsy value to clear decoration.
     */
    decorateRow: function (model) {
        // If there are drawers, make sure we're updating only list views on active drawer.
        if (_.isUndefined(app.drawer) || app.drawer.isActive(this.$el)) {
            this._previewed = model;
            this.$("tr.highlighted").removeClass("highlighted current above below");
            if (model) {
                var rowName = model.module + "_" + model.get("id");
                var curr = this.$("tr[name='" + rowName + "']");
                curr.addClass("current highlighted");
                curr.prev("tr").addClass("highlighted above");
                curr.next("tr").addClass("highlighted below");
            }
        }
    },
    showTooltip: function (e) {
        this.$(e.currentTarget).tooltip("show");
    },
    hideTooltip: function (e) {
        this.$(e.currentTarget).tooltip("hide");
    },
    _renderHtml: function (ctx, options) {

        this.colSpan = this._fields.visible.length || 0;
        if (this.leftColumns.length) {
            this.colSpan++;
        }
        if (this.rightColumns.length) {
            this.colSpan++;
        }
        if (this.colSpan < 2) {
            this.colSpan = null;
        }
        app.view.View.prototype._renderHtml.call(this, ctx, options);

        if (this.leftColumns.length) {
            this.$el.addClass('left-actions');
        }
        if (this.rightColumns.length) {
            this.$el.addClass('right-actions');
        }
    },
    unbind: function() {
        $(window).off("resize.flexlist-" + this.cid);
        app.view.views.ListView.prototype.unbind.call(this);
    },
    /**
     * Updates the class of this flex list as scrollable or not.
     *
     * Runs debunced to postpone the execution when the window is resized.
     */
    resize: _.debounce(function() {
        var $content = this.$('.flex-list-view-content');
        if (!$content.length) {
            return;
        }
        var toggle = $content.get(0).scrollWidth > $content.width();
        this.$el.toggleClass('scroll-width', toggle);
    }, 300)

})
