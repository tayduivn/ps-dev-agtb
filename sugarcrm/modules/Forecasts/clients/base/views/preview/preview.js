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

({
    extendsFrom: 'PreviewView',

    originalModel: undefined,

    _renderPreview: function(model, collection, fetch, previewId){
        var self = this;

        // If there are drawers there could be multiple previews, make sure we are only rendering preview for active drawer
        if(app.drawer && !app.drawer.isActive(this.$el)){
            return;  //This preview isn't on the active layout
        }

        // Close preview if we are already displaying this model
        if(this.model && model && (this.model.get("id") == model.get("id") && previewId == this.previewId)) {
            // Remove the decoration of the highlighted row
            app.events.trigger("list:preview:decorate", false);
            // Close the preview panel
            app.events.trigger('preview:close');
            return;
        }

        if (model) {
            // Get the corresponding detail view meta for said module.
            // this.meta needs to be set before this.getFieldNames is executed.
            this.meta = app.metadata.getView(model.get('parent_type'), 'record') || {};
            this.meta = this._previewifyMetadata(this.meta);
        }

        if (fetch) {
            var mdl = app.data.createBean(model.get('parent_type'), {'id' : model.get('parent_id')});
            this.originalModel = model;
            mdl.fetch({
                //Show alerts for this request
                showAlerts: true,
                success: function(model) {
                    self.renderPreview(model, collection);
                }
            });
        } else {
            this.renderPreview(model, collection);
        }

        this.previewId = previewId;
    },

    /**
     * Renders the preview dialog with the data from the current model and collection
     * @param model Model for the object to preview
     * @param collection Collection of related objects to the current model
     */
    renderPreview: function(model, newCollection) {
        if(newCollection) {
            this.collection.reset(newCollection.models);
        }

        if (model) {
            this.model = app.data.createBean(model.module, model.toJSON());

            app.view.View.prototype._render.call(this);

            // TODO: Remove when pagination on activity streams is fixed.
            if (this.previewModule && this.previewModule === "Activities") {
                this.layout.hideNextPrevious = true;
                this.layout.trigger("preview:pagination:update");
            }
            // Open the preview panel
            app.events.trigger("preview:open",this);
            // Highlight the row
            app.events.trigger("list:preview:decorate", this.originalModel, this);
            if(!this.$el.is(":visible")) {
                this.context.trigger("openSidebar",this);
            }
        }
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
            currIndex = index || _.indexOf(this.collection.models, this.collection.get(this.originalModel.get('id')));

        if( this.switching || this.collection.models.length < 2) {
            // We're currently switching previews or we don't have enough models, so ignore any pagination click events.
            return;
        }
        this.switching = true;
        debugger;
        if( data.direction === "left" && (currID === _.first(this.collection.models).get("parent_id")) ||
            data.direction === "right" && (currID === _.last(this.collection.models).get("parent_id")) ) {
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
                        this.model.set("id", this.collection.models[currIndex].get("parent_id"));
                    }
                    else
                    {
                        this.model.set("postId", this.collection.models[currIndex].get("id"));
                        this.model.set("id", this.collection.models[currIndex].get("target_id"));
                    }
                    this.originalModel = this.collection.models[currIndex];
                    this.model.fetch({
                        //Show alerts for this request
                        showAlerts: true,
                        success: function(model) {
                            model.set("_module", targetModule);
                            self.model = null;
                            //Reset the preview
                            app.events.trigger("preview:render", model, null, false);
                            self.switching = false;
                        }
                    });
                }
            }
        }
    }
})
