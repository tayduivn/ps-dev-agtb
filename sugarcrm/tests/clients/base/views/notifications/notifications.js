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
                _initReminders = sinon.collection.stub(view, '_initReminders', $.noop()),
                startPulling = sinon.collection.stub(view, 'startPulling', $.noop());

            view._bootstrap();

            expect(_initOptions).toHaveBeenCalledOnce();
            expect(_initCollection).toHaveBeenCalledOnce();
            expect(_initReminders).toHaveBeenCalledOnce();
            expect(startPulling).toHaveBeenCalledOnce();
        });

        it('should initialize options with default values', function () {
            view._initOptions();

            expect(view.delay / 60 / 1000).toBe(view._defaultOptions.delay);
            expect(view.limit).toBe(view._defaultOptions.limit);
            expect(view.severityCss).toBe(view._defaultOptions.severity_css);
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
                fields: ['date_entered', 'id', 'name', 'severity']
            });
        });
    });

    describe('Initialization with metadata overridden values', function () {
        var app, view, customOptions = {
            delay: 10,
            limit: 8,
            severity_css: {
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
            expect(view.severityCss).toEqual(customOptions.severity_css);
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
                fields: ['date_entered', 'id', 'name', 'severity']
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
            view.disposed = false;
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
            view.disposed = false;
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
            var pull, _pullReminders, setInterval;

            pull = sinon.collection.stub(view, 'pull', $.noop());
            _pullReminders = sinon.collection.stub(view, '_pullReminders', $.noop());
            setInterval = sinon.collection.stub(window, 'setInterval', $.noop());

            view.startPulling().startPulling();

            expect(pull).toHaveBeenCalledOnce();
            expect(_pullReminders).toHaveBeenCalledOnce();
            expect(setInterval).toHaveBeenCalledOnce();
        });

        it('should clear interval on stop pulling', function () {
            var pull, _pullReminders, setInterval, clearInterval, intervalId = 1;

            pull = sinon.collection.stub(view, 'pull', $.noop());
            _pullReminders = sinon.collection.stub(view, '_pullReminders', $.noop());
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
            var app = SugarTest.app, isAuthenticated, pull, _pullReminders,
                setInterval, stopPulling;

            pull = sinon.collection.stub(view, 'pull', $.noop());
            _pullReminders = sinon.collection.stub(view, '_pullReminders', $.noop());
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
        it('should retrieve severity as a label for non-existent severity', function () {
            var appList, label, severity;

            appList = sinon.collection.stub(app.lang, 'getAppListStrings', function () {
                return {};
            });

            severity = 'non-existent';
            label = view.getSeverityLabel(severity);

            expect(appList).toHaveBeenCalledOnce();
            expect(label).toBe(severity);
        });

        // FIXME: refactor this when data providers support is enabled
        it('should retrieve matching label for existent severity', function () {
            var appList, label, severity;

            appList = sinon.collection.stub(app.lang, 'getAppListStrings', function () {
                return {
                    alert: 'Alert'
                };
            });

            severity = 'alert';
            label = view.getSeverityLabel(severity);

            expect(appList).toHaveBeenCalledOnce();
            expect(label).toBe('Alert');
        });

        // FIXME: refactor this when data providers support is enabled
        it('should retrieve an empty string for non-existent severity', function () {
            view.severityCss = {};

            var css = view.getSeverityCss('non-existent');

            expect(css).toBe('');
        });

        // FIXME: refactor this when data providers support is enabled
        it('should retrieve a css class for existent severity', function () {
            view.severityCss = {
                alert: 'label-important'
            };

            var css = view.getSeverityCss('alert');

            expect(css).toBe('label-important');
        });
    });

    describe('Reminders', function() {
        var app, view;

        beforeEach(function() {
            var meta = {
                remindersFilterDef: {
                    reminder_time: { $gte: 0},
                    status: { $equals: 'Planned'}
                },
                remindersLimit: 100
            };

            app = SugarTest.app;
            view = SugarTest.createView('base', moduleName, viewName, meta);
        });

        afterEach(function() {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should initialize collections for Meetings and Calls', function() {

            sinon.collection.stub(app.data, 'createBeanCollection', function() {
                return {
                    options: {},
                    off: function() {
                    }
                };
            });
            sinon.collection.stub(app.lang, 'getAppListStrings', function() {
                return {
                    '60': '1 minute prior',
                    '300': '5 minutes prior',
                    '600': '10 minutes prior',
                    '900': '15 minutes prior',
                    '1800': '30 minutes prior',
                    '3600': '1 hour prior',
                    '7200': '2 hours prior',
                    '10800': '3 hours prior',
                    '18000': '5 hours prior',
                    '86400': '1 day prior'
                };
            });

            view.delay = 300000; // 5 minutes for each pull;
            view._initReminders();

            _.each(['Calls', 'Meetings'], function(module) {
                expect(view._alertsCollections[module].options).toEqual({
                    limit: 100,
                    myItems: true,
                    fields: ['date_start', 'id', 'name', 'reminder_time', 'location']
                });
            });

            expect(view.reminderMaxTime).toBe(86700); // 1 day + 5 minutes
        });

        it('should not pull reminders if disposed', function() {
            // not calling dispose() directly due to it setting inherently the
            // collection to null
            view.disposed = true;
            view._pullReminders();

            expect(view._parseReminders).not.toHaveBeenCalled();
            view.disposed = false;
        });

        it('should not pull reminders if disposed after fetch', function() {

            var now = new Date('2013-09-04T23:45:56+02:00'),
                clock = sinon.useFakeTimers(now.getTime(), 'Date'),
                startDate = now.toISOString(),
                endDate = new Date('2013-09-05T23:45:56+02:00').toISOString();

            _.each(['Calls', 'Meetings'], function(module) {
                view._alertsCollections = view._alertsCollections || {};
                view._alertsCollections[module] = {
                    off: $.noop,
                    fetch: $.noop
                };

                sinon.collection.stub(view._alertsCollections[module], 'fetch', function(o) {
                    // not calling dispose() directly due to it setting inherently the
                    // collection to null
                    view.disposed = true;
                    o.success();
                });
            });

            view.reminderMaxTime = 86400; // -1 day
            view._pullReminders();

            _.each(['Calls', 'Meetings'], function(module) {
                expect(view._alertsCollections[module].filterDef['date_start']).toEqual({
                    '$dateBetween': [startDate, endDate]
                });
                expect(view._alertsCollections[module].fetch).toHaveBeenCalledOnce();
            });
            expect(view._parseReminders).not.toHaveBeenCalled();
            view.disposed = false;
        });

        describe('Parse reminders', function() {

            var reminderModule = 'Meetings';

            beforeEach(function() {

                var meta = {
                    fields: [],
                    views: [],
                    layouts: []
                };
                app.data.declareModel(reminderModule, meta);
                SugarTest.testMetadata.init();
                SugarTest.loadHandlebarsTemplate('notifications', 'view', 'base', 'notifications-alert');
                SugarTest.testMetadata.set();

            });

            afterEach(function() {
                app.data.reset(reminderModule);
                SugarTest.testMetadata.dispose();
                delete Handlebars.templates;
            });


            it('should parse reminders if new ones are sent', function() {

                var parseReminderStub = sinon.collection.stub(view, '_parseReminder', $.noop),
                    clearRemindersStub = sinon.collection.stub(view, '_clearReminders', $.noop);

                var data = new app.data.createBeanCollection(reminderModule, [
                    {
                        'id': '105b0b4a-1337-e0db-b448-522784b92270',
                        'name': 'Discuss pricing',
                        'date_modified': '2013-09-05T00:59:00+02:00',
                        'description': 'Meeting to discuss project plan and hash out the details of implementation',
                        'date_start': '2013-09-05T03:45:00+02:00'
                    }
                ]);

                view._intervals = {};
                view._intervals[reminderModule] = {};

                view._parseReminders(data);

                expect(parseReminderStub).toHaveBeenCalled();
                expect(clearRemindersStub).not.toHaveBeenCalled();
            });

            it('should clear old reminders if they no longer exist', function() {

                var parseReminderStub = sinon.collection.stub(view, '_parseReminder', $.noop),
                    clearTimeoutStub = sinon.collection.stub(window, 'clearTimeout', $.noop);

                var data = new app.data.createBeanCollection(reminderModule, [
                    {
                        'id': '105b0b4a-1337-e0db-b448-522784b92270',
                        'name': 'Discuss pricing',
                        'date_modified': '2013-09-05T00:59:00+02:00',
                        'description': 'Meeting to discuss project plan and hash out the details of implementation',
                        'date_start': '2013-09-05T03:45:00+02:00'
                    },
                    {
                        'id': '21cd1096-0241-45f6-e3f6-522aa353f676',
                        'name': 'Discuss pricing',
                        'date_modified': '2013-09-05T00:59:00+02:00',
                        'description': 'Meeting to discuss project plan and hash out the details of implementation',
                        'date_start': '2013-09-05T03:45:00+02:00'
                    }
                ]);

                view._intervals = {};
                view._intervals[reminderModule] = {
                    idA: {
                        timer: 1
                    },
                    '105b0b4a-1337-e0db-b448-522784b92270': {
                        timer: 2
                    },
                    '21cd1096-0241-45f6-e3f6-522aa353f676': {
                        timer: 3
                    },
                    idB: {
                        timer: 4
                    },
                    idC: {
                        timer: 5
                    }
                };

                view._parseReminders(data);

                expect(view._intervals[reminderModule]).toEqual({
                    '105b0b4a-1337-e0db-b448-522784b92270': {
                        timer: 2
                    },
                    '21cd1096-0241-45f6-e3f6-522aa353f676': {
                        timer: 3
                    }
                });
                expect(parseReminderStub).toHaveBeenCalled();
                expect(clearTimeoutStub).toHaveBeenCalled();
            });

            it('should define reminder if new', function() {

                var now = new Date('2013-09-04T23:45:56+02:00'),
                    clock = sinon.useFakeTimers(now.getTime(), 'Date'),
                    setTimeoutStub = sinon.collection.stub(window, 'setTimeout', function() {
                        return 3;
                    });

                var model = new app.data.createBean(reminderModule, {
                    'id': '105b0b4a-1337-e0db-b448-522784b92270',
                    'name': 'Discuss pricing',
                    'date_modified': '2013-09-05T00:59:00+02:00',
                    'description': 'Meeting to discuss project plan and hash out the details of implementation',
                    'date_start': '2013-09-05T03:45:00+02:00',
                    'reminder_time': '1800'
                });

                view._intervals = {};
                view._intervals[reminderModule] = {
                    idB: {
                        timer: 2
                    }
                };

                view._parseReminder(model);

                expect(view._intervals[reminderModule]).toEqual({
                    '105b0b4a-1337-e0db-b448-522784b92270': {
                        timer: 3,
                        prevAttr: {
                            'date_start': '2013-09-05T03:45:00+02:00',
                            'reminder_time': '1800'
                        }
                    },
                    idB: {
                        timer: 2
                    }
                });

                //var interval = model.get('date_start') - now.getTime(),
                //    delay = interval - model.get('reminder_time') * 1000; // in milliseconds
                // 12544000 = new Date('2013-09-05T03:45:00+02:00').getTime()
                //            - new Date('2013-09-04T23:45:56+02:00').getTime() - 1800 * 1000
                expect(setTimeoutStub.args[0][1]).toEqual(12544000);

                clock.restore();
            });

            it('should redefine reminder if reminder time is updated', function() {

                var now = new Date('2013-09-04T23:45:56+02:00'),
                    clock = sinon.useFakeTimers(now.getTime(), 'Date'),
                    clearTimeoutStub = sinon.collection.stub(window, 'clearTimeout', $.noop),
                    setTimeoutStub = sinon.collection.stub(window, 'setTimeout', function() {
                        return 3;
                    });

                var model = new app.data.createBean(reminderModule, {
                    'id': '105b0b4a-1337-e0db-b448-522784b92270',
                    'name': 'Discuss pricing',
                    'date_modified': '2013-09-05T00:59:00+02:00',
                    'description': 'Meeting to discuss project plan and hash out the details of implementation',
                    'date_start': '2013-09-05T03:45:00+02:00',
                    'reminder_time': '1800'
                });

                view._intervals = {};
                view._intervals[reminderModule] = {
                    '105b0b4a-1337-e0db-b448-522784b92270': {
                        timer: 1,
                        prevAttr: {
                            'date_start': '2013-09-05T03:45:00+02:00',
                            'reminder_time': '2000'
                        }
                    },
                    idB: {
                        timer: 2
                    }
                };

                view._parseReminder(model);

                expect(view._intervals[reminderModule]).toEqual({
                    '105b0b4a-1337-e0db-b448-522784b92270': {
                        timer: 3,
                        prevAttr: {
                            'date_start': '2013-09-05T03:45:00+02:00',
                            'reminder_time': '1800'
                        }
                    },
                    idB: {
                        timer: 2
                    }
                });
                expect(clearTimeoutStub).toHaveBeenCalled();
                expect(setTimeoutStub).toHaveBeenCalled();

                clock.restore();
            });

            it('should redefine reminder if date start is updated', function() {

                var now = new Date('2013-09-04T23:45:56+02:00'),
                    clock = sinon.useFakeTimers(now.getTime(), 'Date'),
                    clearTimeoutStub = sinon.collection.stub(window, 'clearTimeout', $.noop),
                    setTimeoutStub = sinon.collection.stub(window, 'setTimeout', function() {
                        return 3;
                    });

                var model = new app.data.createBean(reminderModule, {
                    'id': '105b0b4a-1337-e0db-b448-522784b92270',
                    'name': 'Discuss pricing',
                    'date_modified': '2013-09-05T00:59:00+02:00',
                    'description': 'Meeting to discuss project plan and hash out the details of implementation',
                    'date_start': '2013-09-05T03:30:00+02:00',
                    'reminder_time': '1800'
                });

                view._intervals = {};
                view._intervals[reminderModule] = {
                    '105b0b4a-1337-e0db-b448-522784b92270': {
                        timer: 1,
                        prevAttr: {
                            'date_start': '2013-09-05T03:45:00+02:00',
                            'reminder_time': '1800'
                        }
                    },
                    idB: {
                        timer: 2
                    }
                };

                view._parseReminder(model);

                expect(view._intervals[reminderModule]).toEqual({
                    '105b0b4a-1337-e0db-b448-522784b92270': {
                        timer: 3,
                        prevAttr: {
                            'date_start': '2013-09-05T03:30:00+02:00',
                            'reminder_time': '1800'
                        }
                    },
                    idB: {
                        timer: 2
                    }
                });
                expect(clearTimeoutStub).toHaveBeenCalled();
                expect(setTimeoutStub).toHaveBeenCalled();

                clock.restore();
            });

            it('should clear interval after triggering alert', function() {

                var clock = sinon.useFakeTimers(),
                    model = new app.data.createBean(reminderModule, {
                        'id': '105b0b4a-1337-e0db-b448-522784b92270',
                        'name': 'Discuss pricing',
                        'date_modified': '2013-09-05T00:59:00+02:00',
                        'description': 'Meeting to discuss project plan and hash out the details of implementation',
                        'date_start': '2013-09-05T03:45:00+02:00'
                    });

                sinon.collection.stub(window, 'clearTimeout', $.noop);

                view._intervals = {};
                view._intervals[reminderModule] = {
                    idA: {
                        timer: 1
                    },
                    '105b0b4a-1337-e0db-b448-522784b92270': {
                        timer: 2
                    },
                    idC: {
                        timer: 3
                    }
                };

                view._showReminderAlert(model);

                expect(view._intervals[reminderModule]).toEqual({
                    idA: {
                        timer: 1
                    },
                    idC: {
                        timer: 3
                    }
                });
                clock.restore();
            });

            it('should clear reminders and alerts if disposed', function() {

                var clearTimeoutStub = sinon.collection.stub(window, 'clearTimeout', $.noop);

                view._intervals = {};
                view._intervals[reminderModule] = {
                    idA: {
                        timer: 1
                    },
                    idB: {
                        timer: 2
                    },
                    idC: {
                        timer: 3
                    }
                };
                view._alertsCollections = {};
                view._alertsCollections[reminderModule] = {
                    off: $.noop
                };
                var offStub = sinon.collection.stub(view._alertsCollections[reminderModule], 'off', $.noop);

                view.dispose();

                expect(view._alertsCollections).toEqual({});
                expect(view._intervals[reminderModule]).toEqual({});
                expect(offStub).toHaveBeenCalled();
                expect(clearTimeoutStub).toHaveBeenCalled();
            });
        });
    });
});
