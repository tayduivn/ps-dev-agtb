({
    events:{
        'click [name=convert_continue_button]':'processContinue'
    },

    initialize:function (options) {
        var self = this;

        _.bindAll(this);
        app.view.Layout.prototype.initialize.call(this, options);

        //create parent convert model to hold all sub-models
        this.context.convertModel = this.createConvertModel(this.context.get('modelId'));

        //set up the convert steps to control continue flow
        this.context.steps = this.buildConvertStepsList(this.meta.modules);

        //create and place all the accordion panels
        this.initializePanels(this.meta.modules);

        //listen for convert button click
        this.context.on("lead:convert", this.processConvert, this);
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
        var self = this;
        app.view.Layout.prototype.render.call(this);
        //run validation before showing next panel
        this.$('.accordion').on('show', function (e) {
            self.initiateContinue(e);
        });

        $(".collapse").collapse({toggle:false, parent:'#convert-accordion'});
        this.context.trigger("lead:convert:populate", this.model);
        //this.initiateAccordion(this.meta.modules);
        this.showPanel(_.first(this.meta.modules).module);
    },

    /**
     * Collapse all accordion panels initially
     */
    initiateAccordion:function (moduleMetadata) {
        var self = this;

        _.each(moduleMetadata, function (element, index, list) {
            var accordionBody = '#collapse' + element.module;
            self.$(accordionBody).collapse({
                parent:'#convert-accordion'
            });
        });
    },

    showPanel: function (moduleName) {
        var panelBody = '#collapse' + moduleName;
        this.$(panelBody).collapse('show');
    },

    /**
     * Check for possible duplicates before creating a new record
     * @param callback
     */
    initiateContinue:function (evt) {
        var self = this,
            nextModule = evt.target.dataset.module;

        async.waterfall([
            //validation or has selected element
            //Add logic to process current step and if complete move to next one
            //check whether or not can continue depending on dependent modules
            _.bind(this.setNextStepFall, this, nextModule)
        ], function(error) {
            if (error) {
                console.log("Saving failed.");
                //TODO: handle error
            } else {
                callback();
            }
        });

        var result = true;

        return result;
    },

    processContinue:function () {
        var currentStep = this.context.currentStep;

        if (!_.isEmpty(currentStep.next)) {
            this.showPanel(currentStep.next.key);
        }
    },

    setNextStepFall: function(nextModule) {
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
