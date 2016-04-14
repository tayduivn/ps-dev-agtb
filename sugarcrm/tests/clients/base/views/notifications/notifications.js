describe('Notifications', function() {
    var app, view,
        moduleName = 'Notifications',
        viewName = 'notifications';

    beforeEach(function() {
        app = SugarTest.app;
    });

    describe('Initialization with default values', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function() {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should bootstrap', function() {
            var _initOptions = sinon.collection.stub(view, '_initOptions', $.noop()),
                _initCollection = sinon.collection.stub(view, '_initCollection', $.noop());

            view._bootstrap();

            expect(_initOptions).toHaveBeenCalledOnce();
            expect(_initCollection).toHaveBeenCalledOnce();
        });

        it('should initialize options with default values', function() {
            view._initOptions();

            expect(view.delay / 60 / 1000).toBe(view._defaultOptions.delay);
            expect(view.limit).toBe(view._defaultOptions.limit);
        });

        it('should initialize collection options with default values', function() {
            var createBeanCollection = sinon.collection.stub(app.data, 'createBeanCollection', function() {
                return {
                    options: {},
                    off: function() {
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
                fields: [
                    'date_entered',
                    'id',
                    'is_read',
                    'name',
                    'severity'
                ],
                apiOptions: {
                    skipMetadataHash: true
                }
            });
        });

        describe('should bind listeners on app:socket events', function () {
            beforeEach(function () {
                sinon.stub(app.events, 'on');
                sinon.stub(view, 'socketOn');
                sinon.stub(view, 'socketOff');
            });
            afterEach(function () {
                app.events.on.restore();
                view.socketOn.restore();
                view.socketOff.restore();
            });

            it('should bind socketOn on app app:socket:connect', function () {

                view.initialize({});

                sinon.assert.called(app.events.on);
                sinon.assert.calledWith(app.events.on, 'app:socket:connect');

                sinon.assert.notCalled(view.socketOn);
                for (var i = 0; i < app.events.on.callCount; i++) {
                    var info = app.events.on.getCall(i);
                    if (info.args[0] != 'app:socket:connect') {
                        continue;
                    }
                    if (!_.isUndefined(info.args[1]) && _.isFunction(info.args[1])) {
                        info.args[1]();
                    }
                }
                sinon.assert.called(view.socketOn);
            });

            it('should bind socketOff on app app:socket:disconnect', function () {

                view.initialize({});

                sinon.assert.called(app.events.on);
                sinon.assert.calledWith(app.events.on, 'app:socket:disconnect');

                sinon.assert.notCalled(view.socketOff);
                for (var i = 0; i < app.events.on.callCount; i++) {
                    var info = app.events.on.getCall(i);
                    if (info.args[0] != 'app:socket:disconnect') {
                        continue;
                    }
                    if (!_.isUndefined(info.args[1]) && _.isFunction(info.args[1])) {
                        info.args[1]();
                    }
                }
                sinon.assert.called(view.socketOff);
            });

        });

        describe('should bind listener on app:notifications:markAs events', function () {
            beforeEach(function () {
                sinon.stub(app.events, 'on');
                sinon.stub(view, 'notificationMarkHandler');
            });

            afterEach(function () {
                app.events.on.restore();
                view.notificationMarkHandler.restore();
            });

            it('should bind notificationMarkHandler on app app:notifications:markAs', function () {

                view.initialize({});

                sinon.assert.called(app.events.on);
                sinon.assert.calledWith(app.events.on, 'app:notifications:markAs');

                sinon.assert.notCalled(view.notificationMarkHandler);
                for (var i = 0; i < app.events.on.callCount; i++) {
                    var info = app.events.on.getCall(i);
                    if (info.args[0] != 'app:notifications:markAs') {
                        continue;
                    }
                    if (!_.isUndefined(info.args[1]) && _.isFunction(info.args[1])) {
                        info.args[1]();
                    }
                }
                sinon.assert.called(view.notificationMarkHandler);
            });
        });
    });

    describe('Initialization with metadata overridden values', function() {
        var customOptions = {
            delay: 10,
            limit: 8
        };

        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.addViewDefinition(viewName, customOptions, moduleName);
            SugarTest.testMetadata.set();

            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function() {
            sinon.collection.restore();
            SugarTest.testMetadata.dispose();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should initialize options with metadata overridden values', function() {
            view._initOptions();

            expect(view.delay / 60 / 1000).toBe(customOptions.delay);
            expect(view.limit).toBe(customOptions.limit);
        });

        it('should initialize collection options with metadata overridden values', function() {
            var createBeanCollection = sinon.collection.stub(app.data, 'createBeanCollection', function() {
                return {
                    options: {},
                    off: function() {
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
                fields: [
                    'date_entered',
                    'id',
                    'is_read',
                    'name',
                    'severity'
                ],
                apiOptions: {
                    skipMetadataHash: true
                }
            });
        });
    });

    describe('Pulling mechanism', function() {
        beforeEach(function() {
            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function() {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should not pull notifications if disposed', function() {
            // not calling dispose() directly due to it setting inherently the
            // collection to null
            view.disposed = true;
            view.pull();

            expect(view.collection.fetch).not.toHaveBeenCalled();
            view.disposed = false;
        });

        it('should not pull notifications if disposed after fetch', function() {
            var fetch = sinon.collection.stub(view.collection, 'fetch', function(o) {
                // not calling dispose() directly due to it setting inherently the
                // collection to null
                view.disposed = true;
                o.success();
            });

            view.pull();

            expect(fetch).toHaveBeenCalledOnce();
            expect(view.render).not.toHaveBeenCalled();
            view.disposed = false;
        });

        it('should not pull notifications if open', function() {
            var isOpen = sinon.collection.stub(view, 'isOpen', function() {
                return true;
            });

            view.pull();

            expect(view.collection.fetch).not.toHaveBeenCalled();
        });

        it('should not pull notifications if open after fetch', function() {
            var fetch = sinon.collection.stub(view.collection, 'fetch', function(o) {
                var isOpen = sinon.collection.stub(view, 'isOpen', function() {
                    return true;
                });

                o.success();
            });

            view.pull();

            expect(fetch).toHaveBeenCalledOnce();
            expect(view.render).not.toHaveBeenCalled();
        });

        it('should set timeout and call pull once on multiple start pulling calls', function() {
            var pull = sinon.collection.stub(view, 'pull', $.noop()),
                setTimeout = sinon.collection.stub(window, 'setTimeout', $.noop());

            view.startPulling().startPulling();

            expect(pull).toHaveBeenCalledOnce();
            expect(setTimeout).toHaveBeenCalledOnce();
        });

        it('should clear intervals on stop pulling', function() {
            var pull = sinon.collection.stub(view, 'pull', $.noop()),
                setTimeout = sinon.collection.stub(window, 'setTimeout', function() {
                    return intervalId;
                }),
                clearTimeout = sinon.collection.stub(window, 'clearTimeout', $.noop()),
                intervalId = 1;

            view.startPulling().stopPulling();

            expect(clearTimeout).toHaveBeenCalledOnce();
            expect(view._intervalId).toBeNull();
        });

        it('should stop pulling on dispose', function() {
            var stopPulling = sinon.collection.stub(view, 'stopPulling', $.noop());

            view.dispose();

            expect(stopPulling).toHaveBeenCalledOnce();
        });

        it('should stop pulling if authentication expires', function() {
            var isAuthenticated = sinon.collection.stub(app.api, 'isAuthenticated', function() {
                    return false;
                }),
                pull = sinon.collection.stub(view, 'pull', $.noop()),
                setTimeout = sinon.collection.stub(window, 'setTimeout', function(fn) {
                    fn();
                }),
                stopPulling = sinon.collection.stub(view, 'stopPulling', $.noop());

            view.startPulling();

            expect(pull).toHaveBeenCalledOnce();
            expect(setTimeout).toHaveBeenCalledOnce();
            expect(isAuthenticated).toHaveBeenCalledOnce();
            expect(stopPulling).toHaveBeenCalledOnce();
        });

        it('should stop pulling if connected to socket', function () {
            var isAuthenticated = sinon.collection.stub(app.api, 'isAuthenticated', function () {
                    return true;
            }),
            pull = sinon.collection.stub(view, 'pull', $.noop()),
            setTimeout = sinon.collection.stub(window, 'setTimeout', function (fn) {
                    fn();
            });

            view.isSocketConnected = true;
            view._pullAction();

            expect(pull).not.toHaveBeenCalled();
            expect(setTimeout).not.toHaveBeenCalled();
        });
    });

    describe('Socket mechanism', function () {
        beforeEach(function () {
            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function () {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('socket off', function () {
            view.isSocketConnected = true;
            view.startPulling = sinon.collection.stub();
            app.socket.off = sinon.collection.stub();
            view.catchNotification = sinon.collection.stub();

            view.socketOff();

            expect(view.isSocketConnected).toBeFalsy();

            sinon.assert.called(view.startPulling);
            sinon.assert.called(app.socket.off);
            sinon.assert.calledWith(app.socket.off, 'notification');

            app.socket.off.getCall(0).args[1]();
            sinon.assert.called(view.catchNotification);
        });

        it('socket on', function () {
            view.isSocketConnected = false;
            view.stopPulling = sinon.collection.stub();
            view.catchNotification = sinon.collection.stub();
            app.socket.on = sinon.collection.stub();

            view.socketOn();

            expect(view.isSocketConnected).toBeTruthy();

            sinon.assert.called(view.stopPulling);
            sinon.assert.called(app.socket.on);
            sinon.assert.calledWith(app.socket.on, 'notification');

            app.socket.on.getCall(0).args[1]();
            sinon.assert.called(view.catchNotification);
        });

        it('catchNotification', function () {
            var catchedNotif = 'catched Notif';
            view.transferToCollection = sinon.collection.stub();
            view._buffer = [];

            view.catchNotification(catchedNotif);

            expect(view._buffer[0]).toBe(catchedNotif);
            sinon.assert.called(view.transferToCollection);
        });

        describe('transferToCollection', function () {
            beforeEach(function () {
                sinon.stub(view, 'reRender');
                sinon.stub(app.data, 'createBean');
            });

            afterEach(function () {
                view.reRender.restore();
                app.data.createBean.restore();
                view.collection = null;
            });

            it('check calling transferToCollection before bootstrap', function () {
                view._buffer = [
                    {data: 'someData1', _module: 'module'},
                    {data: 'someData2', _module: 'module'}
                ];

                view.collection = null;

                view.transferToCollection();

                sinon.assert.notCalled(view.reRender);
            });

            describe('check calling transferToCollection after bootstrap', function () {
                beforeEach(function () {
                    view.collection = {
                        add: sinon.spy(),
                        get: sinon.stub(),
                        remove: sinon.spy()
                    };
                });

                it('adding notifications', function () {
                    var buffer = [
                            {data: 'someData1', is_read: false, _module: 'module'},
                            {data: 'someData2', is_read: false, _module: 'module'}
                        ],
                        models = ['Model1', 'Model2'];

                    view._buffer = buffer;

                    app.data.createBean
                        .withArgs(buffer[0]['_module'], _.clone(buffer[0])).returns(models[0])
                        .withArgs(buffer[1]['_module'], _.clone(buffer[1])).returns(models[1]);

                    view.transferToCollection();

                    sinon.assert.called(view.reRender);
                    sinon.assert.calledTwice(app.data.createBean);

                    sinon.assert.notCalled(view.collection.get);
                    sinon.assert.notCalled(view.collection.remove);
                    sinon.assert.calledTwice(view.collection.add);
                    sinon.assert.calledWith(view.collection.add, models[0]);
                    sinon.assert.calledWith(view.collection.add, models[1]);
                });

                it('removing notifications', function () {
                    var buffer = [
                            {data: 'someData1', is_read: true, id: 'id:1:' + Math.random(), _module: 'module'},
                            {data: 'someData2', is_read: true, id: 'id:2:' + Math.random(), _module: 'module'}
                        ],
                        models = ['Model1:' + Math.random(), 'Model1:' + Math.random()];

                    view._buffer = _.clone(buffer);

                    view.collection.get
                        .withArgs(buffer[0].id).returns(models[0])
                        .withArgs(buffer[1].id).returns(models[1]);

                    view.transferToCollection();

                    sinon.assert.called(view.reRender);
                    sinon.assert.notCalled(app.data.createBean);
                    sinon.assert.notCalled(view.collection.add);
                    sinon.assert.calledTwice(view.collection.get);
                    sinon.assert.calledWith(view.collection.get, buffer[0].id);
                    sinon.assert.calledWith(view.collection.get, buffer[1].id);
                    sinon.assert.calledTwice(view.collection.remove);
                    sinon.assert.calledWith(view.collection.remove, models[0]);
                    sinon.assert.calledWith(view.collection.remove, models[0]);
                });
            });

            it('check calling transferToCollection after bootstrap if empty buffer', function () {
                view._buffer = [];

                view.collection = {
                    add: sinon.spy()
                };

                view.transferToCollection();

                sinon.assert.called(view.reRender);
                sinon.assert.notCalled(app.data.createBean);
                sinon.assert.notCalled(view.collection.add);
            });
        });
    });

    describe('Notification favicon badge', function() {
        beforeEach(function() {

            // Library mock
            Favico = function() {
                return {
                    badge: $.noop,
                    reset: $.noop
                };
            };

            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function() {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;

            // remove Libarary mock
            delete Favico;
        });

        using('different counts', [
                [23, -1, 23],
                [7, 7, '7+']
            ], function(length, offset, expected) {
                it('should update favicon badge with the correct unread notifications ' +
                    'if notification is added to the collection', function() {
                    view._bootstrap();

                    var badge = sinon.collection.stub(view.favicon, 'badge');
                    view.collection.length = length;
                    view.collection.next_offset = offset;
                    view.collection.trigger('add');

                    expect(badge).toHaveBeenCalledWith(expected);
                });
                it('should update favicon badge with the correct unread notifications ' +
                    'if notification is removed from the collection', function() {
                    view._bootstrap();

                    var badge = sinon.collection.stub(view.favicon, 'badge');
                    view.collection.length = length;
                    view.collection.next_offset = offset;
                    view.collection.trigger('remove');

                    expect(badge).toHaveBeenCalledWith(expected);
                });
                it('should update favicon badge with the correct unread notifications ' +
                    'if the collection is reset', function() {
                    view._bootstrap();

                    var badge = sinon.collection.stub(view.favicon, 'badge');
                    view.collection.length = length;
                    view.collection.next_offset = offset;
                    view.collection.trigger('reset');

                    expect(badge).toHaveBeenCalledWith(expected);
                });
            }
        );

        it('should reset favicon badge if authentication expires or user logout', function() {
            view._bootstrap();

            var resetStub = sinon.collection.stub(view.favicon, 'reset');
            sinon.collection.stub(app.api, 'isAuthenticated', function() {
                return false;
            });

            view.render();

            expect(resetStub).toHaveBeenCalledOnce();
        });
    });

    describe('notificationMarkHandler', function() {
        var model;

        beforeEach(function() {
            view = SugarTest.createView('base', moduleName, viewName);
            view._initCollection();
            model = app.data.createBean(moduleName);
            sinon.stub(view.collection, 'remove');
            sinon.stub(view.collection, 'add');
            sinon.stub(view, 'reRender');
        });

        afterEach(function() {
            model = null;
            view.collection.remove.restore();
            view.collection.add.restore();
            view.reRender.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should remove read notification bean from collection', function() {
            view.notificationMarkHandler(model, true);

            sinon.assert.called(view.collection.remove);
            sinon.assert.calledWith(view.collection.remove, model);
        });

        it('should add unread notification bean to collection', function() {
            view.notificationMarkHandler(model, false);

            sinon.assert.called(view.collection.add);
            sinon.assert.calledWith(view.collection.add, model);
        });

        it('should re-render view in case of read and unread notification mark', function() {
            view.notificationMarkHandler(model, true);
            view.notificationMarkHandler(model, false);

            sinon.assert.calledTwice(view.reRender);
        });
    });
});
