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
    events: {
        'click .more': 'toggleMoreLess',
        'click .less': 'toggleMoreLess',
        'mouseenter .ellipsis_inline':'addTooltip'
    },

    // "binary semaphore" for the pagination click event, this is needed for async changes to the preview model
    switching: false,
    hiddenPanelExists: false,
    addTooltip: function(event){
        if (_.isFunction(app.utils.handleTooltip)) {
            app.utils.handleTooltip(event, this);
        }
    },
    initialize: function(options) {
        _.bindAll(this);
        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = "detail";
        this.context.off("preview:render", null, this);
        this.context.on("preview:render", this._renderPreview, this);
        this.context.off("preview:close:fire", null, this);
        this.context.on("preview:close:fire", this.closePreview,  this);
        this.context.off("preview:collection:change", null, this);
        this.context.on("preview:collection:change", this.updateCollection, this);
        if(this.layout){
            this.layout.off("preview:pagination:fire", null, this);
            this.layout.on("preview:pagination:fire", this.switchPreview, this);
        }
    },

    _renderHtml: function() {
        app.view.View.prototype._renderHtml.call(this);
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
        // Close preview if we are already displaying this model
        if(self.model && model && self.model.get("id") == model.get("id")){
            self.layout.$(".preview-headerbar .closeSubdetail").click();
            self.model = null;
            return;
        }
        if(fetch){
            model.fetch({
                success: function(model) {
                    self.renderPreview(model, collection);
                }
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
            // Create a corresponding Bean and Context for clicked search result. It
            // might be a Case, a Bug, etc...we don't know, so we build dynamically.
            this.model = app.data.createBean(model.get('_module'), model.toJSON());
            this.collection = app.data.createBeanCollection(model.get('_module'), collection.models);
            this.context.set({
                'model': this.model,
                'module': this.model.module,
                'collection': this.collection
            });

            // Get the corresponding detail view meta for said module
            this.meta = app.metadata.getView(this.model.module, 'record') || {};
            this.meta = this._previewifyMetadata(this.meta);
            app.view.View.prototype._render.call(this);
            this.context.trigger("preview:open",this);
            this.context.trigger("list:preview:decorate", this.model, this);
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
            currModule = module || this.model.get("_module"),
            currID = id || this.model.get("postId") || this.model.get("id"),
            currIndex = index || _.indexOf(this.collection.models, this.collection.get(currID));

        if( this.switching ) {
            // We're currently switching previews, so ignore any pagination click events.
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
                            self.context.trigger("preview:render", model, self.collection, false);
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
        this.switching = false;
        this.model.clear();
        this.$el.empty();
    },

    updateCollection: function(collection) {
        this.context.set("collection", collection);
        if( this.collection ) {
            this.collection = collection;
        }
    }

})
