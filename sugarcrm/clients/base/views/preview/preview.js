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
    plugins: ['ellipsis_inline'],
    /**
     * Events related to the preview view:
     *  - preview:open                  indicate we must show the preview panel
     *  - preview:render                indicate we must load the preview with a model/collection
     *  - preview:collection:change     indicate we want to update the preview with the new collection
     *  - preview:close                 indicate we must hide the preview panel
     *  - preview:pagination:fire       (on layout) indicate we must switch to previous/next record
     *  - preview:pagination:update     (on layout) indicate the preview header needs to be refreshed
     *  - list:preview:fire             indicate the user clicked on the preview icon
     *  - list:preview:decorate         indicate we need to update the highlighted row in list view
     */
    events: {
        'click .more': 'toggleMoreLess',
        'click .less': 'toggleMoreLess'
    },

    // "binary semaphore" for the pagination click event, this is needed for async changes to the preview model
    switching: false,
    hiddenPanelExists: false,
    initialize: function(options) {
        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = "detail";
        app.events.off("preview:render", null, this).on("preview:render", this._renderPreview, this);
        app.events.off("preview:collection:change", null, this).on("preview:collection:change", this.updateCollection, this);
        app.events.off("preview:close", null, this).on("preview:close", this.closePreview,  this);
        if(this.layout){
            this.layout.off("preview:pagination:fire", null, this);
            this.layout.on("preview:pagination:fire", this.switchPreview, this);
        }
    },
    updateCollection: function(collection) {
        if( this.collection ) {
            this.collection = collection;
            this.showPreviousNextBtnGroup();
       }
    },

    _renderHtml: function(){
        this.showPreviousNextBtnGroup();
        app.view.View.prototype._renderHtml.call(this);
    },

    showPreviousNextBtnGroup:function() {
        var collection = this.collection;
        if(this.layout){
            if(collection){
                var recordIndex = collection.indexOf(collection.get(this.model.id));
                this.layout.previous = collection.models[recordIndex-1] ? collection.models[recordIndex-1] : undefined;
                this.layout.next = collection.models[recordIndex+1] ? collection.models[recordIndex+1] : undefined;
                this.layout.hideNextPrevious = _.isUndefined(this.layout.previous) && _.isUndefined(this.layout.next);
            } else {
                this.layout.hideNextPrevious = true;
            }
            // Need to rerender the preview header
            this.layout.trigger("preview:pagination:update");
        }
    },

    /**
     * Renders the preview dialog with the data from the current model and collection.
     * @param model Model for the object to preview
     * @param collection Collection of related objects to the current model
     * @param {Boolean} fetch Optional Indicates if model needs to be synched with server to populate with latest data
     * @private
     */
    _renderPreview: function(model, collection, fetch){
        var self = this;
        // If there are drawers there could be multiple previews, make sure we are only rendering preview for active drawer
        if(app.drawer && !app.drawer.isActive(self.$el)){
            return;  //This preview isn't on the active layout
        }
        // Close preview if we are already displaying this model
        if(self.model && model && self.model.get("id") == model.get("id")){
            // Remove the decoration of the highlighted row
            app.events.trigger("list:preview:decorate", false);
            // Close the preview panel
            app.events.trigger('preview:close');
            return;
        }
        if(fetch){
            model.fetch({
                success: function(model) {
                    self.renderPreview(model, collection);
                },
                fields : this.getFieldNames()
            });
        } else {
            self.renderPreview(model, collection);
        }
    },

    /**
     * Renders the preview dialog with the data from the current model and collection
     * @param model Model for the object to preview
     * @param collection Collection of related objects to the current model
     */
    renderPreview: function(model, collection) {
        if (model && collection) {
            this.model = app.data.createBean(model.module, model.toJSON());
            this.collection = app.data.createBeanCollection(model.module, collection.models);

            // Get the corresponding detail view meta for said module
            this.meta = app.metadata.getView(this.model.module, 'record') || {};
            this.meta = this._previewifyMetadata(this.meta);
            app.view.View.prototype._render.call(this);
            // Open the preview panel
            app.events.trigger("preview:open",this);
            // Highlight the row
            app.events.trigger("list:preview:decorate", this.model, this);
            if(!this.$el.is(":visible")) {
                this.context.trigger("openSidebar",this);
            }
        }
    },
    /**
     * Normalizes the metadata, and removes favorite fields, that gets shown in Preview dialog
     * @param meta Layout metadata to be trimmed
     * @return Returns trimmed metadata
     * @private
     */
    _previewifyMetadata: function(meta){
        var trimmed = $.extend(true, {}, meta); //Deep copy
        this.hiddenPanelExists = false; // reset
        _.each(trimmed.panels, function(panel){
            if(panel.header){
                panel.header = false;
                panel.fields = _.filter(panel.fields, function(field){
                    //Don't show favorite icon in Preview, it's already on list view row
                    return field.type != 'favorite';
                });
            }
            //Keep track if a hidden panel exists
            if(!this.hiddenPanelExists && panel.hide){
                this.hiddenPanelExists = true;
            }
        }, this);
        return trimmed;
    },
    /**
     * Switches preview to left/right model in collection.
     * @param {String} data.direction Direction that we are switching to, either 'left' or 'right'.
     * @param index Optional current index in list
     * @param id Optional
     * @param module Optional
     */
    switchPreview: function(data, index, id, module) {
        var self = this,
            currModule = module || this.model.module,
            currID = id || this.model.get("postId") || this.model.get("id"),
            currIndex = index || _.indexOf(this.collection.models, this.collection.get(currID));

        if( this.switching || this.collection.models.length < 2) {
            // We're currently switching previews or we don't have enough models, so ignore any pagination click events.
            return;
        }
        this.switching = true;

        if( data.direction === "left" && (currID === _.first(this.collection.models).get("id")) ||
            data.direction === "right" && (currID === _.last(this.collection.models).get("id")) ) {
            this.switching = false;
            return;
        }
        else {
            // We can increment/decrement
            data.direction === "left" ? currIndex -= 1 : currIndex += 1;

            // If there is no target_id, we don't have access to that activity record
            // The other condition ensures we're previewing from activity stream items.
            if( _.isUndefined(this.collection.models[currIndex].get("target_id")) &&
                this.collection.models[currIndex].get("activity_data") ) {

                currID = this.collection.models[currIndex].id;
                this.switching = false;
                this.switchPreview(data, currIndex, currID, currModule);
            }
            else {
                var targetModule = this.collection.models[currIndex].get("target_module") || currModule,
                    moduleMeta = app.metadata.getModule(targetModule);

                // Some activity stream items aren't previewable - e.g. no detail views
                // for "Meetings" module.
                if( moduleMeta && _.isUndefined(moduleMeta.views.detail) ) {
                    currID = this.collection.models[currIndex].id;
                    this.switching = false;
                    this.switchPreview(data, currIndex, currID, currModule);
                }
                else {
                    this.model = app.data.createBean(targetModule);

                    if( _.isUndefined(this.collection.models[currIndex].get("target_id")) ) {
                        this.model.set("id", this.collection.models[currIndex].get("id"));
                    }
                    else
                    {
                        this.model.set("postId", this.collection.models[currIndex].get("id"));
                        this.model.set("id", this.collection.models[currIndex].get("target_id"));
                    }

                    this.model.fetch({
                        success: function(model) {
                            model.set("_module", targetModule);
                            self.model = null;
                            //Reset the preview
                            app.events.trigger("preview:render", model, self.collection, false);
                            self.switching = false;
                        }
                    });
                }
            }
        }
    },
    toggleMoreLess: function() {
        this.$(".less").toggleClass("hide");
        this.$(".more").toggleClass("hide");
        this.$(".panel_hidden").toggleClass("hide");
    },

    closePreview: function() {
        if(_.isUndefined(app.drawer) || app.drawer.isActive(this.$el)){
            this.switching = false;
            delete this.model;
            delete this.collection;
            this.$el.empty();
        }
    }
})
