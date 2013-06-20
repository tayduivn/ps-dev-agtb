describe('Notifications', function () {
    var moduleName = 'Notifications',
        viewName = 'notifications';

    describe('Initialization with default values', function () {
        var app, view;

        beforeEach(function () {
            view = SugarTest.createView('base', moduleName, viewName);
            app = SugarTest.app;
        });

        afterEach(function () {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should bootstrap', function () {
            var _initOptions = sinon.collection.stub(view, '_initOptions', $.noop()),
                _initCollection = sinon.collection.stub(view, '_initCollection', $.noop()),
                startPulling = sinon.collection.stub(view, 'startPulling', $.noop());

            view._bootstrap();

            expect(_initOptions).toHaveBeenCalledOnce();
            expect(_initCollection).toHaveBeenCalledOnce();
            expect(startPulling).toHaveBeenCalledOnce();
        });

        it('should initialize options with default values', function () {
            view._initOptions();

            expect(view.delay / 60 / 1000).toBe(view._defaultOptions.delay);
            expect(view.limit).toBe(view._defaultOptions.limit);
            expect(view.typeCss).toBe(view._defaultOptions.type_css);
        });

        it('should initialize collection options with default values', function () {
            var createBeanCollection = sinon.collection.stub(app.data, 'createBeanCollection', function () {
                return {
                    options: {},
                    off: function () {
                    }
                };
            });

            view._initCollection();

            expect(view.collection.options).toEqual({
                params: {
                    order_by: 'date_entered:desc'
                },
                limit: view.limit,
                myItems: true,
                fields: ['date_entered', 'id', 'name', 'type']
            });
        });
    });

    describe('Initialization with metadata overridden values', function () {
        var app, view, customOptions = {
            delay: 10,
            limit: 8,
            type_css: {
                alert: 'cstm-label-alert',
                information: 'cstm-label-info',
                other: 'cstm-label-inverse',
                success: 'cstm-label-success',
                warning: 'cstm-label-warning'
            }
        };

        beforeEach(function () {
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.addViewDefinition(viewName, customOptions, moduleName);
            SugarTest.testMetadata.set();

            view = SugarTest.createView('base', moduleName, viewName);
            app = SugarTest.app;
        });

        afterEach(function () {
            sinon.collection.restore();
            SugarTest.testMetadata.dispose();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should initialize options with metadata overridden values', function () {
            view._initOptions();

            expect(view.delay / 60 / 1000).toBe(customOptions.delay);
            expect(view.limit).toBe(customOptions.limit);
            expect(view.typeCss).toEqual(customOptions.type_css);
        });

        it('should initialize collection options with metadata overridden values', function () {
            var createBeanCollection = sinon.collection.stub(app.data, 'createBeanCollection', function () {
                return {
                    options: {},
                    off: function () {
                    }
                };
            });

            view._initCollection();

            expect(view.collection.options).toEqual({
                params: {
                    order_by: 'date_entered:desc'
                },
                limit: view.limit,
                myItems: true,
                fields: ['date_entered', 'id', 'name', 'type']
            });
        });
    });

    describe('Pulling mechanism', function () {
        var view;

        beforeEach(function () {
            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function () {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should not pull notifications if disposed', function () {
            // not calling dispose() directly due to it setting inherently the
            // collection to null
            view.disposed = true;
            view.pull();

            expect(view.collection.fetch).not.toHaveBeenCalled();
        });

        it('should not pull notifications if disposed after fetch', function () {
            var fetch = sinon.collection.stub(view.collection, 'fetch', function (o) {
                // not calling dispose() directly due to it setting inherently the
                // collection to null
                view.disposed = true;
                o.success();
            });

            view.pull();

            expect(fetch).toHaveBeenCalledOnce();
            expect(view.render).not.toHaveBeenCalled();
        });

        it('should not pull notifications if opened', function () {
            var isOpened = sinon.collection.stub(view, 'isOpened', function () {
                return true;
            });

            view.pull();

            expect(view.collection.fetch).not.toHaveBeenCalled();
        });

        it('should not pull notifications if opened after fetch', function () {
            var fetch = sinon.collection.stub(view.collection, 'fetch', function (o) {
                var isOpened = sinon.collection.stub(view, 'isOpened', function () {
                    return true;
                });

                o.success();
            });

            view.pull();

            expect(fetch).toHaveBeenCalledOnce();
            expect(view.render).not.toHaveBeenCalled();
        });

        it('should set interval only once on multiple start pulling calls', function () {
            var pull, setInterval;

            pull = sinon.collection.stub(view, 'pull', $.noop());
            setInterval = sinon.collection.stub(window, 'setInterval', $.noop());

            view.startPulling().startPulling();

            expect(pull).toHaveBeenCalledOnce();
            expect(setInterval).toHaveBeenCalledOnce();
        });

        it('should clear interval on stop pulling', function () {
            var pull, setInterval, clearInterval, intervalId = 1;

            pull = sinon.collection.stub(view, 'pull', $.noop());
            setInterval = sinon.collection.stub(window, 'setInterval', function () {
                return intervalId;
            });
            clearInterval = sinon.collection.stub(window, 'clearInterval', $.noop());

            view.startPulling().stopPulling();

            expect(clearInterval).toHaveBeenCalledOnce();
            expect(clearInterval).toHaveBeenCalledWith(intervalId);
            expect(view._intervalId).toBeNull();
        });

        it('should stop pulling on dispose', function () {
            var stopPulling = sinon.collection.stub(view, 'stopPulling', $.noop());

            view.dispose();

            expect(stopPulling).toHaveBeenCalledOnce();
        });

        it('should stop pulling if authentication expires', function () {
            var app = SugarTest.app, isAuthenticated, pull, setInterval, stopPulling;

            pull = sinon.collection.stub(view, 'pull', $.noop());
            setInterval = sinon.collection.stub(window, 'setInterval', function (fn) {
                fn();
            });
            isAuthenticated = sinon.collection.stub(app.api, 'isAuthenticated', function () {
                return false;
            });
            stopPulling = sinon.collection.stub(view, 'stopPulling', $.noop());

            view.startPulling();

            expect(pull).toHaveBeenCalledOnce();
            expect(setInterval).toHaveBeenCalledOnce();
            expect(isAuthenticated).toHaveBeenCalledOnce();
            expect(stopPulling).toHaveBeenCalledOnce();
        });
    });

    describe('Helpers', function () {
        var app, view;

        beforeEach(function () {
            app = SugarTest.app;
            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function () {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        // FIXME: refactor this when data providers support is enabled
        it('should retrieve type as a label for non-existent type', function () {
            var appList, label, type;

            appList = sinon.collection.stub(app.lang, 'getAppListStrings', function () {
                return {};
            });

            type = 'non-existent';
            label = view.getTypeLabel(type);

            expect(appList).toHaveBeenCalledOnce();
            expect(label).toBe(type);
        });

        // FIXME: refactor this when data providers support is enabled
        it('should retrieve matching label for existent type', function () {
            var appList, label, type;

            appList = sinon.collection.stub(app.lang, 'getAppListStrings', function () {
                return {
                    alert: 'Alert'
                };
            });

            type = 'alert';
            label = view.getTypeLabel(type);

            expect(appList).toHaveBeenCalledOnce();
            expect(label).toBe('Alert');
        });

        // FIXME: refactor this when data providers support is enabled
        it('should retrieve an empty string for non-existent type', function () {
            view.typeCss = {};

            var css = view.getTypeCss('non-existent');

            expect(css).toBe('');
        });

        // FIXME: refactor this when data providers support is enabled
        it('should retrieve a css class for existent type', function () {
            view.typeCss = {
                alert: 'label-important'
            };

            var css = view.getTypeCss('alert');

            expect(css).toBe('label-important');
        });
    });
});
