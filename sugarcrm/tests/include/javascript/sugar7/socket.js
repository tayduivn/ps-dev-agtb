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
                initSocketConstructor();
            });

            it('binds _initConfig on app:init event', function() {
                sinon.assert.calledOnce(app.events.on);
                sinon.assert.calledOnce(app.events.on, app.events);
                sinon.assert.calledWith(app.events.on, 'app:init');

                var callback = app.events.on.getCall(0).args[1];
                var bind = app.events.on.getCall(0).args[2];

                expect(bind).toBe(app.socket);
                callback();

                sinon.assert.calledOnce(app.socket._initConfig);
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
            it('does not call _initClientLibrary in done callback of request when result does not have location', function() {
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
            it('calls _initClientLibrary with response url in done callback of request when result has location', function() {
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

                scope.stub(app.socket, 'authorize');
                scope.stub(app.socket, '_message');
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
