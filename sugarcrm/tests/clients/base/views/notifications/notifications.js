describe('Notifications', function() {
    var moduleName = 'Notifications',
        viewName = 'notifications';

    describe('Initialization with default values', function() {
        var app, view;

        beforeEach(function() {
            view = SugarTest.createView('base', moduleName, viewName);
            app = SugarTest.app;
        });

        afterEach(function() {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        it('should bootstrap', function() {
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

        it('should initialize options with default values', function() {
            view._initOptions();

            expect(view.delay / 60 / 1000).toBe(view._defaultOptions.delay);
            expect(view.limit).toBe(view._defaultOptions.limit);
            expect(view.severityCss).toBe(view._defaultOptions.severity_css);
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
                fields: ['date_entered', 'id', 'name', 'severity']
            });
        });
    });

    describe('Initialization with metadata overridden values', function() {
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

        beforeEach(function() {
            SugarTest.testMetadata.init();
            SugarTest.testMetadata.addViewDefinition(viewName, customOptions, moduleName);
            SugarTest.testMetadata.set();

            view = SugarTest.createView('base', moduleName, viewName);
            app = SugarTest.app;
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
            expect(view.severityCss).toEqual(customOptions.severity_css);
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
                fields: ['date_entered', 'id', 'name', 'severity']
            });
        });
    });

    describe('Pulling mechanism', function() {
        var view;

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

        it('should not pull notifications if opened', function() {
            var isOpened = sinon.collection.stub(view, 'isOpened', function() {
                return true;
            });

            view.pull();

            expect(view.collection.fetch).not.toHaveBeenCalled();
        });

        it('should not pull notifications if opened after fetch', function() {
            var fetch = sinon.collection.stub(view.collection, 'fetch', function(o) {
                var isOpened = sinon.collection.stub(view, 'isOpened', function() {
                    return true;
                });

                o.success();
            });

            view.pull();

            expect(fetch).toHaveBeenCalledOnce();
            expect(view.render).not.toHaveBeenCalled();
        });

        it('should set timeout twice once on multiple start pulling calls', function() {
            var pull = sinon.collection.stub(view, 'pull', $.noop()),
                _pullReminders = sinon.collection.stub(view, '_pullReminders', $.noop()),
                setTimeout = sinon.collection.stub(window, 'setTimeout', $.noop());

            view.startPulling().startPulling();

            expect(pull).toHaveBeenCalledOnce();
            expect(_pullReminders).toHaveBeenCalledOnce();
            expect(setTimeout).toHaveBeenCalledTwice();
        });

        it('should clear intervals on stop pulling', function() {
            var pull = sinon.collection.stub(view, 'pull', $.noop()),
                _pullReminders = sinon.collection.stub(view, '_pullReminders', $.noop()),
                setTimeout = sinon.collection.stub(window, 'setTimeout', function() {
                    return intervalId;
                }),
                clearInterval = sinon.collection.stub(window, 'clearInterval', $.noop()),
                intervalId = 1;

            view.startPulling().stopPulling();

            expect(clearInterval).toHaveBeenCalledTwice();
            expect(view._intervalId).toBeNull();
            expect(view._remindersIntervalId).toBeNull();
        });

        it('should stop pulling on dispose', function() {
            var stopPulling = sinon.collection.stub(view, 'stopPulling', $.noop());

            view.dispose();

            expect(stopPulling).toHaveBeenCalledOnce();
        });

        it('should stop pulling if authentication expires', function() {
            var app = SugarTest.app,
                isAuthenticated = sinon.collection.stub(app.api, 'isAuthenticated', function() {
                    return false;
                }),
                pull = sinon.collection.stub(view, 'pull', $.noop()),
                _pullReminders = sinon.collection.stub(view, '_pullReminders', $.noop()),
                setTimeout = sinon.collection.stub(window, 'setTimeout', function(fn) {
                    fn();
                }),
                stopPulling = sinon.collection.stub(view, 'stopPulling', $.noop());

            view.startPulling();

            expect(pull).toHaveBeenCalledOnce();
            expect(setTimeout).toHaveBeenCalledTwice();
            expect(isAuthenticated).toHaveBeenCalledTwice();
            expect(stopPulling).toHaveBeenCalledTwice();
        });
    });

    describe('Helpers', function() {
        var app, view;

        beforeEach(function() {
            app = SugarTest.app;
            view = SugarTest.createView('base', moduleName, viewName);
        });

        afterEach(function() {
            sinon.collection.restore();
            SugarTest.app.view.reset();
            view.dispose();
            view = null;
        });

        // FIXME: refactor this when data providers support is enabled
        it('should retrieve severity as a label for non-existent severity', function() {
            var appList, label, severity;

            appList = sinon.collection.stub(app.lang, 'getAppListStrings', function() {
                return {};
            });

            severity = 'non-existent';
            label = view.getSeverityLabel(severity);

            expect(appList).toHaveBeenCalledOnce();
            expect(label).toBe(severity);
        });

        // FIXME: refactor this when data providers support is enabled
        it('should retrieve matching label for existent severity', function() {
            var appList, label, severity;

            appList = sinon.collection.stub(app.lang, 'getAppListStrings', function() {
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
        it('should retrieve an empty string for non-existent severity', function() {
            view.severityCss = {};

            var css = view.getSeverityCss('non-existent');

            expect(css).toBe('');
        });

        // FIXME: refactor this when data providers support is enabled
        it('should retrieve a css class for existent severity', function() {
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
                    fields: ['date_start', 'id', 'name', 'reminder_time', 'location']
                });
            });

            expect(view.reminderMaxTime).toBe(86700); // 1 day + 5 minutes
        });


        describe('Check reminders', function() {

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

            it('Shouldn\'t check reminders if authentication expires', function() {
                var isAuthenticated = sinon.collection.stub(app.api, 'isAuthenticated', function() {
                        return false;
                    }),
                    setTimeout = sinon.collection.stub(window, 'setTimeout', $.noop()),
                    stopPulling = sinon.collection.stub(view, 'stopPulling', $.noop());

                view.checkReminders();

                expect(setTimeout).not.toHaveBeenCalled();
                expect(isAuthenticated).toHaveBeenCalledOnce();
                expect(stopPulling).toHaveBeenCalledOnce();
            });

            it('Should show reminder if need', function() {

                var now = new Date('2013-09-04T22:45:56+02:00'),
                    dateStart = new Date('2013-09-04T23:16:16+02:00'),
                    clock = sinon.useFakeTimers(now.getTime(), 'Date'),
                    setTimeout = sinon.collection.stub(window, 'setTimeout', $.noop()),
                    _showReminderAlert = sinon.collection.stub(view, '_showReminderAlert'),
                    isAuthenticated = sinon.collection.stub(app.api, 'isAuthenticated', function() {
                        return true;
                    }),
                    model = new app.data.createBean(reminderModule, {
                        'id': '105b0b4a-1337-e0db-b448-522784b92270',
                        'name': 'Discuss pricing',
                        'date_modified': '2013-09-05T00:59:00+02:00',
                        'description': 'Meeting',
                        'date_start': dateStart.toISOString(),
                        'reminder_time': '1800'
                    });

                view._initReminders();
                view._alertsCollections[reminderModule].add(model);
                view.dateStarted = now.getTime();
                view.checkReminders();

                expect(_showReminderAlert).toHaveBeenCalledWith(model);

                clock.restore();
            });
        });
    });
});
