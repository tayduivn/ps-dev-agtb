/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
describe('Resolve Conflicts Headerpane View', function() {
    var view, app,
        moduleName = 'Accounts',
        getAppStringStub,
        context;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        getAppStringStub = sinon.stub(app.lang, 'getAppString', function() {
            return 'foo {{name}}'
        });

        context = app.context.getContext();
        context.set({
            module: moduleName,
            modelToSave: new Backbone.Model()
        });
        context.prepare();

        view = SugarTest.createView('base', moduleName, 'resolve-conflicts-headerpane', null, context);
    });

    afterEach(function() {
        getAppStringStub.restore();
        view.dispose();

        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('_setTitle', function() {
        it('should set the title for the headerpane', function() {
            context.get('modelToSave').set('name', 'bar');
            view._setTitle();

            expect(view.title).toBe('foo bar');
        });
    });

    describe('selectClicked', function() {
        var originalDrawer, drawerCloseSpy;

        beforeEach(function() {
            originalDrawer = app.drawer;
            app.drawer = {
                close: function(){}
            };
            drawerCloseSpy = sinon.spy(app.drawer, 'close');
        });

        afterEach(function() {
            drawerCloseSpy.restore();
            app.drawer = originalDrawer;
        });

        it('should close the drawer indicating that it is from the client data', function() {
            var modelToSave = new Backbone.Model();

            context.set('modelToSave', modelToSave);
            context.set('dataInDb', {
                date_modified: 123
            });
            context.set('selection_model', new Backbone.Model({
                _dataOrigin: 'client'
            }));

            view.selectClicked();

            expect(drawerCloseSpy.calledOnce).toBe(true);
            expect(drawerCloseSpy.calledWith(modelToSave, false)).toBe(true);
        });

        it('should close the drawer indicating that it is from the database data', function() {
            var modelToSave = new Backbone.Model();

            context.set('modelToSave', modelToSave);
            context.set('dataInDb', {
                data: 123
            });
            context.set('selection_model', new Backbone.Model({
                _dataOrigin: 'database'
            }));

            view.selectClicked();

            expect(drawerCloseSpy.calledOnce).toBe(true);
            expect(drawerCloseSpy.calledWith(modelToSave, true)).toBe(true);
        });

        it('should copy the date_modified in the database to the model when client data is selected', function() {
            var modelToSave = new Backbone.Model();

            context.set('modelToSave', modelToSave);
            context.set('dataInDb', {
                date_modified: 123
            });
            context.set('selection_model', new Backbone.Model({
                _dataOrigin: 'client'
            }));

            view.selectClicked();

            expect(modelToSave.get('date_modified')).toBe(123);
        });

        it('should copy over the data from the database when the database data is selected', function() {
            var modelToSave = new Backbone.Model();

            context.set('modelToSave', modelToSave);
            context.set('dataInDb', {
                data: 123
            });
            context.set('selection_model', new Backbone.Model({
                _dataOrigin: 'database'
            }));

            view.selectClicked();

            expect(modelToSave.get('data')).toBe(123);
        });
    });
});
