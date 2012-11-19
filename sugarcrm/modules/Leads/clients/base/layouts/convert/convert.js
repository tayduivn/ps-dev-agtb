({
    events:{
        'click [name=convert_continue_button]':'processContinue', //TODO: remove this if we don't do the continue button
        'click .accordion-heading.enabled': 'handlePanelHeaderClick',
        'click [name=lead_convert_finish_button].enabled': 'processConvert'
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

        this.context.requiredComplete = false;
    },

    initializePanels: function(modulesMetadata) {
        var self = this,
            moduleNumber = 1;

        _.each(modulesMetadata, function (moduleMeta, index, list) {
            moduleMeta.moduleNumber = moduleNumber++;
            var view = app.view.createView({
                context: this.context,
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

        $(".collapse").collapse({toggle:false, parent:'#convert-accordion'});
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
        var isComplete,
            self = this;

        isComplete =  _.all(moduleMeta.dependentModules, function(moduleName) {
            var convertPanel,
                meta = self._getModuleMeta(moduleName);

            if (!meta.required) {
                return true;
            }

            convertPanel = self._getPanelByModuleName(moduleName);
            if (!convertPanel.isComplete()) {
                return false;
            }
            return true;
        });

        return isComplete;
    },

    checkRequired: function() {
        var showFinish,
            self = this;

        if (_.isBoolean(this.context.requiredComplete) && this.context.requiredComplete) {
            return;
        }

        showFinish = _.all(this.meta.modules, function(module){
            if (_.isBoolean(module.required) && module.required) {
                var convertPanel = self._getPanelByModuleName(module.module);
                if (!convertPanel.isComplete()) {
                    return false;
                }
            }
            return true;
        });

        if(showFinish) {
            this.context.requiredComplete = true;
            this.enableFinishButton();
        }
    },

    enableFinishButton: function() {
        $('[name=lead_convert_finish_button]').removeClass('disabled');
        $('[name=lead_convert_finish_button]').addClass('enabled');
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
            },

            addSubModel:function (name, model) {
                this.set(name, model);
            }
        });

        return new convertModel();
    },

    /**
     * Save the convert model and process the response
     * TODO: make sure this works
     */
    processConvert:function () {
        var self = this,
            convertModel;

        app.alert.show('save_edit_view', {level:'info', title:'Please Wait. Processing the conversion of the lead.'});

        //create parent convert model to hold all sub-models
        convertModel = this.createConvertModel(this.context.get('modelId'));

        //grab the associated model for each module
        _.each(this.meta.modules, function (moduleMeta) {
            var convertPanel = self._getPanelByModuleName(moduleMeta.module),
                associatedModel = convertPanel.getAssociatedModel();
            convertModel.addSubModel(moduleMeta.module, associatedModel);
        });

        convertModel.save(null, {
            success:function (data) {
                app.alert.dismiss('save_edit_view');
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
