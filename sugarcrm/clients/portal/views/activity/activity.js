/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * View that displays the activity stream.
 * @class View.Views.ActivityView
 * @alias SUGAR.App.layout.ActivityView
 * @extends View.View
 */
({
    events: {
        'click .addNote': 'openNoteModal',
        'click .activity a': 'loadPreview',
        'click [name=show_more_button]': 'showMoreRecords'
    },

    plugins: ['Tooltip'],

    /**
     * @override
     * @param options
     */
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this._addPreviewEvents();
    },

    /**
     * Handlebars flag for when activity stream contains no items
     */
    emptyStream: false,

    /**
     * @override
     * @private
     */
    _render: function() {
        if(this.hasLoadedActivities()){
            this.emptyStream = this.collection.length < 1;
        }
        // Bug 54597 activity view not respecting list ACL
        var oViewName = this.name;
        this.name = 'list';
        app.view.View.prototype._render.call(this);
        this.name = oViewName;
    },

    /**
     * Test if activities collection has been fetched yet
     * @returns boolean TRUE if activities have been fetched
     */
    hasLoadedActivities: function(){
        //page has a value once fetch is complete
        return _.isNumber(this.collection.page);
    },

    /**
     * @override
     */
    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    },

    /**
     * Add preview events
     * @private
     */
    _addPreviewEvents: function() {
        //When switching to next/previous record from the preview panel, we need to update the highlighted row
        app.events.on("list:preview:decorate", this.decorateRow, this);
        this.collection.on("reset", function() {
            //When fetching more records, we need to update the preview collection
            app.events.trigger("preview:collection:change", this.collection);
            if (this._previewed) {
                this.decorateRow(this._previewed);
            }
        }, this);
    },

    /**
     * Load Preview
     * @param event
     */
    loadPreview: function(event) {
        // gets the activityId in the data attribute
        var $parent = this.$(event.currentTarget).parents("li.activity");
        var activityId = $parent.data("id");

        // gets the activity model
        var activity = this.collection.get(activityId);

        this.decorateRow(activity);
        app.events.trigger("preview:render", activity, this.collection, false);
    },

    /**
     * Decorate a row in the list that is being shown in Preview
     * @param model Model for row to be decorated.  Pass a falsy value to clear decoration.
     */
    decorateRow: function(model) {
        this._previewed = model;
        // UI fix
        this.$("li.activity").removeClass("on");
        if (model) {
            this.$("li.activity[data-id=" + model.get("id") + "]").addClass("on");
        }
    },

    /**
     * Open the modal for writing a note
     * @param event
     */
    openNoteModal: function(event) {
        if (Modernizr.touch) {
            app.$contentEl.addClass('content-overflow-visible');
        }
        // triggers an event to show the modal
        this.layout.trigger("app:view:activity:editmodal");
        this.$('li.open').removeClass('open');
        return false;
    },

    /**
     * Loads more notes
     * @param event
     */
    showMoreRecords: function(event) {
        var self = this, options;
        app.alert.show('show_more_records', {level: 'process', title: app.lang.getAppString('LBL_LOADING')});

        // If in "search mode" (the search filter is toggled open) set q:term param
        options = self.filterOpened ? self.getSearchOptions() : {};

        // Indicates records will be added to those already loaded in to view
        options.add = true;

        if (this.collection.link) {
            options.relate = true;
        }

        options.success = function() {
            app.alert.dismiss('show_more_records');
            self.layout.trigger("list:paginate:success");
            self.render();
            window.scrollTo(0, document.body.scrollHeight);
        };
        options.limit = this.limit;
        this.collection.paginate(options);
    }
})
