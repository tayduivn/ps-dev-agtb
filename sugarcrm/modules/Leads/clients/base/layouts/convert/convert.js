({
    events:{
        'click [name=convert_continue_button]':'processContinue',
        'click [name=pick]': 'selectDuplicate'
    },

    _placeComponent: function(component, def) {
        this.$('#collapse' + def.moduleName).find('.' + def.contentType + 'View').append(component.el);
    },

    /**
     * Initialize convert layout
     * @param options
     */
    initialize:function (options) {
        var self = this,
            leadId, firstModule;

        _.bindAll(this);

        this.context = options.context;
        this.context.meta = options.meta
        app.view.Layout.prototype.initialize.call(this, options);

        //create parent convert model to hold all sub-models
        leadId = this.context.get('modelId');
        this.context.convertModel = this.createConvertModel(leadId);

        //listen for convert button click
        this.context.off("lead:convert", this.processConvert);
        this.context.on("lead:convert", this.processConvert, this);

        //set up the convert steps to control continue flow
        this.context.steps = this.buildConvertStepsList(this.meta.modules);

        this.$('.accordion').on('show', function (e) {
            self.initiateContinue(e);
        });
        this.initiateSubComponents(this.meta.modules);
    },

    render:function () {
        app.view.Layout.prototype.render.call(this);

        var self = this,
            firstModule = this.context.steps.getHead().key;;

        $('.accordion').on('show', function (e) {
            self.initiateContinue(e);
        });

        this.initiateAccordion(this.meta.modules);
        //  this.populateRecordModelsFromLeadsData();
        this.showAccordion(firstModule);
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

    /**
     * Add sub-views defined by the convert metadata to the layout
     */
    initiateSubComponents:function (modulesMetadata) {
        var self = this;

        _.each(modulesMetadata, function (moduleMeta, index, list) {
            var def = {
                    'view':'list-singleselect',
                    'context':{'module':moduleMeta.module}
                };

            self.insertDuplicateViewInAccordionBody(moduleMeta, def);

            def = {
                'view':'edit',
                'context':{'module':moduleMeta.module}
            };

            self.insertRecordViewInAccordionBody(moduleMeta, def);
            self.context.convertModel.addSubModel(moduleMeta.module, new Backbone.Model());
        });
    },

    insertDuplicateViewInAccordionBody: function(moduleMeta, def) {
        var view = this.insertViewInAccordionBody(moduleMeta, 'duplicate', def);

        if (moduleMeta.duplicateCheck) {
            view.collection.on("reset", function(){
                this.updateDuplicateMessage(view);
            }, this);
            view.$el.parent().removeClass('hide').addClass('show');
            view.loadData();
        }
    },

    insertRecordViewInAccordionBody: function(moduleMeta, def) {
        var view = this.insertViewInAccordionBody(moduleMeta, 'record', def);
    },

    insertViewInAccordionBody:function (moduleMeta, contentType, def) {
        var self = this,
            context = self.context.getChildContext(def.context);

        context.prepare();
        context.set('limit', 3);

        var view = app.view.createView({
            context:context,
            name:def.view,
            module:moduleMeta.module,
            layout:self,
            id:def.id
        });

        self.addComponent(view, {moduleName:moduleMeta.module, contentType:contentType});

        view.render();

        return view;
    },

    updateDuplicateMessage: function(view) {
        var $foundDuplicatePlaceholder = this.$('.accordion-group[data-module=' + view.module + ']').find('.found-duplicate');
        $foundDuplicatePlaceholder.text(view.collection.length + ' duplicates found'); //todo translate
    },

    processContinue:function () {
        var currentStep = this.context.currentStep;

        if (!_.isEmpty(currentStep.next)) {
            this.showAccordion(currentStep.next.key);
        }
    },

    setNextStepFall: function(nextModule) {
        var moduleMeta;
        this.context.currentStep = this.context.steps.search(nextModule);

        moduleMeta = this._getModuleMeta(nextModule);
    },

    showAccordion:function (moduleName) {
        var accordionBody = '#collapse' + moduleName;
        this.$(accordionBody).collapse('show');
        this.$(accordionBody).css('height', 'auto');
    },

    /**
     * Save the convert model and process the response
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

    /**
     * Displays the results in the detail page.
     */
    displayResults:function (data) {
        var modules = data.attributes.modules;
        var message = [];
        _.each(modules, function (module) {
            var link = (app.router.buildRoute(module.module_name, module.id, 'detail'));

            message.push(module.module_name + ": <a href='#" + link + "'>" + module.name + "</a>");
        });

        app.alert.show('convert-results', {level:"info", title:'Lead Converted', messages:message, autoclose:false});
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

    selectDuplicate: function(e) {
        var $selectedRadio = this.$(e.target),
            recordId = $selectedRadio.val(),
            module = $selectedRadio.attr('data-module'),
            subModel = this.context.convertModel.getSubModel(module);

        subModel.set('id', recordId);
    },

    /**
     * Iterate over the sub-models and copy Leads data to the sub-models
     */
    populateRecordModelsFromLeadsData:function () {
        var self = this,
            leadModel = self.model;

        //iterate over sub-models
        _.each(self.meta.modules, function (element, index, list) {
            var recordView = self.layout.getComponent(element.module);

            //field mappings: copy over data according to the metadata field mapping
            _.each(element.fieldMapping, function (sourceField, targetField) {
                if (leadModel.has(sourceField)) {
                    recordView.model.set(targetField, leadModel.get(sourceField));
                }
            });
        });
    },

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
    },

    _getModuleMeta: function(nextModule) {
        _.find(this.meta.module, function(moduleMeta){
            return moduleMeta.module === nextModule;
        })

        return {};
    }
})
