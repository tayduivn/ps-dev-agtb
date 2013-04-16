({
    DUPLICATE_VIEW: 'duplicate',
    RECORD_VIEW: 'record',

    STATUS_INIT: 'init',
    STATUS_DIRTY: 'dirty',
    STATUS_COMPLETE: 'complete',

    enableDuplicateCheck: false,

    events:{
        'click .toggle-subview':'handleToggleClick'
    },

    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        _.bindAll(this);
        this.context.on("lead:convert:populate", this.handlePopulateRecords);
        this.context.on("lead:convert:"+this.meta.module+":show", this.handleShow);
        this.context.on("lead:convert:"+this.meta.module+":hide", this.handleHide);
        this.context.on("lead:convert:"+this.meta.module+":validate", this.runValidation);
        this.context.on("lead:convert:"+this.meta.module+":enable", this.handleEnablePanel);
        this.initiateDependentModuleDuplicateViewTriggers();

        this.defaultState = {
            activeView: this.DUPLICATE_VIEW,
            duplicateCount: 0,
            selectedId: null,
            selectedName: ''
        };

        //enable or disable duplicate check
        var moduleMetadata = app.metadata.getModule(this.meta.module);
        this.enableDuplicateCheck = (moduleMetadata && moduleMetadata.dupCheckEnabled) || false;

        this.currentState =  _.extend({},this.defaultState);
    },

    _render:function () {
        app.view.View.prototype._render.call(this);
        this.initiateSubComponents(this.meta);
        this.meta.moduleSingular = this.recordView.moduleSingular;
        this.updatePanelHeader();
    },

    /**
     * Resets the panels state to the default settings
     * @param activeView
     */
    resetPanelToDefaultState : function(activeView) {
        this.currentState =  _.extend({}, this.defaultState);
        activeView = activeView || this.defaultState.activeView;
        this.currentState.activeView = activeView;

        this.resetViewsState();
    },

    /**
     * Resets the status of the duplicate and record views.
     */
    resetViewsState : function() {
        var selection_model = this.duplicateView.context.get('selection_model');
        if (selection_model) {
            var id = selection_model.module + '_select_' + selection_model.id;
            this.$el.find('#'+ id).prop('checked', false);
            this.duplicateView.context.unset('selection_model', {silent:true});
        }
        this.currentState.selectedName = '';
        this.currentState.selectedId = null;

        this.duplicateView.validationStatus = this.STATUS_INIT;
        this.recordView.validationStatus = this.STATUS_INIT;
    },

    /**
     * Sets the listeners for changes to the dependent modules duplicate views panel.
     */
    initiateDependentModuleDuplicateViewTriggers: function () {
        _.each(this.meta.dependentModules, function(modules, moduleName, list) {
            this.context.on("dupecheck:" + moduleName + ":model:change", this.updateFromDependentModuleChanges);
            this.context.on("module:" + moduleName + ":reset", this.resetFromDependentModuleChanges);
        }, this);
    },

    /**
     * Add sub-views defined by the convert metadata to the view
     */
    initiateSubComponents:function (moduleMeta) {
        this.insertDuplicateViewInPanel(moduleMeta);
        this.insertRecordViewInPanel(moduleMeta);

        //if dupe check is turned on for module, check if dupes found
        if (moduleMeta.duplicateCheckOnStart && this.enableDuplicateCheck) {
            this.toggleSubViews(this.DUPLICATE_VIEW);
        } else {
            this.toggleSubViews(this.RECORD_VIEW);
        }
    },

    /**
     * Inserts the duplicate list view into module panel based on metadata
     */
    insertDuplicateViewInPanel: function(moduleMeta) {
        var context = this.context.getChildContext({
            'module': moduleMeta.module,
            'forceNew': true,
            'dupelisttype': 'dupecheck-list-select'
        });
        context.prepare();

        this.duplicateView = app.view.createLayout({
            context: context,
            name: 'dupecheck',
            layout: this.layout,
            module: context.module
        });

        this.addToLayoutComponents(this.duplicateView);

        this.$('.' + this.DUPLICATE_VIEW + 'View').append(this.duplicateView.el);
        this.duplicateView.context.on('change:selection_model', this.selectDuplicate);
        this.duplicateView.collection.on("reset", this.duplicateViewCallback, this);

        this.duplicateView.render();
        this.duplicateView.validationStatus = this.STATUS_INIT;
    },

    /**
     * Callback for when the duplicate list view collection has been updated.
     */
    duplicateViewCallback: function() {
        this.currentState.duplicateCount = this.duplicateView.collection.length;
        this.updatePanelHeader();
        if (this.duplicateView.collection.length === 0) {
            //no dupes, switch over to record view
            this.toggleSubViews(this.RECORD_VIEW);
        }
        else if (this.currentState.activeView != this.DUPLICATE_VIEW) {
            this.toggleSubViews(this.DUPLICATE_VIEW);
        }
    },

    /**
     * Insert the create/record view into the module panel based on metadata
     */
    insertRecordViewInPanel: function(moduleMeta) {
       var context = this.context.getChildContext({
           'module': moduleMeta.module,
           forceNew: true,
           create: true
       });
       context.prepare();

       this.recordView = app.view.createView({
            context: context,
            name: 'create',
            module: context.module,
            layout: this.layout
        });

        this.addToLayoutComponents(this.recordView);

        this.recordView.meta = this.removeFieldsFromMeta(this.recordView.meta, moduleMeta);
        this.$('.' +  this.RECORD_VIEW + 'View').append(this.recordView.el);
        this.recordView.enableHeaderButtons = false;
        this.recordView.render();

        this.recordView.validationStatus = this.STATUS_INIT;
    },

    /**
     * Removes fields from the meta and replaces with empty html container based on the modules config option - hiddenFields.
     * For example.  Account name dropdown should not be available on contact and opportunity module.
     * @param meta
     * @param moduleMeta
     * @return {*}
     */
    removeFieldsFromMeta: function(meta, moduleMeta){
        _.each(meta.panels, function(panel){
              _.each(panel.fields, function(field, index, list){
                  if (_.isString(field)) {
                       field = {name: field};
                  }
                  if (_.contains(moduleMeta.hiddenFields, field.name || field)) {
                        list[index] = {type:'html'};
                  }
                });
        }, this);
        return meta;
    },

    /**
     * Open up the panel, showing the bottom and ability to toggle the subview
     */
    handleShow: function() {
        this.$('.header').addClass('active');
        this.showBody();
        this.showSubViewToggle();
        //set status of the panel to dirty if the currently active view is the record view
        if ((this.currentState.activeView === this.RECORD_VIEW) && (this.getStatus() === this.STATUS_INIT)) {
            this.setStatus(this.STATUS_DIRTY);
        }
    },

    /**
     * Show the body of the panel
     */
    showBody: function () {
        var panelBody = '#collapse' + this.meta.module;
        this.$(panelBody).collapse('show');
    },

    /**
     * Close the panel, hide the body and ability to toggle the subview
     */
    handleHide: function() {
        this.$('.header').removeClass('active');
        this.updatePanelHeader();
        this.hideSubViewToggle();
    },

    /**
     * Enable the panel
     */
    handleEnablePanel: function(isEnabled) {
        this.$('.header').removeClass(isEnabled ? 'disabled' : 'enabled');
        this.$('.header').addClass(isEnabled ? 'enabled' : 'disabled');
    },

    /**
     * Toggle the subviews based on which link was clicked
     *
     * @param event
     */
    handleToggleClick: function(event) {
        this.resetViewsState();
        this.context.trigger("module:" + this.meta.module + ":reset");
        if (this.$(event.target).hasClass('show-duplicate')) {
            this.toggleSubViews(this.DUPLICATE_VIEW);
        } else if (this.$(event.target).hasClass('show-record')) {
            this.toggleSubViews(this.RECORD_VIEW);
            if (this.getStatus() === this.STATUS_INIT) {
                this.setStatus(this.STATUS_DIRTY);
            }
        }
        event.stopPropagation();
    },

    /**
     * Updates the attributes on the model based on the changes from dependent modules duplicate view.  Uses dependeModules property - fieldMappings
     * @param moduleName
     * @param model
     */
    updateFromDependentModuleChanges: function(moduleName, model) {
        var modelChanged = false;
        if (this.meta.dependentModules && this.meta.dependentModules[moduleName] && this.meta.dependentModules[moduleName].fieldMapping) {
            modelChanged = this.populateRecords(model, this.meta.dependentModules[moduleName].fieldMapping);
            if (modelChanged) {
                if (this.currentState.activeView === this.DUPLICATE_VIEW) {
                    this.resetPanelToDefaultState(this.DUPLICATE_VIEW);
                }
                this.triggerDuplicateCheck();
            }
        }
    },

    /**
     * Wrapper to check whether to fire the duplicate check event
     */
    triggerDuplicateCheck: function() {
        if (this.shouldDupeCheckBePerformed(this.recordView.model)) {
            this.duplicateView.context.trigger("dupecheck:fetch:fire", this.recordView.model, {
                //Show alerts for this request
                showAlerts: true
            });
        }
    },

    /**
     * Check if duplicate check should be performed - dependent on enableDuplicateCheck setting and required dupe check fields
     * @param model
     */
    shouldDupeCheckBePerformed: function(model) {
        var performDuplicateCheck = this.enableDuplicateCheck;

        if (this.meta.duplicateCheckRequiredFields) {
            _.each(this.meta.duplicateCheckRequiredFields, function (field) {
                if (_.isEmpty(model.get(field))) {
                    performDuplicateCheck = false;
                }
            });
        }
        return performDuplicateCheck;
    },

    /**
     * Resets the state of the panels based on a dependent module being reset
     */
    resetFromDependentModuleChanges: function() {
        this.resetPanelToDefaultState(this.DUPLICATE_VIEW);
        this.duplicateView.collection.reset();
    },

    /**
     * When a duplicate is selected, grab the id & name and set status to dirty
     *
     * @param event
     */
    selectDuplicate: function(event) {
       var selectedModel = event.changed.selection_model;

        this.currentState.selectedId = selectedModel.get('id');
        this.currentState.selectedName = selectedModel.get('name');
        this.setStatus(this.STATUS_COMPLETE);
        this.updatePanelHeader();

        this.context.trigger("dupecheck:" + this.meta.module+":model:change", this.meta.module, selectedModel);
    },

    /**
     * When there is a change to the state of a panel, updates need to be made to the header
     */
    updatePanelHeader: function() {
        this.updatePanelTitle();
        this.updatePanelSubTitle();
    },

    /**
     * Update the panel's title
     * Includes check mark for completion as well as text indicating whether the module
     * was associated or needs to be associated.
     */
    updatePanelTitle: function() {
        var newTitle;

        if (this.getStatus() === this.STATUS_COMPLETE) {
            this.$('.step-circle-right').addClass('complete');
            newTitle = app.lang.get(
                'LBL_CONVERT_MODULE_ASSOCIATED',
                this.module,
                {'moduleName': this.meta.moduleSingular}
            );
            if (!this.meta.required) {
                this.$('.optional').hide();
            }
        } else {
            this.$('.step-circle-right').removeClass('complete');
            newTitle = app.lang.get(
                'LBL_CONVERT_ASSOCIATE_MODULE',
                this.module,
                {'moduleName': this.meta.moduleSingular}
            );
            if (!this.meta.required) {
                this.$('.optional').show();
            }
        }
        this.$('.title').text(newTitle);
    },

    /**
     * Update the panel's subtitle
     * Includes either:
     *      the name of the associated record (if associated)
     *      number of duplicates found (if in dupe view)
     *      create new record heading (if in create view)
     */
    updatePanelSubTitle: function() {
        var newSubTitle, translatedString;

        if (this.getStatus() === this.STATUS_COMPLETE) {
            if (this.currentState.activeView === this.DUPLICATE_VIEW) {
                newSubTitle = this.currentState.selectedName;
            } else {
                newSubTitle = this.getDisplayName(this.recordView.model);
            }

        } else if (this.currentState.activeView === this.DUPLICATE_VIEW) {
            if (this.currentState.duplicateCount > 0) {
                translatedString = app.lang.get(
                    'LBL_CONVERT_DUPLICATES_FOUND',
                    this.module,
                    {'duplicateCount': this.currentState.duplicateCount}
                );
                newSubTitle = translatedString;
            }
        } else if (this.currentState.activeView === this.RECORD_VIEW) {
            translatedString = app.lang.get(
                'LBL_CONVERT_CREATE_NEW',
                this.module,
                {'moduleName': this.meta.moduleSingular}
            );
            newSubTitle = translatedString;
        } else {
            return;
        }

        this.$('.sub-title').text(newSubTitle);
    },

    /**
     * Special logic for grabbing the display name for a module
     * using the name fields if they exist or a 'name' field if it exists
     *
     * @param model
     * @return {String}
     */
    getDisplayName: function(model) {
        var moduleFields = app.metadata.getModule(this.meta.module).fields,
            displayName = '';

        if (moduleFields.name && moduleFields.name.fields) {
            _.each(moduleFields.name.fields, function(field) {
                if (model.has(field)) {
                    displayName += model.get(field) + ' ';
                }
            });
        } else if (moduleFields.name) {
            displayName = model.get('name');
        }
        return displayName;
    },

    hideSubViewToggle: function() {
        this.$('.subview-toggle').hide();
    },

    showSubViewToggle: function() {
        this.$('.subview-toggle').show();
    },

    /**
     * Populates the record view from the passed in model and then kick off the dupe check
     *
     * @param model
     */
    handlePopulateRecords: function(model) {
        this.populateRecords(model, this.meta.fieldMapping);
        if(this.meta.duplicateCheckOnStart) {
            this.triggerDuplicateCheck();
        }
    },

    /**
     * Use the convert metadata to determine how to map the lead fields to module fields
     *
     * @param model
     * @param fieldMapping
     * @return {Boolean} whether the recordview model has changed
     */
    populateRecords:function (model, fieldMapping) {
        var hasChanged = false;
        _.each(fieldMapping, function (sourceField, targetField) {
            if (model.has(sourceField) && model.get(sourceField) !== this.recordView.model.get(targetField)) {
                this.recordView.model.set(targetField, model.get(sourceField));
                hasChanged = true;
            }
        }, this);
        return hasChanged
    },

    /**
     * Helper method for switching subviews
     * (also updates header and lets the layout know a panel has been updated)
     *
     * @param viewToShow
     */
    toggleSubViews: function(viewToShow) {
        this.toggleDuplicateView(viewToShow === this.DUPLICATE_VIEW);
        this.toggleRecordView(viewToShow === this.RECORD_VIEW);
        this.updatePanelHeader();
        this.context.trigger("lead:convert:panel:update");
    },

    /**
     * Switch on/off the duplicate view and update the current state
     *
     * @param show true to show, false to hide
     */
    toggleDuplicateView: function(show) {
        this.duplicateView.$el.parent().toggle(show);
        this.$('.show-record').toggle(show);
        if (show) {
            this.currentState.activeView = this.DUPLICATE_VIEW;
        }
    },

    /**
     * Switch on/off the record/create view and update the current state
     *
     * @param show true to show, false to hide
     */
    toggleRecordView: function(show) {
        this.recordView.$el.parent().toggle(show);
        if (this.currentState.duplicateCount > 0) {
            this.$('.show-duplicate').toggle(show);
        }
        if (show) {
            this.currentState.activeView = this.RECORD_VIEW;
        }
    },

    /**
     * Run validation, report errors as appropriate
     * @param callback
     * @param force
     */
    runValidation: function(callback, force) {
        var force = force || false;
        if (this.currentState.activeView === this.DUPLICATE_VIEW) {
            //mark completed if a value is selected
            if (this.currentState.selectedId !== null) {
                this.setStatus(this.STATUS_COMPLETE);
                callback();
            } else if (!this.meta.required || !force) {
                callback();
            } else {
                this.showValidationAlert();
            }
        } else {
            var view = this.recordView,
                model = view.model;

            view.clearValidationErrors(view.editableFields);
            if (model.isValid(view.getFields(view.module))) {
                this.setStatus(this.STATUS_COMPLETE);
                callback();
            } else {
                this.showValidationAlert();
            }
        }
    },

    /**
     * Show validation errors on the record/create view
     */
    showValidationAlert: function() {
        var title = app.lang.get('LBL_CONVERT_FAILED_VALIDATION_TITLE', 'Leads');
        var message = app.lang.get('LBL_CONVERT_FAILED_VALIDATION_MESSAGE', 'Leads');
        app.alert.show('failed_validation', {level:'error', title: title, messages: message, autoClose: true});
    },

    /**
     * Retrieve the validation status from the currently displayed subview
     *
     * @return {*}
     */
    getStatus: function() {
        if (this.currentState.activeView === this.DUPLICATE_VIEW) {
            if (this.duplicateView && this.duplicateView.validationStatus) {
                return this.duplicateView.validationStatus;
            } else {
                return this.STATUS_INIT;
            }
        } else {
            if (this.recordView && this.recordView.validationStatus) {
                return this.recordView.validationStatus;
            } else {
                return this.STATUS_INIT;
            }
        }
    },

    /**
     * Update the validation status on the currently displayed subview
     * @param status
     */
    setStatus: function(status) {
        if (this.currentState.activeView === this.DUPLICATE_VIEW) {
            this.duplicateView.validationStatus = status;
        } else {
            this.recordView.validationStatus = status;
        }
        this.context.trigger("lead:convert:panel:update");
    },

    /**
     * Method used the by layout for determine if this panel has been complete
     * or (in the dupe view case) a record has been selected
     *
     * @return {Boolean}
     */
    isDirtyOrComplete: function() {
       return (this.getStatus() === this.STATUS_COMPLETE || this.getStatus() === this.STATUS_DIRTY);
    },

    /**
     * Retrieve the duplicate selected or record view model to be created
     *
     * @return {*} backbone model containing id or full record to create
     */
    getAssociatedModel: function() {
        var associatedModel;

        if (this.getStatus() !== this.STATUS_COMPLETE) {
            return null;
        }

        if (this.currentState.activeView === this.DUPLICATE_VIEW) {
            associatedModel = new Backbone.Model();
            associatedModel.set('id', this.currentState.selectedId);
            return associatedModel;
        } else {
            return this.recordView.model;
        }
    },

    /**
     * Add component to layout's component list so it gets cleaned up properly on dispose
     *
     * @param component
     */
    addToLayoutComponents: function(component) {
        this.layout._components.push(component);
    }
})
