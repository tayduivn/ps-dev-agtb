({
    events:{
        'click [name=convert_continue_button]':'processContinue', //TODO: remove this if we don't do the continue button
        'click .accordion-heading': 'handlePanelHeaderClick'
    },

    initialize:function (options) {
        _.bindAll(this);
        app.view.Layout.prototype.initialize.call(this, options);

        //create parent convert model to hold all sub-models
        this.context.convertModel = this.createConvertModel(this.context.get('modelId'));

        //TODO: remove this if we don't do the continue button
        //set up the convert steps to control continue flow
        this.context.steps = this.buildConvertStepsList(this.meta.modules);

        //create and place all the accordion panels
        this.initializePanels(this.meta.modules);

        //listen for convert button click
        this.context.on("lead:convert", this.processConvert, this);
        this.context.requiredComplete = false;
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
            },

            getSubModel:function (name) {
                return this.get(name);
            }
        });

        return new convertModel();
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
        this.enableFinishButton();
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
            };

        //todo: check if create view is visible and dirty - don't run if not dirty
        if (nextModule != this.context.currentStep.key) {
            this.runValidation(nextModule, callback);
        }
    },

    /**
     * Run validation before calling back to show the panel body
     */
    runValidation:function (nextModule, callback) {
        var self = this;

        async.waterfall([
            //validation or has selected element
            //Add logic to process current step and if complete move to next one
            //check whether or not can continue depending on dependent modules
            _.bind(this.enableFinishButtonFall, this)
        ], function(error) {
            if (error) {
                console.log("Saving failed.");
                //TODO: handle error
            } else {
                self.setNextStep(nextModule);
                callback();
            }
        });
    },

    enableFinishButtonFall: function(callback) {
        this.enableFinishButton();
        callback(false);
    },

    enableFinishButton: function() {
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
            $('[name=save_button]').removeClass('disabled');
        }
    },

    setNextStep: function(nextModule) {
        var moduleMeta;
        this.context.currentStep = this.context.steps.search(nextModule);
        moduleMeta = this._getModuleMeta(nextModule);
    },

    _getModuleMeta: function(nextModule) {
        _.find(this.meta.module, function(moduleMeta){
            return moduleMeta.module === nextModule;
        })

        return {};
    },

    //todo: fix to not access this._components directly
    _getPanelByModuleName: function(moduleName) {
        return _.find(this._components, function(component) {
            return ((component.name === 'convert-panel') && (component.meta.module === moduleName));
        })
    },

    /**
     * Save the convert model and process the response
     * TODO: make sure this works
     */
    processConvert:function () {
        var self = this;

        app.alert.show('save_edit_view', {level:'info', title:'Please Wait. Processing the conversion of the lead.'});
        this.context.convertModel.save(null, {
            success:function (data) {
                app.alert.dismiss('save_edit_view');
                app.navigate(self.context, self.model, 'detail');
                self.displayResults(data);
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
