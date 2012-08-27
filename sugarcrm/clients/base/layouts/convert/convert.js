({
        /**
         * Holds the metadata for each of the components used in forecasts
         */
        componentsMeta: {},

        /**
         * Stores the initial data models coming from view.sidecar.php
         * todo: use this to populate models that we already have data for; currently only holds filters, chartoptions, & user
         *
         */
        initDataModel: {},

        initialize: function(options) {
            var leadId;

            debugger;
            //load metadata for convert
            //traverse and add to metadata prior to initialize.
           // options.context = _.extend(options.context, this.initializeAllModels());
            this.app.view.Layout.prototype.initialize.call(this, options);


            leadId = this.context.attributes.modelId;
            this.convertModel = this.createConvertModel(leadId);

            this.componentsMeta = this.app.metadata.getLayout("Leads").convert.meta.components;
/*
            <?php
            $viewdefs['Leads']['base']['layout']['convert'] = array(
    'type' => 'convert',
    'components' => array(
        0 => array(
            'view' => 'convertheader',
        ),
        1 => array(
            'layout' => 'accordion',
        ),
        2 => array(
            'view' => 'convertbottom',
        ),

    )
);

           */
            debugger;
            var createHeaderDef = {'view' : 'convertheader'};
            this.addComponent(app.view.createView({
                context: this.context,
                name: createHeaderDef.view,
                module: this.context.get("module"),
                layout: this,
                id: this.model.id
            }), createHeaderDef);


           debugger;
            //    var component = this.getComponent('"convertheader"');
            var def = {
                'view' : 'accordion-panel',
                'context' : {'module' : 'Prospects'}
            };
            // Switch context if necessary
            if (def.context) {
                context = this.context.getChildContext(def.context);
                context.prepare();
                module = context.get("module");
            }

            if (def.view) {
                view = app.view.createView({
                    context: context,
                    name: 'accordion-panel',
                    module: module,
                    layout: this,
                    id: def.id
                });
                this.addComponent(view, def);
            }

            var def = {
                'view' : 'accordion-panel',
                'context' : {'module' : 'ProspectLists'}
            };
            // Switch context if necessary
            if (def.context) {
                context = this.context.getChildContext(def.context);
                context.prepare();
                module = context.get("module");
            }

            if (def.view) {
                view = app.view.createView({
                    context: context,
                    name: 'accordion-panel',
                    module: module,
                    layout: this,
                    id: def.id
                });
                this.addComponent(view, def);
            }



            var createfooterDef = {'view' : 'convertbottom'};
            this.addComponent(app.view.createView({
                context: this.context,
                name: createfooterDef.view,
                module: this.context.get("module"),
                layout: this,
                id: createfooterDef.id
            }), createfooterDef);




            this.context.off("lead:convert", null, this);
            this.context.on("lead:convert", this.convert, this);





        },


         convert: function () {
            var self = this;
            debugger;
            _.each( this.context.getModels(), function(model) {
                self.convertModel.addSubModel(model.cid, model);
            }, this);
            self.convertModel.save();
        },
    /**
     * creates a convert model that will hold all module models for syncing
     * @return {*} instance of a backbone model.
     */
    createConvertModel:function (id) {
        debugger;
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
         * Fetches data for layout's model or collection.
         *
         * The default implementation first calls the {@link Core.Context#loadData} method for the layout's context
         * and then iterates through the components and calls their {@link View.Component#loadData} method.
         * This method sets context's `fields` property beforehand.
         *
         * Override this method to provide custom fetch algorithm.
         */
       /* loadData: function() {
            //debugger;
            this.fetchAllModels();
        },*/

        /**
         * Iterates through all the loaded models & collections as defined in metadata and does a "fetch" on it
         */
        fetchAllModels: function() {
            var self = this;
            _.each(this.componentsMeta, function(component) {

                if(component.model && component.model.name){
                    self.context.convert[component.model.name.toLowerCase()].fetch();
                }


                if(component.contextCollection && component.contextCollection.name) {
                    self.context.convert[component.contextCollection.name.toLowerCase()].fetch();
                }
                if(component.collection && component.collection.name) {
                    self.context.convert[component.collection.name.toLowerCase()].fetch();
                }

            });
        },

        /**
         * Iterates through metadata to define and initialize each model and collection as defined therein.
         * @return {Object} new instance of the main model, which contains instances of the sub-models for each view
         * as defined in metadata.
         */
        initializeAllModels: function () {
            var self = this,
                componentsMetadata = this.componentsMeta,
                models = {};
            _.each(componentsMetadata, function(component) {
                var name,
                    modelMetadata = component.model,
                    context = component.contextCollection,
                    collectionMetadata = component.collection;

                var module = self.options.module.toLowerCase();

                if (!models[module]) {
                    var topModel = app.data.createBean(module);
                    models[module] = topModel;
                }

                if (modelMetadata) {
                    name = modelMetadata.name.toLowerCase();
                    self.namespace(models, module);
                    models[module][name] = self.createModel(modelMetadata, app.viewModule);
                }

                if(context) {
                    var name = context.name.toLowerCase();
                    var moduleContext = context.module;
                    self.namespace(models, module);

                    models[module][name] = self.createCollection();
                }

                if (collectionMetadata) {
                    name = collectionMetadata.name.toLowerCase();
                    self.namespace(models, module);
                    models[module][name] = self.createCollection();
                    models[module][name].url = app.config.serverUrl + '/' + app.viewModule + '/' + name;
                }
            });

            return models;
        }

         /**
         * Add a view (or layout) to this layout.
         * @param {View.Layout/View.View} comp Component to add
         */
      /*  _placeComponent: function(comp) {
            if (!this.$el.children()[0]) {
                this.$el.addClass("complex-layout");
            }

            //add the layout to the div
            $(".view-"+comp.name).append(comp.$el);
        }*/
    })