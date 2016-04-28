describe('Sugar Socket Server Client', function() {
    var scope, app, initSocketConstructor, source;


    beforeEach(function() {
        scope = sinon.sandbox.create();

        if (!source) {
            source = SugarTest.loadFile('../include/javascript/sugar7', 'socket', 'js', function(source) {
                return source;
            });
        }

        app = {
            config: {},
            events: {
                on: scope.stub().returnsThis(),
                off: scope.stub().returnsThis(),
                trigger: scope.stub().returnsThis()
            },
            augment: function(name, data) {
                app[name] = data;
            },
            socket: null
        };

        initSocketConstructor = scope.spy();

        var SUGAR = {
            App: app
        };
        var lazySocketConstructor = function(callback) {
            initSocketConstructor = callback;
        };
        eval(source);
    });
    afterEach(function() {
        scope.restore();
    });

    describe('Socket', function() {
        describe('Socket', function() {
            beforeEach(function() {
                scope.stub(app.socket, '_initConfig');
                scope.stub(app.socket, '_appSync');
                initSocketConstructor();
            });

            it('binds _initConfig on app:init event', function() {
                sinon.assert.called(app.events.on);
                sinon.assert.called(app.events.on, app.events);
                sinon.assert.calledWith(app.events.on, 'app:init');

                var callback = app.events.on.getCall(0).args[1];
                var bind = app.events.on.getCall(0).args[2];

                expect(bind).toBe(app.socket);
                callback();

                sinon.assert.calledOnce(app.socket._initConfig);
            });
            it('binds _appSync on app:notifications:socket:config:changed event', function() {
                sinon.assert.called(app.events.on);
                sinon.assert.called(app.events.on, app.events);
                sinon.assert.calledWith(app.events.on, 'app:notifications:socket:config:changed');

                var callback = app.events.on.getCall(1).args[1];
                var bind = app.events.on.getCall(1).args[2];

                expect(bind).toBe(app.socket);
                callback();

                sinon.assert.calledOnce(app.socket._appSync);
            });
        });
        describe('_initConfig', function() {
            var $Stub;

            beforeEach(function() {
                $Stub = {
                    get: scope.stub().returnsThis(),
                    done: scope.stub().returnsThis()
                };

                scope.stub(app.socket, '_initClientLibrary');
                scope.stub(app.socket, '_Factory$').returns($Stub);
            });

            it('does not execute _initClientLibrary if config.websockets is not defined', function() {
                app.socket._initConfig();
                sinon.assert.notCalled(app.socket._initClientLibrary);
            });
            it('does not execute _initClientLibrary if config.websockets.client is not defined', function() {
                app.config.websockets = {};
                app.socket._initConfig();
                sinon.assert.notCalled(app.socket._initClientLibrary);
            });
            it('does not execute _initClientLibrary if config.websockets.client.url is not defined', function() {
                app.config.websockets = {
                    client: {}
                };
                app.socket._initConfig();
                sinon.assert.notCalled(app.socket._initClientLibrary);
            });
            it('does not execute _initClientLibrary if config.websockets.client.url is empty', function() {
                app.config.websockets = {
                    client: {
                        url: ''
                    }
                };
                app.socket._initConfig();
                sinon.assert.notCalled(app.socket._initClientLibrary);
            });
            it('does not call _Factory$ when balancer is not required', function() {
                app.config.websockets = {
                    client: {
                        url: 'http://domain' + Math.round(Math.random() * 100) + '.com',
                        balancer: false
                    }
                };
                app.socket._initConfig();
                sinon.assert.notCalled(app.socket._Factory$);
            });
            it('calls _initClientLibrary with config url when balancer is not required', function() {
                app.config.websockets = {
                    client: {
                        url: 'http://domain' + Math.round(Math.random() * 100) + '.com',
                        balancer: false
                    }
                };
                app.socket._initConfig();
                sinon.assert.calledOnce(app.socket._initClientLibrary);
                sinon.assert.calledWith(app.socket._initClientLibrary, app.config.websockets.client.url);
            });
            it('sends get request to balancer if it is required', function() {
                app.config.websockets = {
                    client: {
                        url: 'http://domain' + Math.round(Math.random() * 100) + '.com',
                        balancer: true
                    }
                };
                app.socket._initConfig();
                sinon.assert.calledOnce($Stub.get);
                sinon.assert.calledWith($Stub.get, app.config.websockets.client.url);
                sinon.assert.notCalled(app.socket._initClientLibrary);
            });
            it('does not call _initClientLibrary in done callback of request when result does not have location',
                function() {
                    app.config.websockets = {
                        client: {
                            url: 'http://domain' + Math.round(Math.random() * 100) + '.com',
                            balancer: true
                        }
                    };
                    app.socket._initConfig();
                    sinon.assert.calledOnce($Stub.get);
                    sinon.assert.calledWith($Stub.get, app.config.websockets.client.url);
                    sinon.assert.notCalled(app.socket._initClientLibrary);
                    sinon.assert.calledOnce($Stub.done);
                    $Stub.done.getCall(0).args[0]();
                    sinon.assert.notCalled(app.socket._initClientLibrary);
                });
            it('does not call _initClientLibrary in done callback of request when result have empty location',
                function() {
                    app.config.websockets = {
                        client: {
                            url: 'http://domain' + Math.round(Math.random() * 100) + '.com',
                            balancer: true
                        }
                    };
                    var location = '';
                    app.socket._initConfig();
                    sinon.assert.calledOnce($Stub.get);
                    sinon.assert.calledWith($Stub.get, app.config.websockets.client.url);
                    sinon.assert.notCalled(app.socket._initClientLibrary);
                    sinon.assert.calledOnce($Stub.done);
                    $Stub.done.getCall(0).args[0]({location: location});
                    sinon.assert.notCalled(app.socket._initClientLibrary);
                });
            it('calls _initClientLibrary with response url in done callback of request when result has location',
                function() {
                    app.config.websockets = {
                        client: {
                            url: 'http://domain' + Math.round(Math.random() * 100) + '.com',
                            balancer: true
                        }
                    };
                    var location = 'http://domain' + Math.round(Math.random() * 100) + '.com';
                    app.socket._initConfig();
                    sinon.assert.calledOnce($Stub.get);
                    sinon.assert.calledWith($Stub.get, app.config.websockets.client.url);
                    sinon.assert.notCalled(app.socket._initClientLibrary);
                    sinon.assert.calledOnce($Stub.done);
                    $Stub.done.getCall(0).args[0]({location: location});
                    sinon.assert.calledOnce(app.socket._initClientLibrary);
                    sinon.assert.calledWith(app.socket._initClientLibrary, location);
                });
        });
        describe('_initClientLibrary', function() {
            var $Stub, url;

            beforeEach(function() {
                $Stub = {
                    getScript: scope.stub().returnsThis()
                };
                url = 'http://domain' + Math.round(Math.random() * 100) + '.com';

                scope.stub(app.socket, '_Factory$').returns($Stub);
                scope.stub(app.socket, '_initSocket');
            });

            it('calls getScript with correct url', function() {
                app.socket._initClientLibrary(url);
                sinon.assert.calledOnce($Stub.getScript);
                sinon.assert.calledWith($Stub.getScript, url + '/socket.io/socket.io.js');
            });
            it('calls getScript with correct callback', function() {
                app.socket._initClientLibrary(url);
                sinon.assert.calledOnce($Stub.getScript);
                sinon.assert.notCalled(app.socket._initSocket);
                $Stub.getScript.getCall(0).args[1]();
                sinon.assert.calledOnce(app.socket._initSocket);
                sinon.assert.calledOn(app.socket._initSocket, app.socket);
                sinon.assert.calledWith(app.socket._initSocket, url);
            });
        });
        describe('_initSocket', function() {
            var IOStub, url;

            beforeEach(function() {
                IOStub = {
                    open: scope.stub().returnsThis()
                };
                url = 'http://domain' + Math.round(Math.random() * 100) + '.com';

                scope.stub(app.socket, '_bind');
                scope.stub(app.socket, '_FactoryIO').returns(IOStub);
            });

            it('calls _FactoryIO with correct url and parameters', function() {
                app.socket._initSocket(url);
                sinon.assert.calledOnce(app.socket._FactoryIO);
                sinon.assert.calledWith(app.socket._FactoryIO, url, {
                    autoConnect: false
                });
            });
            it('calls _bind', function() {
                app.socket._initSocket(url);
                sinon.assert.calledOnce(app.socket._bind);
            });
            it('opens connection to socket', function() {
                app.socket._initSocket(url);
                sinon.assert.calledOnce(IOStub.open);
            });
            it('uses right order of calls', function() {
                app.socket._initSocket(url);
                sinon.assert.callOrder(app.socket._FactoryIO, app.socket._bind, IOStub.open);
            });
        });
        describe('_bind', function() {
            var IOStub, url;

            beforeEach(function() {
                IOStub = {
                    on: scope.stub().returnsThis()
                };
                url = 'http://domain' + Math.round(Math.random() * 100) + '.com';

                app.socket._socketBinds = {
                    onConnect: null,
                    onConnectAuth: null,
                    onDisconnect: null,
                    onMessage: null,
                    onClose: null
                };

                scope.stub(app.socket, 'on');
                scope.stub(app.socket, 'authorize');
                scope.stub(app.socket, '_message');
                scope.stub(app.socket, '_appSync');
                scope.stub(app.socket, 'socket').returns(IOStub);
            });

            it('subscribes to app:login:success event of app with authorize method', function() {
                app.socket._bind();
                sinon.assert.called(app.events.on);
                sinon.assert.calledWith(app.events.on, 'app:login:success', app.socket.authorize, app.socket);
            });
            it('subscribes to app:logout event of app with authorize method', function() {
                app.socket._bind();
                sinon.assert.called(app.events.on);
                sinon.assert.calledWith(app.events.on, 'app:logout', app.socket.authorize, app.socket);
            });
            it('subscribes to connect event of socket with authorize method', function() {
                app.socket._bind();
                sinon.assert.called(IOStub.on);
                sinon.assert.calledWith(IOStub.on, 'connect');
                sinon.assert.notCalled(app.socket.authorize);
                for (var i = 0; i < IOStub.on.callCount; i ++) {
                    var info = IOStub.on.getCall(i);
                    if (info.args[0] != 'connect') {
                        continue;
                    }
                    expect(info.args[1]).toBeDefined();
                    info.args[1]();
                }
                sinon.assert.called(app.socket.authorize);
                sinon.assert.calledOn(app.socket.authorize, app.socket);
            });
            it('subscribes to message event of socket with _message method', function() {
                app.socket._bind();
                sinon.assert.called(IOStub.on);
                sinon.assert.calledWith(IOStub.on, 'message');
                for (var i = 0; i < IOStub.on.callCount; i ++) {
                    var info = IOStub.on.getCall(i);
                    if (info.args[0] != 'message') {
                        continue;
                    }
                    expect(info.args[1]).toBeDefined();
                    sinon.assert.notCalled(app.socket._message);
                    info.args[1]();
                    sinon.assert.called(app.socket._message);
                    sinon.assert.calledOn(app.socket._message, app.socket);
                }
            });
            it('subscribes to close event with _appSync method', function() {
                app.socket._bind();
                sinon.assert.called(app.socket.on);
                sinon.assert.calledWith(app.socket.on, 'close');
                for (var i = 0; i < app.socket.on.callCount; i ++) {
                    var info = app.socket.on.getCall(i);
                    if (info.args[0] != 'close') {
                        continue;
                    }
                    expect(info.args[1]).toBeDefined();
                    sinon.assert.notCalled(app.socket._appSync);
                    info.args[1]();
                    sinon.assert.called(app.socket._appSync);
                    sinon.assert.calledOn(app.socket._appSync, app.socket);
                }
            });
            ['connect', 'disconnect'].forEach(function (event) {
                it('is bound event ' + event, function () {
                    app.socket._bind();
                    sinon.assert.called(IOStub.on);
                    sinon.assert.calledWith(IOStub.on, event);

                    for (var i = 0; i < IOStub.on.callCount; i++) {
                        var call = IOStub.on.getCall(i);
                        if (call.args[0] != event) {
                            continue;
                        }
                        call.args[1]();
                    }


                    sinon.assert.called(app.events.trigger);
                    sinon.assert.calledOn(app.events.trigger, app.events);
                    sinon.assert.calledWith(app.events.trigger, 'app:socket:' + event);
                })
            });
        });
        describe('_unbind', function() {
            var IOStub;
            var socketBinds;

            beforeEach(function() {
                IOStub = {
                    off: scope.stub().returnsThis()
                };

                scope.stub(app.socket, 'off');
                scope.stub(app.socket, 'authorize');
                scope.stub(app.socket, 'socket').returns(IOStub);

                socketBinds = {
                    onConnect: Math.random(),
                    onConnectAuth: Math.random(),
                    onDisconnect: Math.random(),
                    onMessage: Math.random(),
                    onClose: Math.random()
                };

                app.socket._socketBinds = {
                    onConnect: socketBinds.onConnect,
                    onConnectAuth: socketBinds.onConnectAuth,
                    onDisconnect: socketBinds.onDisconnect,
                    onMessage: socketBinds.onMessage,
                    onClose: socketBinds.onClose
                };
            });

            it('unsubscribes from app:login:success event of app with authorize method', function() {
                app.socket._unbind();
                sinon.assert.called(app.events.off);
                sinon.assert.calledWith(app.events.off, 'app:login:success', app.socket.authorize, app.socket);
            });
            it('unsubscribes from app:logout event of app with authorize method', function() {
                app.socket._unbind();
                sinon.assert.called(app.events.off);
                sinon.assert.calledWith(app.events.off, 'app:logout', app.socket.authorize, app.socket);
            });
            it('unsubscribes from connect event of socket with authorize and app.events.trigger methods',
                function() {
                    app.socket._unbind();
                    sinon.assert.called(IOStub.off);
                    for (var i = 0; i < IOStub.off.callCount; i ++) {
                        var info = IOStub.off.getCall(i);
                        if (info.args[0] != 'connect') {
                            continue;
                        }
                        expect(info.args[1]).toBeDefined();
                        var index = [
                            socketBinds.onConnect,
                            socketBinds.onConnectAuth
                        ].indexOf(info.args[1]);
                        expect(index).toBeGreaterThan(-1);
                    }
                    expect(app.socket._socketBinds.onConnect).toBeNull();
                    expect(app.socket._socketBinds.onConnectAuth).toBeNull();
                });
            it('unsubscribes from message event of socket with _message method', function() {
                app.socket._unbind();
                sinon.assert.called(IOStub.off);
                for (var i = 0; i < IOStub.off.callCount; i ++) {
                    var info = IOStub.off.getCall(i);
                    if (info.args[0] != 'message') {
                        continue;
                    }
                    expect(info.args[1]).toBeDefined();
                    expect(info.args[1]).toEqual(socketBinds.onMessage);
                }
                expect(app.socket._socketBinds.onMessage).toBeNull();
            });
            it('unsubscribes from close event with _appSync method', function() {
                app.socket._unbind();
                sinon.assert.called(app.socket.off);
                for (var i = 0; i < app.socket.off.callCount; i ++) {
                    var info = app.socket.off.getCall(i);
                    if (info.args[0] != 'close') {
                        continue;
                    }
                    expect(info.args[1]).toBeDefined();
                    expect(info.args[1]).toEqual(socketBinds.onClose);
                }
                expect(app.socket._socketBinds.onClose).toBeNull();
            });
            it('unsubscribes from disconnect event with app.events.trigger method', function() {
                app.socket._unbind();
                sinon.assert.called(IOStub.off);
                for (var i = 0; i < IOStub.off.callCount; i ++) {
                    var info = IOStub.off.getCall(i);
                    if (info.args[0] != 'disconnect') {
                        continue;
                    }
                    expect(info.args[1]).toBeDefined();
                    expect(info.args[1]).toEqual(socketBinds.onDisconnect);
                }
                expect(app.socket._socketBinds.onDisconnect).toBeNull();
            });
        });
        describe('_message', function() {
            var triggerStub, channel1Stub, channel2Stub, factoryChannelStub, message;

            beforeEach(function() {
                message = {
                    message: Math.random(),
                    args: Math.random()
                };
                channel1Stub = {
                    trigger: scope.stub().returnsThis(),
                    systemEvents: {
                        on: scope.stub().returnsThis()
                    }
                };
                channel2Stub = {
                    trigger: scope.stub().returnsThis(),
                    systemEvents: {
                        on: scope.stub().returnsThis()
                    }
                };
                triggerStub = scope.stub(app.socket, 'trigger');
                factoryChannelStub = scope.stub(app.socket, '_FactoryChannel');

                factoryChannelStub.withArgs('channel1').returns(channel1Stub);
                factoryChannelStub.withArgs('channel2').returns(channel2Stub);

                app.socket.channel('channel1');
                app.socket.channel('channel2');
            });
            afterEach(function() {
                app.socket._destroyChannel('channel1');
                app.socket._destroyChannel('channel2');
            });

            it('triggers event with args globally if channel is not present', function() {
                app.socket._message(message);
                sinon.assert.calledOnce(triggerStub);
                sinon.assert.calledOn(triggerStub, app.socket);
                sinon.assert.calledWith(triggerStub, message.message, message.args);
                sinon.assert.notCalled(channel1Stub.trigger);
                sinon.assert.notCalled(channel2Stub.trigger);
            });
            it('does not trigger event globally is channel is present', function() {
                message.channel = Math.random();
                app.socket._message(message);
                sinon.assert.notCalled(triggerStub);
            });
            it('triggers event in required channel is channel is present', function() {
                message.channel = 'channel1';
                app.socket._message(message);
                sinon.assert.notCalled(triggerStub);
                sinon.assert.calledOnce(channel1Stub.trigger);
                sinon.assert.calledOn(channel1Stub.trigger, channel1Stub);
                sinon.assert.calledWith(channel1Stub.trigger, message.message, message.args);
                sinon.assert.notCalled(channel2Stub.trigger);
            });
        });
        describe('_appSync', function() {
            var webSocketsConfig;

            beforeEach(function() {
                app.sync = scope.stub();
                webSocketsConfig = Math.random();
                app.config.websockets = webSocketsConfig;

                scope.stub(app.socket, '_onAppSync');
            });
            afterEach(function() {
                delete app.sync;
            });

            it('calls app.sync with _onAppSync binding as callback', function() {
                app.socket._appSync();
                sinon.assert.calledOnce(app.sync);

                var callback = app.sync.getCall(0);
                expect(callback.args[0]).toBeDefined();
                expect(callback.args[0].callback).toBeDefined();
                sinon.assert.notCalled(app.socket._onAppSync);
                callback.args[0].callback();
                sinon.assert.called(app.socket._onAppSync);
                sinon.assert.calledOn(app.socket._onAppSync, app.socket);
                sinon.assert.calledWith(app.socket._onAppSync, webSocketsConfig);
            });
            it('not call app.sync on syncing', function() {
                app.socket._appSync();
                app.socket._appSync();

                sinon.assert.calledOnce(app.sync);
            });
        });
        describe('_onAppSync', function() {
            var IOStub;
            var webSocketsConfig;

            beforeEach(function() {
                webSocketsConfig = {
                    url: Math.random()
                };
                app.config.websockets = webSocketsConfig;
                IOStub = {
                    close: scope.stub()
                };

                scope.stub(app.socket, '_unbind');
                scope.stub(app.socket, '_initConfig');
                scope.stub(app.socket, 'socket').returns(IOStub);
            });

            it('does nothing if old websockets config equals new one', function() {
                var oldWebSocketsConfig = {
                    url: webSocketsConfig.url
                };
                app.socket._onAppSync(oldWebSocketsConfig);
                sinon.assert.notCalled(app.socket._unbind);
                sinon.assert.notCalled(app.socket._initConfig);
                sinon.assert.notCalled(IOStub.close);
            });
            it('calls _unbind if socket io is created', function() {
                var oldWebSocketsConfig = {
                    url: Math.random()
                };
                app.socket._onAppSync(oldWebSocketsConfig);
                sinon.assert.calledOnce(app.socket._unbind);
                sinon.assert.calledOn(app.socket._unbind, app.socket);
            });
            it('closes socket if socket io is created', function() {
                var oldWebSocketsConfig = {
                    url: Math.random()
                };
                app.socket._onAppSync(oldWebSocketsConfig);
                sinon.assert.calledOnce(IOStub.close);
                sinon.assert.calledOn(IOStub.close, IOStub);
            });
            it('calls _initConfig', function() {
                var oldWebSocketsConfig = {
                    url: Math.random()
                };
                app.socket._onAppSync(oldWebSocketsConfig);
                sinon.assert.calledOnce(app.socket._initConfig);
                sinon.assert.calledOn(app.socket._initConfig, app.socket);
            });
        });
        describe('authorize', function() {
            var token, channels, IOStub;

            beforeEach(function() {
                token = Math.random();
                channels = Math.random();

                app.config.siteUrl = Math.random();
                app.config.serverUrl = Math.random();
                app.api = {
                    getOAuthToken: scope.stub().returns(token)
                };

                IOStub = {
                    emit: scope.stub().returnsThis()
                };

                scope.stub(app.socket, '_currentChannels').returns(channels);
                scope.stub(app.socket, 'socket').returns(IOStub);
            });

            it('emits OAuthToken message to socket', function() {
                app.socket.authorize();
                sinon.assert.calledOnce(IOStub.emit);
                sinon.assert.calledOn(IOStub.emit, IOStub);
                sinon.assert.calledWith(IOStub.emit, 'OAuthToken');
            });
            it('passes correct siteUrl to message', function() {
                app.socket.authorize();
                sinon.assert.calledOnce(IOStub.emit);
                var actual = IOStub.emit.getCall(0).args[1];
                expect(actual.siteUrl).toBe(app.config.siteUrl);
            });
            it('passes correct serverUrl to message', function() {
                app.socket.authorize();
                sinon.assert.calledOnce(IOStub.emit);
                var actual = IOStub.emit.getCall(0).args[1];
                expect(actual.serverUrl).toBe(app.config.serverUrl);
            });
            it('passes correct token to message', function() {
                app.socket.authorize();
                sinon.assert.calledOnce(IOStub.emit);
                var actual = IOStub.emit.getCall(0).args[1];
                expect(actual.token).toBe(token);
            });
            it('passes correct channels to message', function() {
                app.socket.authorize();
                sinon.assert.calledOnce(IOStub.emit);
                var actual = IOStub.emit.getCall(0).args[1];
                expect(actual.channels).toBe(channels);
            });
        });
        describe('_currentChannels', function() {
            var IOStub, channel1Stub, channel2Stub;

            beforeEach(function() {
                IOStub = {
                    on: scope.stub().returnsThis()
                };

                channel1Stub = {
                    name: Math.random() + '',
                    isEmpty: scope.stub().returns(false),
                    systemEvents: {
                        on: scope.stub().returns(this)
                    }
                };

                channel2Stub = {
                    name: Math.random() + '',
                    isEmpty: scope.stub().returns(false),
                    systemEvents: {
                        on: scope.stub().returns(this)
                    }
                };

                scope.stub(app.socket, 'socket').returns(IOStub);
                scope.stub(app.socket, '_FactoryChannel');
                app.socket._FactoryChannel.withArgs(channel1Stub.name).returns(channel1Stub);
                app.socket._FactoryChannel.withArgs(channel2Stub.name).returns(channel2Stub);
            });

            it('returns empty array when no channels were joined', function() {
                var actual = app.socket._currentChannels();
                expect(actual).toEqual([]);
            });
            it('returns array of all joined channels', function() {
                app.socket.channel(channel1Stub.name);
                app.socket.channel(channel2Stub.name);
                var actual = app.socket._currentChannels();
                expect(actual).toEqual([channel1Stub.name, channel2Stub.name]);
            });
            it('ignores empty rooms', function() {
                app.socket.channel(channel1Stub.name);
                app.socket.channel(channel2Stub.name);
                channel1Stub.isEmpty.returns(true);
                var actual = app.socket._currentChannels();
                expect(actual).toEqual([channel2Stub.name]);
            });
        });
        describe('channel', function() {
            var channelStub;

            beforeEach(function() {
                channelStub = {
                    name: Math.random() + '',
                    isEmpty: scope.stub().returns(false),
                    systemEvents: {
                        on: scope.stub().returns(this)
                    }
                };

                scope.stub(app.socket, '_FactoryChannel').returns(channelStub);
            });

            it('uses factory for new channels', function() {
                var actual = app.socket.channel(channelStub.name);
                expect(actual).toBe(channelStub);
                sinon.assert.calledOnce(app.socket._FactoryChannel);
                sinon.assert.calledOn(app.socket._FactoryChannel, app.socket);
                sinon.assert.calledWith(app.socket._FactoryChannel, channelStub.name);
            });
            it('subscribes on leave event of channel with _destroyChannel method', function() {
                app.socket.channel(channelStub.name);
                sinon.assert.called(channelStub.systemEvents.on);
                sinon.assert.calledOn(channelStub.systemEvents.on, channelStub.systemEvents);
                sinon.assert.calledWith(channelStub.systemEvents.on, 'leave', app.socket._destroyChannel, app.socket);
            });
            it('subscribes on leave event of channel with _leaveChannel method', function () {
                app.socket.channel(channelStub.name);
                sinon.assert.called(channelStub.systemEvents.on);
                sinon.assert.calledOn(channelStub.systemEvents.on, channelStub.systemEvents);
                sinon.assert.calledWith(channelStub.systemEvents.on, 'leave', app.socket._leaveChannel, app.socket);
            });
            it('subscribes on join event of channel with _joinChannel method', function () {
                app.socket.channel(channelStub.name);
                sinon.assert.called(channelStub.systemEvents.on);
                sinon.assert.calledOn(channelStub.systemEvents.on, channelStub.systemEvents);
                sinon.assert.calledWith(channelStub.systemEvents.on, 'join', app.socket._joinChannel, app.socket);
            });
            it('returns already registered channel without usage of factory', function() {
                app.socket.channel(channelStub.name);
                app.socket._FactoryChannel.reset();
                app.socket.channel(channelStub.name);
                sinon.assert.notCalled(app.socket._FactoryChannel);
            });
        });
        describe('_destroyChannel', function() {
            var IOStub, channel1Stub, channel2Stub;

            beforeEach(function() {
                IOStub = {
                    on: scope.stub().returnsThis()
                };

                channel1Stub = {
                    name: Math.random() + '',
                    isEmpty: scope.stub().returns(false),
                    systemEvents: {
                        on: scope.stub().returns(this)
                    }
                };

                channel2Stub = {
                    name: Math.random() + '',
                    isEmpty: scope.stub().returns(false),
                    systemEvents: {
                        on: scope.stub().returns(this)
                    }
                };

                scope.stub(app.socket, 'socket').returns(IOStub);
                scope.stub(app.socket, '_FactoryChannel');
                app.socket._FactoryChannel.withArgs(channel1Stub.name).returns(channel1Stub);
                app.socket._FactoryChannel.withArgs(channel2Stub.name).returns(channel2Stub);
            });

            it('deletes channel object from socket', function() {
                app.socket.channel(channel1Stub.name);
                app.socket.channel(channel2Stub.name);
                var actual = app.socket._currentChannels();
                expect(actual).toEqual([channel1Stub.name, channel2Stub.name]);
                app.socket._destroyChannel(channel2Stub.name);
                actual = app.socket._currentChannels();
                expect(actual).toEqual([channel1Stub.name]);
            });
        });

        describe('join/leave Channel', function () {
            var channelName = 'SomeChannel' + Math.random();
            beforeEach(function () {
                scope.stub(app.socket, '_forward');
            });

            it('_joinChannel', function () {

                app.socket._joinChannel(channelName);

                sinon.assert.calledOnce(app.socket._forward);
                sinon.assert.calledOn(app.socket._forward, app.socket);
                sinon.assert.calledWith(app.socket._forward, 'join', channelName);
            });

            it('_leaveChannel', function () {

                app.socket._leaveChannel(channelName);

                sinon.assert.calledOnce(app.socket._forward);
                sinon.assert.calledOn(app.socket._forward, app.socket);
                sinon.assert.calledWith(app.socket._forward, 'leave', channelName);
            });
        });

        describe('forwarding message', function () {
            var channelName = 'SomeChannel' + Math.random(), action = 'someAction' + Math.random(),
                socket = {emit: function () { }}, spyForward;
            beforeEach(function () {
                spyForward = sinon.spy(app.socket, '_forward');
                scope.stub(app.socket, 'socket');
                scope.stub(socket, 'emit');
            });
            it('test if socket not initialized', function () {
                app.socket.socket.returns(null);

                try {
                    app.socket._forward(action, channelName);
                } catch (e) {
                }

                expect(spyForward.exceptions[0]).toBeUndefined('Not triggered error on execution');
                sinon.assert.notCalled(socket.emit);
            });
            it('test if socket initialized', function () {
                app.socket.socket.returns(socket);

                app.socket._forward(action, channelName);

                sinon.assert.calledOnce(socket.emit);
                sinon.assert.calledOn(socket.emit, socket);
                sinon.assert.calledWith(socket.emit, action, channelName);
            });
        });
    });
    describe('Channel', function() {
        var channel, channelName, IOStub;

        beforeEach(function() {
            IOStub = {
                'emit': scope.stub().returnsThis()
            };

            scope.stub(app.socket, 'socket').returns(IOStub);

            channelName = Math.random() + '';
            channel = app.socket.channel(channelName);
        });
        afterEach(function() {
            app.socket._destroyChannel(channelName);
        });

        describe('name', function() {
            it('returns correct name of channel', function() {
                var actual = channel.name();
                expect(actual).toBe(channelName);
            });
        });
        describe('on', function() {
            beforeEach(function() {
                scope.stub(channel, 'isEmpty');
                scope.stub(channel, 'name');
                scope.stub(channel.systemEvents, 'trigger');
            });

            it('emit event join if channel was empty', function () {
                var name = 'someChannelName' + Math.random();
                channel.isEmpty.returns(true);
                channel.name.returns(name);

                channel.on('test');

                sinon.assert.calledOnce(channel.systemEvents.trigger);
                sinon.assert.calledOn(channel.systemEvents.trigger, channel.systemEvents);
                sinon.assert.calledWith(channel.systemEvents.trigger, 'join', name);
            });
            it('does not emit event join if channel is not empty', function () {
                channel.isEmpty.returns(false);
                channel.on('test');
                sinon.assert.notCalled(channel.systemEvents.trigger);
            });
        });
        describe('off', function() {
            beforeEach(function() {
                scope.stub(channel, 'isEmpty');
                scope.stub(channel, 'name');
                scope.stub(channel.systemEvents, 'trigger');
            });

            it('emit event leave if channel was empty', function () {
                var name = 'someChannelName' + Math.random();
                channel.isEmpty.returns(true);
                channel.name.returns(name);

                channel.off('test');

                sinon.assert.calledOnce(channel.systemEvents.trigger);
                sinon.assert.calledOn(channel.systemEvents.trigger, channel.systemEvents);
                sinon.assert.calledWith(channel.systemEvents.trigger, 'leave', name);
            });

            it('does not emit event leave if channel is not empty', function () {
                channel.isEmpty.returns(false);

                channel.off('test');

                sinon.assert.notCalled(channel.systemEvents.trigger);
            });
        });

        describe('isEmpty', function() {
            var callback = function() {
                return Math.random;
            };

            it('returns true on empty channel by default', function() {
                expect(channel.isEmpty()).toBeTruthy();
            });
            it('returns false when channel has subscribers', function() {
                channel.on('test', callback);
                expect(channel.isEmpty()).toBeFalsy();
            });
            it('returns true when last subscriber left channel', function() {
                channel.on('test', callback);
                expect(channel.isEmpty()).toBeFalsy();
                channel.off('test', callback);
                expect(channel.isEmpty()).toBeTruthy();
            });
        });
    });
});
