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
     * Creates the parent model that holds all sub-models and logic for performing the convert action
     * @return {*} instance of a backbone model.
     */
    createConvertModel:function (id) {
        var convertModel = Backbone.Model.extend({
            sync:function (method, model, options) {
                var myURL = app.api.buildURL('Leads', 'convert', {id:id});
                return app.api.call(method, myURL, model, options);
            },
            // Fetch the model from the server. If the server's representation of the
            // model differs from its current attributes, they will be overriden,
            // triggering a `"change"` event.
            fetch:function (options) {
                options = options ? _.clone(options) : {};
                if (options.parse === void 0) options.parse = true;
                var model = this;
                var success = options.success;
                options.success = function (resp) {
                    if (!model.set(model.parse(resp, options), options)) return false;
                    if (success) success(model, resp, options);
                    model.trigger('sync', model, resp, options);
                };
                var error = options.error;
                options.error = function (resp) {
                    if (error) error(model, resp, options);
                    model.trigger('error', model, resp, options);
                };
                return this.sync('read', this, options);
            },
            // Set a hash of model attributes, and sync the model to the server.
            // If the server returns an attributes hash that differs, the model's
            // state will be `set` again.
            save:function (key, val, options) {
                var attrs, method, xhr, attributes = this.attributes;

                // Handle both `"key", value` and `{key: value}` -style arguments.
                if (key == null || typeof key === 'object') {
                    attrs = key;
                    options = val;
                } else {
                    (attrs = {})[key] = val;
                }

                // If we're not waiting and attributes exist, save acts as `set(attr).save(null, opts)`.
                if (attrs && (!options || !options.wait) && !this.set(attrs, options)) return false;

                options = _.extend({validate:true}, options);

                // Do not persist invalid models.
                if (!this._validate(attrs, options)) return false;

                // Set temporary attributes if `{wait: true}`.
                if (attrs && options.wait) {
                    this.attributes = _.extend({}, attributes, attrs);
                }

                // After a successful server-side save, the client is (optionally)
                // updated with the server-side state.
                if (options.parse === void 0) options.parse = true;
                var model = this;
                var success = options.success;
                options.success = function (resp) {
                    // Ensure attributes are restored during synchronous saves.
                    model.attributes = attributes;
                    var serverAttrs = model.parse(resp, options);
                    if (options.wait) serverAttrs = _.extend(attrs || {}, serverAttrs);
                    if (_.isObject(serverAttrs) && !model.set(serverAttrs, options)) {
                        return false;
                    }
                    if (success) success(model, resp, options);
                    model.trigger('sync', model, resp, options);
                };

                method = this.isNew() ? 'create' : (options.patch ? 'patch' : 'update');
                if (method === 'patch') options.attrs = attrs;
                xhr = this.sync(method, this, options);

                // Restore attributes.
                if (attrs && options.wait) this.attributes = attributes;

                return xhr;
            }
        });

        return new convertModel();
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
        var self = this,
            leadsModel,
            convertModel,
            models = {};

        app.alert.show('processing_convert', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_SAVING')});

        //create parent convert model to hold all sub-models
        leadsModel = this.context.get('leadsModel');
        convertModel = this.createConvertModel(leadsModel.id);

        //grab the associated model for each module
        _.each(this.meta.modules, function (moduleMeta) {
            var convertPanel = self._getPanelByModuleName(moduleMeta.module),
                associatedModel = convertPanel.getAssociatedModel();
            if (!_.isEmpty(associatedModel)) {
                models[moduleMeta.module] = associatedModel;
            }
        });

        convertModel.set('modules', models);
        convertModel.save(null, {
            success:function (data) {
                app.drawer.close();
                app.alert.dismiss('processing_convert');
                app.navigate(self.context, leadsModel, 'record');
            }
        });
    }
})
