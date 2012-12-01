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
        'click .closeSubdetail': 'closePreview'
    },

    // "binary semaphore" for the pagination click event, this is needed for async changes to the preview model
    switching: false,

    initialize: function(options) {
        _.bindAll(this);

        app.view.View.prototype.initialize.call(this, options);
        this.fallbackFieldTemplate = "detail";
        this.context.off("togglePreview", null, this);
        this.context.on("togglePreview", this.togglePreview, this);
        this.context.off("preview:collection:change", null, this);
        this.context.on("preview:collection:change", this.updateCollection, this);
        this.layout.off("preview:pagination:fire", null, this);
        this.layout.on("preview:pagination:fire", this.switchPreview, this);
    },

    _render: function() {
        this.$el.parent().parent().addClass("container-fluid tab-content");
    },

    _renderHtml: function() {
        var fieldsArray;
        app.view.View.prototype._renderHtml.call(this);
    },

    togglePreview: function(model, collection) {
        var fieldsToDisplay = app.config.fieldsToDisplay || 5;
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
            this.meta = app.metadata.getView(this.model.module, 'detail') || {};
            // Clip meta panel fields to first N number of fields per the spec
            this.meta.panels[0].fields = _.first(this.meta.panels[0].fields, fieldsToDisplay);

            app.view.View.prototype._render.call(this);
        }
    },

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
                            self.model.set("_module", targetModule);
                            self.context.trigger("togglePreview", model, self.collection);
                            self.switching = false;
                        }
                    });
                }
            }
        }
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
