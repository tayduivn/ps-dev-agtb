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
 * @class View.Views.Base.PreviewView
 * @alias SUGAR.App.view.views.BasePreviewView
 * @extends View.View
 */
({
    plugins: ['ToggleMoreLess', 'Editable', 'ErrorDecoration'],
    fallbackFieldTemplate: 'detail',
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

    // "binary semaphore" for the pagination click event, this is needed for async changes to the preview model
    switching: false,

    hiddenPanelExists: false,

    events: {
        'click [data-action=save]': 'saveClicked',
        'click [data-action=cancel]': 'cancelClicked'
    },

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);
        this.action = 'detail';
        this._delegateEvents();
        this.collection = app.data.createBeanCollection(this.module);
    },

    /**
     * Show the save and cancel buttons in the preview-header and
     * hide the left, right and x buttons if user has acl access
     *
     */
    showSaveAndCancel: function() {
        if (app.acl.hasAccessToModel('edit', this.model)) {
            this.layout.$('.save-btn, .cancel-btn').show();
            this.layout.$('.btn-left, .btn-right, .closeSubdetail').hide();
        }
    },

    /**
     * Hide the save and cancel buttons and show the left, right and
     * x buttons
     *
     */
    hideSaveAndCancel: function() {
        this.layout.$('.save-btn, .cancel-btn').hide();
        this.layout.$('.btn-left, .btn-right, .closeSubdetail').show();
    },

    /**
     * When clicking on save, validate all the fields
     *
     */
    saveClicked: function() {
        this.model.doValidate(this.getFields(this.module), _.bind(this.validationComplete, this));
    },

    /**
     * Called each time a validation pass is completed on the model.
     *
     * Enables the action button and calls {@link #handleSave} if the model is
     * valid.
     *
     * @param {boolean} isValid TRUE if model is valid.
     */
    validationComplete: function(isValid) {
        if (isValid) {
            this.handleSave();
        }
    },

    /**
     * Runs when validation is successful
     * Returns the preview to detail view
     *
     */
    handleSave: function() {
        if (this.disposed) {
            return;
        }
        this._saveModel();
        this.hideSaveAndCancel();

        if (!this.disposed) {
            this.setRoute();
            this.unsetContextAction();
            this.toggleFields(this.editableFields, false);
        }
    },

    /**
     * Saves the model
     *
     * @private
     */
    _saveModel: function() {
        var options,
            successCallback = _.bind(function() {
                // Loop through the visible subpanels and have them sync. This is to update any related
                // fields to the record that may have been changed on the server on save.
                _.each(this.context.children, function(child) {
                    if (!_.isUndefined(child.attributes) && !_.isUndefined(child.attributes.isSubpanel)) {
                        if (child.attributes.isSubpanel && !child.attributes.hidden) {
                            child.reloadData({recursive: false});
                        }
                    }
                });
                if (this.createMode) {
                    app.navigate(this.context, this.model);
                } else if (!this.disposed && !app.acl.hasAccessToModel('edit', this.model)) {
                    //re-render the view if the user does not have edit access after save.
                    this.render();
                }
            }, this);

        //Call editable to turn off key and mouse events before fields are disposed (SP-1873)
        this.turnOffEvents(this.fields);

        options = {
            showAlerts: true,
            success: successCallback,
            error: _.bind(function(error) {
                if (error.status === 412 && !error.request.metadataRetry) {
                    this.handleMetadataSyncError(error);
                } else if (error.status === 409) {
                    app.utils.resolve409Conflict(error, this.model, _.bind(function(model, isDatabaseData) {
                        if (model) {
                            if (isDatabaseData) {
                                successCallback();
                            } else {
                                this._saveModel();
                            }
                        }
                    }, this));
                } else {
                    this.editClicked();
                }
            }, this),
            lastModified: this.model.get('date_modified'),
            viewed: true
        };

        options = _.extend({}, options, this.getCustomSaveOptions(options));

        this.model.save({}, options);
    },

    /**
     * Deals with metadata sync error
     *
     * @param error
     */
    handleMetadataSyncError: function(error) {
        var self = this;
        //On a metadata sync error, retry the save after the app is synced
        self.resavingAfterMetadataSync = true;
        app.once('app:sync:complete', function() {
            error.request.metadataRetry = true;
            self.model.once('sync', function() {
                self.resavingAfterMetadataSync = false;
                //self.model.changed = {};
                app.router.refresh();
            });
            //add a new success callback to refresh the page after the save completes
            error.request.execute(null, app.api.getMetadataHash());
        });
    },

    /**
     * Updates url without triggering the router.
     *
     * @param {string} action Action to pass when building the route
     *   with {@link Core.Router#buildRoute}.
     */
    setRoute: function(action) {
        if (!this.meta.hashSync) {
            return;
        }
        app.router.navigate(app.router.buildRoute(this.module, this.model.id, action), {trigger: false});
    },

    /**
     * Unsets the `action` attribute from the current context.
     *
     * Once 'action' is unset, the action is 'detail' and the view will render
     * next in detail mode.
     */
    unsetContextAction: function() {
        this.context.unset('action');
    },

    /**
     * Dismisses all {@link #_viewAlerts alerts} defined in this view.
     *
     * @protected
     */
    _dismissAllAlerts: function() {
        if (_.isEmpty(this._viewAlerts)) {
            return;
        }
        _.each(_.uniq(this._viewAlerts), function(alert) {
            app.alert.dismiss(alert);
        });
        this._viewAlerts = [];
    },

    getCustomSaveOptions: function(options) {
        return {};
    },

    /**
     * When clciking cancel, return the preview view to detail state
     */
    cancelClicked: function() {
        this.handleCancel();
        this.clearValidationErrors(this.editableFields);
        this.setRoute();
        this.unsetContextAction();
    },

    /**
     * Undo the changes on the model
     */
    handleCancel: function() {
        this.model.revertAttributes();
        this.toggleFields(this.editableFields, false);
        this.hideSaveAndCancel();
        this._dismissAllAlerts();
    },

    /**
     * Add event listeners
     *
     * @private
     */
    _delegateEvents: function() {
        app.events.on('preview:render', this._renderPreview, this);
        app.events.on('preview:collection:change', this.updateCollection, this);
        app.events.on('preview:close', this.closePreview, this);

        // TODO: Remove when pagination on activity streams is fixed.
        app.events.on('preview:module:update', this.updatePreviewModule, this);

        if (this.layout) {
            this.layout.on('preview:pagination:fire', this.switchPreview, this);
            this.layout.on('preview:edit', this.handleEdit, this);
            this.layout.on('button:save_button:click', this.saveClicked, this);
            this.layout.on('button:cancel_button:click', this.cancelClicked, this);

        }
    },

    updateCollection: function(collection) {
        if( this.collection ) {
            this.collection.reset(collection.models);
            this.showPreviousNextBtnGroup();
       }
    },

    // TODO: Remove when pagination on activity streams is fixed.
    updatePreviewModule: function(module) {
        this.previewModule = module;
    },

    filterCollection: function() {
        this.collection.remove(_.filter(this.collection.models, function(model){
            return !app.acl.hasAccessToModel("view", model);
        }, this), { silent: true });
    },

    _renderHtml: function(){
        this.showPreviousNextBtnGroup();
        app.view.View.prototype._renderHtml.call(this);
    },

    /**
     * Show previous and next buttons groups on the view.
     *
     * This gets called everytime the collection gets updated. It also depends
     * if we have a current model or layout.
     *
     * TODO we should check if we have the preview open instead of doing a bunch
     * of if statements.
     */
    showPreviousNextBtnGroup: function () {
        if (!this.model || !this.layout || !this.collection) {
            return;
        }
        var collection = this.collection;
        if (!collection.size()) {
            this.layout.hideNextPrevious = true;
        }
        var recordIndex = collection.indexOf(collection.get(this.model.id));
        this.layout.previous = collection.models[recordIndex-1] ? collection.models[recordIndex-1] : undefined;
        this.layout.next = collection.models[recordIndex+1] ? collection.models[recordIndex+1] : undefined;
        this.layout.hideNextPrevious = _.isUndefined(this.layout.previous) && _.isUndefined(this.layout.next);

        // Need to rerender the preview header
        this.layout.trigger("preview:pagination:update");
    },

    /**
     * Renders the preview dialog with the data from the current model and collection.
     * @param model Model for the object to preview
     * @param collection Collection of related objects to the current model
     * @param {Boolean} fetch Optional Indicates if model needs to be synched with server to populate with latest data
     * @param {Number|String} previewId Optional identifier use to determine event origin. If event origin is not the same
     * but the model id is the same, preview should still render the same model.
     * @private
     */
    _renderPreview: function(model, collection, fetch, previewId) {
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

        if (app.metadata.getModule(model.module).isBwcEnabled) {
            // if module is in BWC mode, just return
            return;
        }

        if (model) {
            // Use preview view if available, otherwise fallback to record view
            var viewName = 'preview',
                previewMeta = app.metadata.getView(model.module, 'preview'),
                recordMeta = app.metadata.getView(model.module, 'record');
            if (_.isEmpty(previewMeta) || _.isEmpty(previewMeta.panels)) {
                viewName = 'record';
            }
            this.meta = this._previewifyMetadata(_.extend({}, recordMeta, previewMeta));
            this.renderPreview(model, collection);
            fetch && model.fetch({
                showAlerts: true,
                view: viewName
            });
        }

        this.previewId = previewId;
    },
    /**
     * Use the given model to render preview.
     * @param {Bean} model Model to render preview
     */
    switchModel: function(model) {
        this.model && this.model.abortFetchRequest();
        this.stopListening(this.model);
        this.model = model;

        // Close preview when model destroyed by deleting the record
        this.listenTo(this.model, 'destroy', function() {
            // Remove the decoration of the highlighted row
            app.events.trigger('list:preview:decorate', false);
            // Close the preview panel
            app.events.trigger('preview:close');
        });
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
            this.switchModel(model);
            this.render();

            // TODO: Remove when pagination on activity streams is fixed.
            if (this.previewModule && this.previewModule === "Activities") {
                this.layout.hideNextPrevious = true;
                this.layout.trigger("preview:pagination:update");
            }
            // Open the preview panel
            app.events.trigger('preview:open', this);
            // Highlight the row
            app.events.trigger("list:preview:decorate", this.model, this);
        }
    },

    /**
     * Normalizes the metadata, and removes favorite/follow fields that gets
     * shown in Preview dialog.
     *
     * @param meta Layout metadata to be trimmed
     * @return Returns trimmed metadata
     * @private
     */
    _previewifyMetadata: function(meta){
        this.hiddenPanelExists = false; // reset
        _.each(meta.panels, function(panel){
            if(panel.header){
                panel.header = false;
                panel.fields = _.filter(panel.fields, function(field){
                    //Don't show favorite or follow in Preview, it's already on list view row
                    return field.type != 'favorite' && field.type != 'follow';
                });
            }
            //Keep track if a hidden panel exists
            if(!this.hiddenPanelExists && panel.hide){
                this.hiddenPanelExists = true;
            }
        }, this);
        return meta;
    },
    /**
     * Switches preview to left/right model in collection.
     * @param {Object} data
     * @param {String} data.direction Direction that we are switching to, either 'left' or 'right'.
     * @param index Optional current index in list
     * @param id Optional
     * @param module Optional
     */
    switchPreview: function(data, index, id, module) {
        var currID = id || this.model.get('id'),
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
        } else {
            // We can increment/decrement
            data.direction === "left" ? currIndex -= 1 : currIndex += 1;

            //Reset the preview
            app.events.trigger('preview:render', this.collection.models[currIndex], null, true);
            this.switching = false;
        }
    },

    closePreview: function() {
        if(_.isUndefined(app.drawer) || app.drawer.isActive(this.$el)){
            this.switching = false;
            delete this.model;
            this.collection.reset();
            this.$el.empty();
        }
    },

    bindDataChange: function() {
        if(this.collection) {
            this.collection.on("reset", this.filterCollection, this);
            // when remove active model from collection then close preview
            this.collection.on("remove", function(model) {
                if (model && this.model && (this.model.get("id") == model.get("id"))) {
                    // Remove the decoration of the highlighted row
                    app.events.trigger("list:preview:decorate", false);
                    // Close the preview panel
                    app.events.trigger('preview:close');
                }
            }, this);
        }
    },
    /**
     * When clicking on the pencil icon, toggle all editable fields
     * to edit mode
     */
    handleEdit: function() {
        this.setEditableFields();
        this.toggleFields(this.editableFields, true, this.showSaveAndCancel());
    },

    /**
     * Set a list of editable fields
     */
    setEditableFields: function() {
        // we only want to edit non readonly fields
        this.editableFields = _.reject(this.fields, function(field) {
            return field.def.readOnly === true
                || !app.acl.hasAccessToModel('edit', this.model, field.name)
                || field.def.preview_edit === false;
        });
    },

    /**
     * Check if the model has any unsaved changes
     *
     * @return {boolean} `true` if current model contains unsaved changes,
     *   `false` otherwise.
     */
    hasUnsavedChanges: function() {
        var changedAttributes,
            editableFieldNames = [],
            unsavedFields;

        if (_.isUndefined(this.model)) {
            return false;
        }

        if (this.resavingAfterMetadataSync) {
            return false;
        }

        changedAttributes = this.model.changedAttributes(this.model.getSyncedAttributes());

        if (_.isEmpty(changedAttributes)) {
            return false;
        }

        // get names of all editable fields on the page including fields in a fieldset and add fields that
        // are not readonly and user has acl access
        _.each(this.meta.panels, function(panel) {
            _.each(panel.fields, function(field) {
                if (field.type === 'fieldset' && !field.readonly && _.every(field.fields, function(field) {
                        return app.acl.hasAccessToModel('edit', this.model, field.name);
                    }, this)) {
                    editableFieldNames.push(field.name)
                } else if (!field.readonly && app.acl.hasAccessToModel('edit', this.model, field.name)) {
                    editableFieldNames.push(field.name)
                }
            });
        });

        // check whether the changed attributes are among the editable fields
        unsavedFields = _.intersection(_.keys(changedAttributes), editableFieldNames);

        return !_.isEmpty(unsavedFields);
    }
})
