({
    events:{
        'click [name=convert_continue_button]':'processContinue', //TODO: remove this if we don't do the continue button
        'click .accordion-heading.enabled': 'handlePanelHeaderClick',
        'click [name=lead_convert_finish_button].enabled': 'initiateFinish'
    },

    initialize:function (options) {
        _.bindAll(this);
        app.view.Layout.prototype.initialize.call(this, options);

        //TODO: remove this if we don't do the continue button
        //set up the convert steps to control continue flow
        this.context.steps = this.buildConvertStepsList(this.meta.modules);

        //create and place all the accordion panels
        this.initializePanels(this.meta.modules);

        //listen for panel status updates
        this.context.on("lead:convert:panel:update", this.handlePanelUpdate, this);
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
            view.$el.addClass('accordion-group');
            view.$el.data('module', moduleMeta.module);

            self.addComponent(view); //places panel in the layout
        });
    },

    _placeComponent: function(component) {
        this.$('#convert-accordion').append(component.el);
    },

    render: function () {
        var firstModule;
        app.view.Layout.prototype.render.call(this);

        this.$(".collapse").collapse({toggle:false, parent:'#convert-accordion'});
        this.context.trigger("lead:convert:populate", this.model);

        //show first panel
        firstModule = _.first(this.meta.modules).module;
        this.context.currentStep = this.context.steps.search(firstModule);
        this.context.trigger('lead:convert:' + firstModule + ':show');
        this.checkRequired();
    },

    handlePanelHeaderClick: function(event) {
        var panelHeader = this.$(event.target).closest('.accordion-heading'),
            nextModule = panelHeader.attr('data-module');

        this.initiateShow(nextModule);
    },

    initiateShow: function(nextModule) {
        var self = this,
            currentModule = this.context.currentStep.key,
            callback = function() {
                self.context.trigger('lead:convert:' + currentModule + ':hide');
                self.context.trigger('lead:convert:' + nextModule + ':show');
                self.setNextStep(nextModule);
            };

        if (nextModule != this.context.currentStep.key) {
            self.context.trigger('lead:convert:' + currentModule + ':validate', callback);
        }
    },

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

    setNextStep: function(nextModule) {
        this.context.currentStep = this.context.steps.search(nextModule);
    },

    _getModuleMeta: function(nextModule) {
       return _.find(this.meta.modules, function(moduleMeta){
            return moduleMeta.module === nextModule;
        })
    },

    //todo: fix to not access this._components directly
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
        var currentModule = this.context.currentStep.key
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
        convertModel = this.createConvertModel(this.context.get('modelId'));

        //grab the associated model for each module
        _.each(this.meta.modules, function (moduleMeta) {
            var convertPanel = self._getPanelByModuleName(moduleMeta.module),
                associatedModel = convertPanel.getAssociatedModel();
            models[moduleMeta.module] = associatedModel;
        });
        convertModel.set('modules', models);

        convertModel.save(null, {
            success:function (data) {
                app.alert.dismiss('processing_convert');
                app.navigate(self.context, self.model, 'record');
                //todo: display success message?
            }
        });
    },

    //TODO: remove this if we don't do the continue button
    processContinue:function () {
        var currentStep = this.context.currentStep;

        if (!_.isEmpty(currentStep.next)) {
            this.initiateShow(currentStep.next.key);
        }
    },

    //TODO: remove this if we don't do the continue button
    buildConvertStepsList: function(moduleMetadata) {
        var linkedListNode = function (key) {
            return {
                key: key,
                next: null
            };
        };
        var linkedList = function () {
            var head = null;
            var next = null;
            var insert = function (node) {
                if (head == null) {
                    head = node;
                }
                if (next != null) {
                    next.next = node;
                }
                next = node;
            };

            var search = function (key) {
                var node = head;
                while (node !== null && node.key !== key) {
                    node = node.next;
                };
                return node;
            };

            var getHead = function () {
                return head;
            };

            return {
                insert:insert,
                search:search,
                getHead:getHead
            };
        };

        var convertSteps = new linkedList();
        _.each(moduleMetadata, function (element, index, list) {
            convertSteps.insert(new linkedListNode(element.module));
        });

        return convertSteps;
    }
})
