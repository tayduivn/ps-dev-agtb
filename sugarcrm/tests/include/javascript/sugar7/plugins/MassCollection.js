describe('MassCollection plugin:', function() {

    var app, layout, view, data, massCollection, createMassCollectionStub, _preselectModelsStub, collection;
    var moduleName = 'Accounts',
        viewName = 'multi-selection-list',
        layoutName = 'multi-selection-list';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', viewName);
        var context = app.context.getContext();
        context.set({
            module: moduleName,
            layout: layoutName,
            preselectedModelIds: ['1', '2']
        });
        context.prepare();
        layout = app.view.createLayout({
            name: layoutName,
            context: context
        });
        view = SugarTest.createView('base', moduleName, viewName, null, context, null, layout);
    });

    afterEach(function() {
        view.dispose();
        layout.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        layout = null;
        view = null;
        data = null;
    });

    describe('Initialize:', function() {
        beforeEach(function() {
            createMassCollectionStub = sinon.collection.stub(view, 'createMassCollection');
            _preselectModelsStub = sinon.collection.stub(view, '_preselectModels');
        });
        it('should create the mass collection', function() {
            view.trigger('init');
            expect(createMassCollectionStub).toHaveBeenCalled();
        });
        it('should handle preselected models', function() {
            view.trigger('init');
            massCollection = view.context.get('mass_collection');
            expect(_preselectModelsStub).toHaveBeenCalled();
        });
    });

    describe('CreateMassCollection:', function() {
        it('should set the mass collection in the context', function() {
            view.context.attributes.mass_collection = null;
            view.createMassCollection();
            expect(view.context.get('mass_collection')).toBeDefined();
        });
    });

    describe('addModel:', function() {
        beforeEach(function() {
            massCollection = view.context.get('mass_collection');
            massCollection.add([{id: 1}, {id: 2}]);
        });
        it('should add the model to the mass collection', function() {
            view.addModel({id: 3});
            var addedModel = _.find(massCollection.models, function(model) {
                return model.id == 3;
            });

            expect(addedModel).toBeDefined();
        });

        it('should trigger an event on the massCollection', function() {
            sinon.collection.stub(view, '_isAllChecked', function() {return true});
            var triggerStub = sinon.collection.stub(massCollection, 'trigger');
            view.addModel({id: 1});

            expect(triggerStub).toHaveBeenCalled();

        });
    });

    describe('addAllModels:', function() {
        beforeEach(function() {
            massCollection = view.context.get('mass_collection');
            massCollection.add([{id: 1}, {id: 2}, {id: 3}]);
            view.collection.add([{id: 4}, {id: 5}]);
        });
        it('should add all models of the current collection to the massCollection', function() {
            view.addAllModels();
            expect(_.intersection(view.collection.models, massCollection) == view.collection.models);
        });

        it('should reset the massCollection to match the view collection', function() {
            // Boolean set to `false` indicating the mass collection is tied to the collection.
            view.independentMassCollection = false;
            view.addAllModels();
            expect(_.isEqual(massCollection.models, view.collection.models)).toBe(true);
        });
    });

    describe('removeModel:', function() {
        beforeEach(function() {
            massCollection = view.context.get('mass_collection');
            massCollection.add([{id: 1}, {id: 2}, {id: 3}]);
        });
        it('should remove the model from the mass collection', function() {
            view.removeModel({id: 1});
            var model1 = _.find(massCollection.models, function(model) {
                return model.id == 1;
            });
            expect(model1).toBeUndefined();
        });

        it('should trigger an event on the massCollection', function() {
            sinon.collection.stub(view, '_isAllChecked', function() {return false});
            var triggerStub = sinon.collection.stub(massCollection, 'trigger');
            view.removeModel({id: 1});

            expect(triggerStub).toHaveBeenCalled();
        });
    });

    describe('removeAllModels:', function() {
        beforeEach(function() {
            massCollection = view.context.get('mass_collection');
            massCollection.add([{id: 1}, {id: 2}, {id: 3}]);
            view.collection.add([{id: 2}, {id: 3}]);
        });
        it('should remove all the records in the view collection from the mass collection', function() {
            view.removeAllModels();
            var removedModel1 = _.find(massCollection.models, function(model) {
                return model.id == 2;
            });
            var removedModel2 = _.find(massCollection.models, function(model) {
                return model.id == 3;
            });
            expect(removedModel1).toBeUndefined();
            expect(removedModel2).toBeUndefined();
        });
        it('should clear the mass collection', function() {
            var clearMassCollectionStub = sinon.collection.stub(view, 'clearMassCollection');
            view.independentMassCollection = false;
            view.removeAllModels();

            expect(clearMassCollectionStub).toHaveBeenCalled();

        });
    });

    describe('clearMassCollection:', function() {
        beforeEach(function() {
            massCollection = view.context.get('mass_collection');
            massCollection.add([{id: 1}, {id: 2}, {id: 3}]);
        });
       it('should clear the mass collection', function() {
           var resetSpy = sinon.collection.spy(massCollection, 'reset');
           view.clearMassCollection();

           expect(resetSpy).toHaveBeenCalled();
           expect(_.isEmpty(massCollection.models)).toBe(true);
       });
        it('should trigger an event on the massCollection', function() {
            var triggerStub = sinon.collection.stub(massCollection, 'trigger');
            view.clearMassCollection();

            expect(triggerStub).toHaveBeenCalled();
        });
    });

    //Events
    describe('getting an "mass_collection:add" event', function() {
        it('should call addModel method', function() {
            var addModelStub = sinon.collection.stub(view, 'addModel');
            view.trigger('init');
            view.context.trigger('mass_collection:add', {});
            expect(addModelStub).toHaveBeenCalled();
        });
    });
    describe('getting an "mass_collection:add:all" event', function() {
        it('should call addAllModels method', function() {
            var addAllModelsStub = sinon.collection.stub(view, 'addAllModels');
            view.trigger('init');
            view.context.trigger('mass_collection:add:all', {});
            expect(addAllModelsStub).toHaveBeenCalled();
        });
    });
    describe('getting an "mass_collection:remove" event', function() {
        it('should call removeModel method', function() {
            var removeModelStub = sinon.collection.stub(view, 'removeModel');
            view.trigger('init');
            view.context.trigger('mass_collection:remove', {});
            expect(removeModelStub).toHaveBeenCalled();
        });
    });
    describe('getting an "mass_collection:remove:all" event', function() {
        it('should call removeAllModels method', function() {
            var removeAllModelsStub = sinon.collection.stub(view, 'removeAllModels');
            view.trigger('init');
            view.context.trigger('mass_collection:remove:all', {});
            expect(removeAllModelsStub).toHaveBeenCalled();
        });
    });
    describe('getting an "mass_collection:clear" event', function() {
        it('should call clearMassCollection method', function() {
            var clearMassCollectionStub = sinon.collection.stub(view, 'clearMassCollection');
            view.trigger('init');
            view.context.trigger('mass_collection:clear');
            expect(clearMassCollectionStub).toHaveBeenCalled();
        });
    });
});
