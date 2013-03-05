({
    events:{
        'click .header.enabled': 'handlePanelHeaderClick',
        'click [name=lead_convert_finish_button].enabled': 'initiateFinish'
    },

    initialize:function (options) {
        _.bindAll(this);
        app.view.Layout.prototype.initialize.call(this, options);

        //create and place all the accordion panels
        this.initializePanels(this.meta.modules);

        //listen for panel status updates
        this.context.on("lead:convert:panel:update", this.handlePanelUpdate);
        this.context.on("lead:convert:finish", this.initiateFinish);
    },

    /**
     * Iterate over the modules defined in convert-main.php
     * Create a convert panel for each module defined there
     *
     * @param modulesMetadata
     */
    initializePanels: function(modulesMetadata) {
        var moduleNumber = 1;

        _.each(modulesMetadata, function (moduleMeta) {
            moduleMeta.moduleNumber = moduleNumber++;
            var view = app.view.createView({
                context: this.context,
                name: 'convert-panel',
                layout: this,
                meta: moduleMeta
            });

            //This is because backbone injects a wrapper element.
            view.$el.addClass('accordion-group filter_el step_1_container');
            view.$el.data('module', moduleMeta.module);

            this.addComponent(view);
        }, this);
    },

    _render: function () {
        var firstModule;
        app.view.Layout.prototype._render.call(this);

        //This is because backbone injects a wrapper element.
        this.$el.addClass('accordion');
        this.$el.attr('id','convert-accordion');

        //apply the accordion to this layout
        this.$(".collapse").collapse({toggle:false, parent:'#convert-accordion'});

        //copy lead data down to each module when we get the lead data
        this.context.trigger("lead:convert:populate", this.context.get('leadsModel'));

        //show first panel
        firstModule = _.first(this.meta.modules).module;
        this.setStep(firstModule);
        this.context.trigger('lead:convert:' + firstModule + ':show');

        this.checkRequired();
    },

    /**
     * When user clicks on the panel headers, kick off validation
     * for the currently open panel
     *
     * @param event
     */
    handlePanelHeaderClick: function(event) {
        var panelHeader = this.$(event.target).closest('.header'),
            nextModule = panelHeader.attr('data-module');

        this.initiateShow(nextModule);
    },

    /**
     * Kick off validation for the currently open panel
     * This will prevent the next panel from opening and display errors if validation fails
     *
     * @param nextModule
     */
    initiateShow: function(nextModule) {
        var self = this,
            currentModule = this.getStep(),
            callback = function() {
                self.context.trigger('lead:convert:' + currentModule + ':hide');
                self.context.trigger('lead:convert:' + nextModule + ':show');
                self.setStep(nextModule);
            };

        if (nextModule != this.getStep()) {
            this.context.trigger('lead:convert:' + currentModule + ':validate', callback);
        }
    },

    /**
     * Checks if each module's dependencies are met and enables the panel if they are.
     * Dependencies are defined in the convert-main.php
     */
    checkDependentModules: function() {
        var modulesMeta = this.meta.modules,
            isEnabled = false;

        _.each(modulesMeta, function (moduleMeta) {
            if(!_.isUndefined(moduleMeta.dependentModules)) {
                if (this.isDependentModulesComplete(moduleMeta)) {
                    isEnabled = true;
                }
                this.context.trigger("lead:convert:" + moduleMeta.module + ":enable", isEnabled);
            }
        }, this);
    },

    /**
     * Checks if a given module's dependencies are met
     *
     * @param moduleMeta
     * @return boolean
     */
    isDependentModulesComplete: function(moduleMeta) {
        var isDirtyOrComplete,
            self = this;

        isDirtyOrComplete =  _.all(moduleMeta.dependentModules, function(module, moduleName, list) {
            var convertPanel,
                meta = self._getModuleMeta(moduleName);

            if (!meta.required) {
                return true;
            }

            convertPanel = self._getPanelByModuleName(moduleName);
            if (!convertPanel.isDirtyOrComplete()) {
                return false;
            }
            return true;
        });

        return isDirtyOrComplete;
    },

    /**
     * Checks if all required modules have been completed
     * Enables the finish button if all are complete
     */
    checkRequired: function() {
        var showFinish,
            self = this;

        showFinish = _.all(this.meta.modules, function(module){
            if (_.isBoolean(module.required) && module.required) {
                var convertPanel = self._getPanelByModuleName(module.module);
                if (!convertPanel.isDirtyOrComplete()) {
                    return false;
                }
            }
            return true;
        });

        if (showFinish) {
            this.toggleFinishButton(true);
        } else {
            this.toggleFinishButton(false);
        }
    },

    /**
     * Enable/disable the Finish button on the page
     *
     * @param enable true to enable, false to disable
     */
    toggleFinishButton: function(enable) {
        $('[name=lead_convert_finish_button]').toggleClass('enabled', enable);
        $('[name=lead_convert_finish_button]').toggleClass('disabled', !enable);
    },

    /**
     * When a panel has been updated, check if any module's dependencies are met
     * and/or if all required modules have been completed
     */
    handlePanelUpdate: function() {
        this.checkDependentModules();
        this.checkRequired();
    },

    setStep: function(nextModule) {
        this.context.currentStep = nextModule;
    },

    getStep: function() {
        return this.context.currentStep;
    },

    /**
     * Helper for getting a module's convert lead metadata
     * @param nextModule
     * @private
     */
    _getModuleMeta: function(nextModule) {
       return _.find(this.meta.modules, function(moduleMeta){
            return moduleMeta.module === nextModule;
        })
    },

    /**
     * Helper for getting a sub-panel component from the layout's component array by module name
     * @param moduleName
     * @return {*} convert-panel view for given module name
     * @private
     */
    _getPanelByModuleName: function(moduleName) {
        return _.find(this._components, function(component) {
            return ((component.name === 'convert-panel') && (component.meta.module === moduleName));
        })
    },

    /**
     * When finish button is clicked, need to kick off validation for current module
     */
    initiateFinish: function() {
        var currentModule = this.getStep();
        //run validation - set force=true to make sure required panels are completed
        this.context.trigger('lead:convert:' + currentModule + ':validate', this.processConvert, true);
    },

    /**
     * Save the convert model and process the response
     */
    processConvert:function () {
        var leadsModel,
            convertModel,
            models = {},
            myURL;

        this.toggleFinishButton(false);
        app.alert.show('processing_convert', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_SAVING')});

        //create parent convert model to hold all sub-models
        leadsModel = this.context.get('leadsModel');

        //grab the associated model for each module
        _.each(this.meta.modules, function (moduleMeta) {
            var convertPanel = this._getPanelByModuleName(moduleMeta.module),
                associatedModel = convertPanel.getAssociatedModel();
            if (!_.isEmpty(associatedModel)) {
                models[moduleMeta.module] = associatedModel;
            }
        }, this);

        convertModel = new Backbone.Model(_.extend({}, {'modules' : models}));

        myURL = app.api.buildURL('Leads', 'convert', {id:leadsModel.id});

        app.api.call('create', myURL, convertModel, {
            success: this.uploadAssociatedRecordFiles,
            error: this.convertError
        });
    },

    /**
     * After successfully converting a lead, loop through all modules and attempt to upload file input fields
     * All modules are done asynchronously and the last one to complete calls the appropriate completion callback
     *
     * @param convertResults
     */
    uploadAssociatedRecordFiles: function(convertResults) {
        var modulesToProcess = this.meta.modules.length,
            failureCount = 0;

        var completeFn = _.bind(function() {
            modulesToProcess--;
            if (modulesToProcess === 0) {
                if (failureCount > 0) {
                    this.convertWarning();
                } else {
                    this.convertSuccess();
                }
            }
        }, this);

        _.each(this.meta.modules, function(moduleMeta) {
            var convertPanel = this._getPanelByModuleName(moduleMeta.module),
                associatedModel = convertPanel.getAssociatedModel(),
                moduleResult;

            moduleResult = _.find(convertResults.modules, function(module) {
                return (moduleMeta.module === module._module);
            }, this);

            //if associatedModel has no id, then it came from recordView on convertPanel and may need file uploads
            if (moduleResult && _.isEmpty(associatedModel.get('id'))) {
                associatedModel.set('id', moduleResult.id);
                app.file.checkFileFieldsAndProcessUpload(
                    convertPanel.recordView,
                    {
                        success: function() { completeFn(); },
                        error: function() { failureCount++; completeFn(); }
                    },
                    {deleteIfFails:false},
                    false
                );
            //no files to upload because an existing record was selected for this module, just run complete
            } else {
                completeFn();
            }

        }, this);
    },

    /**
     * Lead was successfully converted
     */
    convertSuccess: function() {
        this.convertComplete('success', 'LBL_CONVERTLEAD_SUCCESS', true);
    },

    /**
     * Lead was converted, but some files failed to upload
     */
    convertWarning: function() {
        this.convertComplete('warning', 'LBL_CONVERTLEAD_FILE_WARN', true);
    },

    /**
     * There was a problem converting the lead
     */
    convertError: function() {
        this.convertComplete('error', 'LBL_CONVERTLEAD_ERROR', false);

        if (!this.disposed) {
            this.toggleFinishButton(true);
        }
    },

    /**
     * Based on success of lead conversion, display the appropriate messages and optionally close the drawer
     * @param level
     * @param message
     * @param doClose
     */
    convertComplete: function(level, message, doClose) {
        var leadsModel = this.context.get('leadsModel');
        app.alert.dismiss('processing_convert');
        app.alert.show('convert_complete', {
            level: level,
            messages: app.lang.get(message, this.module, {leadName:leadsModel.get('first_name')+' '+leadsModel.get('last_name')}),
            autoClose: (level === 'success')
        });
        if (!this.disposed && doClose) {
            app.drawer.close();
            app.navigate(this.context, leadsModel, 'record');
        }
    }
})
