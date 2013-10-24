describe("Base.View.RecordList", function () {
    var view, layout, app, moduleName = 'Cases';

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadHandlebarsTemplate('flex-list', 'view', 'base');
        SugarTest.testMetadata.addViewDefinition('list', {
            'favorite': true,
            'selection': {
                'type': 'multi',
                'actions': []
            },
            'rowactions': {
                'actions': []
            },
            'panels': [
                {
                    'name': 'panel_header',
                    'header': true,
                    'fields': [
                        'name',
                        'case_number',
                        'type',
                        'description',
                        'date_entered',
                        'date_modified',
                        'modified_user_id'
                    ]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        view = SugarTest.createView('base', moduleName, 'recordlist', null, null);
        layout = SugarTest.createLayout('base', moduleName, 'list', null, null);
        view.layout = layout;
        app = SUGAR.App;
    });

    afterEach(function () {
        layout.dispose();
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
    });

    describe('adding actions to list view', function () {

        it('should return my_favorite field when calling getFieldNames', function () {
            var fields = view.getFieldNames();
            expect(_.indexOf(fields, 'my_favorite')).toBeGreaterThan(-1);
        });

        it('should return my_favorite field and save to context for filtering', function () {
            expect(_.indexOf(view.context._recordListFields, 'my_favorite')).toBeGreaterThan(-1);
        });

        it('should have added favorite field', function () {
            view.render();
            expect(view.leftColumns[0].fields[1]).toEqual({type:'favorite'});
        });

        it('should have added favorite field', function () {
            view.dispose();

            SugarTest.testMetadata.updateModuleMetadata("Cases", {
                favoritesEnabled: false
            });
            var nofavoriteview = SugarTest.createView("base", "Cases", "recordlist", null, null);
            nofavoriteview.render();
            var actualFavoriteField = _.where(nofavoriteview.leftColumns[0].fields, {type: 'favorite'});
            expect(actualFavoriteField.length).toBe(0);
            nofavoriteview.dispose();
        });

        it('should have added row actions', function () {
            view.render();
            expect(view.leftColumns[0].fields[2]).toEqual({
                type:'editablelistbutton',
                label:'LBL_CANCEL_BUTTON_LABEL',
                name:'inline-cancel',
                css_class:'btn-link btn-invisible inline-cancel'
            });
            expect(view.rightColumns[0].fields[1]).toEqual({
                type:'editablelistbutton',
                label:'LBL_SAVE_BUTTON_LABEL',
                name:'inline-save',
                css_class:'btn-primary'
            });
            expect(view.rightColumns[0].css_class).toEqual('overflow-visible');
        });
    });

    describe('hasUnsavedChanges', function() {
        beforeEach(function() {
            view.collection = new app.data.createBeanCollection('Cases', [{
                id: 1,
                name: 'First',
                case_number: 123,
                description: 'first description'
            },{
                id: 2,
                name: 'Second',
                case_number: 123,
                description: 'second description'
            },{
                id: 3,
                name: 'Third',
                case_number: 123,
                description: 'third description'
            }]);
            view.render();
        });

        it('should warn unsaved changes among the synced attributes', function() {
            var selectedModelId = '1';
            view.toggleRow(selectedModelId, true);
            var model = view.collection.get(selectedModelId);
            model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(true);
        });

        it('should ignore warning unsaved changes once the edit fields are reverted', function() {
            var selectedModelId = '2';
            view.toggleRow(selectedModelId, true);
            var model = view.collection.get(selectedModelId);
            model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(true);

            view.toggleRow(selectedModelId, false);
            actual = view.hasUnsavedChanges();
            expect(actual).toBe(false);
        });

        it('should inspect unsaved changes on multiple rows', function() {
            var selectedModelId = '3';
            view.toggleRow(selectedModelId, true);
            expect(_.size(view.toggledModels)).toBe(1);

            //set two rows editable
            view.toggleRow('1', true);
            expect(_.size(view.toggledModels)).toBe(2);

            var model = view.collection.get(selectedModelId);
            model.set({
                name: 'Name',
                case_number: 123,
                description: 'Description'
            });
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(true);

            view.toggleRow(selectedModelId, false);
            actual = view.hasUnsavedChanges();
            expect(actual).toBe(false);
            expect(_.size(view.toggledModels)).toBe(1);
        });

        it('should warn unsaved changes ONLY IF the changes are editable fields', function() {
            var selectedModelId = '2';
            view.toggleRow(selectedModelId, true);
            var model = view.collection.get(selectedModelId);

            model._setSyncedAttributes({
                name: 'Original',
                case_number: 456,
                description: 'Previous description',
                non_editable: 'system value'
            });

            //un-editable field
            model.set({
                name: 'Original',
                case_number: 456,
                description: 'Previous description'
            });
            var actual = view.hasUnsavedChanges();
            expect(actual).toBe(false);

            //Changed non-editable field
            model.set({
                non_editable: 'user value'
            });
            actual = view.hasUnsavedChanges();
            var editableFields = _.pluck(view.rowFields[selectedModelId], 'name');
            expect(_.contains(editableFields, 'non_editable')).toBe(false);
            expect(actual).toBe(false);

            //Changed editable field
            model.set({
                description: 'Changed description'
            });
            actual = view.hasUnsavedChanges();
            expect(_.contains(editableFields, 'description')).toBe(true);
            expect(actual).toBe(true);
        });


        describe("Warning delete", function() {
            var sinonSandbox, alertShowStub, routerStub;
            beforeEach(function() {
                sinonSandbox = sinon.sandbox.create();
                routerStub = sinonSandbox.stub(app.router, "navigate");
                sinonSandbox.stub(Backbone.history, "getFragment");
                alertShowStub = sinonSandbox.stub(app.alert, "show");
            });

            afterEach(function() {
                sinonSandbox.restore();
            });

            it("should not alert warning message if _modelToDelete is not defined", function() {
                app.routing.triggerBefore("route");
                expect(alertShowStub).not.toHaveBeenCalled();
            });
            it("should return true if _modelToDelete is not defined", function() {
                sinonSandbox.stub(view, 'warnDelete');
                expect(view.beforeRouteDelete()).toBeTruthy();
            });
            it("should return false if _modelToDelete is defined (to prevent routing to other views)", function() {
                sinonSandbox.stub(view, 'warnDelete');
                view._modelToDelete = new Backbone.Model();
                expect(view.beforeRouteDelete()).toBeFalsy();
            });
            it("should redirect the user to the targetUrl", function() {
                var unbindSpy = sinonSandbox.spy(view, 'unbindBeforeRouteDelete');
                view._modelToDelete = new Backbone.Model();
                view._currentUrl = 'Accounts';
                view._targetUrl = 'Contacts';
                view.deleteModel();
                expect(unbindSpy).toHaveBeenCalled();
                expect(routerStub).toHaveBeenCalled();
            });
        });
    });
});
