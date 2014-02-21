describe('Home Menu', function() {
    var moduleName = 'Home',
        viewName = 'module-menu',
        app,
        view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', null, moduleName);
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'recently-viewed', moduleName);
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.loadComponent('base', 'view', viewName, moduleName);
        SugarTest.testMetadata.set();

        view = SugarTest.createView('base', moduleName, 'module-menu', null, null);
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
    });

    it('should populate recently viewed on menu open', function() {
        var fetchStub = sinon.collection.stub(view.collection, 'fetch', function(options) {
            options.success.call(this, {
                next_offset: -1,
                models: []
            });
        });

        view.$el.trigger('shown.bs.dropdown');

        expect(fetchStub.calledTwice).toBeTruthy();
    });

    using('different recently records amount and settings', [{
        recordSize: 4,
        nextOffset: -1,
        visible: 1,
        expect: {
            open: false,
            showRecentToggle: true
        }
    },{
        recordSize: 5,
        nextOffset: 5,
        visible: 1,
        expect: {
            open: false,
            showRecentToggle: true
        }
    },{
        recordSize: 3,
        nextOffset: 3,
        visible: 0,
        expect: {
            open: true,
            showRecentToggle: true
        }
    },{
        recordSize: 3,
        nextOffset: -1,
        visible: 0,
        expect: {
            open: true,
            showRecentToggle: false
        }
    }], function(value) {
        it('should show recently viewed toggle based on amount of records found', function() {
            var renderPartialStub = sinon.collection.stub(view, '_renderPartial');

            sinon.collection.stub(app.user.lastState, 'get', function() {
                return value.visible;
            });

            sinon.collection.stub(view.collection, 'fetch', function(options) {

                var models = [];
                for (var i = 0; i < value.recordSize; i++) {
                    models.push(new Backbone.Model({
                        name: 'Record ' + (i + 1)
                    }));
                }

                options.success.call(this, {
                    next_offset: value.nextOffset,
                    models: models
                });
            });

            view.populateRecentlyViewed();
            expect(renderPartialStub).toHaveBeenCalledWith('recently-viewed', value.expect);
        });
    });
});
