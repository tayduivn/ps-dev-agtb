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
        this.context.on("lead:convert:panel:update", this.handlePanelUpdate, this);
        this.context.on("lead:convert:finish", this.initiateFinish, this);
    },

    initializePanels: function(modulesMetadata) {
        var self = this,
            moduleNumber = 1;

        _.each(modulesMetadata, function (moduleMeta, index, list) {
            moduleMeta.moduleNumber = moduleNumber++;
            var view = app.view.createView({
                context: self.context,
                name: 'convert-panel',
                layout: self,
                meta: moduleMeta
            });

            //This is because backbone injects a wrapper element.
            view.$el.addClass('accordion-group filter_el step_1_container');
            view.$el.data('module', moduleMeta.module);

            self.addComponent(view);
        });
    },

    _placeComponent: function(component) {
        this.$el.append(component.el);
    },

    render: function () {
        var firstModule;
        app.view.Layout.prototype.render.call(this);

        //This is because backbone injects a wrapper element.
        this.$el.addClass('accordion');
        this.$el.attr('id','convert-accordion');

        this.$(".collapse").collapse({toggle:false, parent:'#convert-accordion'});
        this.context.trigger("lead:convert:populate", this.context.get('leadsModel'));

        //show first panel
        firstModule = _.first(this.meta.modules).module;
        this.setStep(firstModule);
        this.context.trigger('lead:convert:' + firstModule + ':show');
        this.checkRequired();
    },

    handlePanelHeaderClick: function(event) {
        var panelHeader = this.$(event.target).closest('.header'),
            nextModule = panelHeader.attr('data-module');

        this.initiateShow(nextModule);
    },

    initiateShow: function(nextModule) {
        var self = this,
            currentModule = this.getStep(),
            callback = function() {
                self.context.trigger('lead:convert:' + currentModule + ':hide');
                self.context.trigger('lead:convert:' + nextModule + ':show');
                self.setStep(nextModule);
            };

        if (nextModule != this.getStep()) {
            self.context.trigger('lead:convert:' + currentModule + ':validate', callback);
        }
    },

    /*
    * This method checks whether a module is active depending on other modules being completed.
    */
    checkDependentModules: function() {
        var self = this,
            modulesMeta = this.meta.modules;

        _.each(modulesMeta, function (moduleMeta, index, list) {
            if(!_.isUndefined(moduleMeta.dependentModules)) {
                if (self.isDependentModulesComplete(moduleMeta)) {
                    self.context.trigger("lead:convert:" + moduleMeta.module + ":enable");
                }
            }
        });
    },

    isDependentModulesComplete: function(moduleMeta) {
        var isDirtyOrComplete,
            self = this;

        isDirtyOrComplete =  _.all(moduleMeta.dependentModules, function(moduleName) {
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

    toggleFinishButton: function(enable) {
        $('[name=lead_convert_finish_button]').toggleClass('enabled', enable);
        $('[name=lead_convert_finish_button]').toggleClass('disabled', !enable);
    },

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

    _getModuleMeta: function(nextModule) {
       return _.find(this.meta.modules, function(moduleMeta){
            return moduleMeta.module === nextModule;
        })
    },

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
                myURL = app.api.buildURL('Leads', 'convert', {id:id});
                return app.api.call(method, myURL, model, options);
            }
        });

        return new convertModel();
    },

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
            convertModel,
            models = {};

        app.alert.show('processing_convert', {level: 'process', title: app.lang.getAppString('LBL_PORTAL_SAVING')});

        //create parent convert model to hold all sub-models
        var leadsModel = this.context.get('leadsModel');
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
                app.alert.dismiss('processing_convert');
                app.navigate(self.context, leadsModel, 'record');
            }
        });
    }
})
