describe('Sugar7 sync alerts', function() {
    var moduleName = 'Cases',
        app,
        context,
        model,
        alertStubs = {};


    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
        alertStubs.show = sinon.stub(app.alert, 'show');
        alertStubs.dismiss = sinon.stub(app.alert, 'dismiss');

        context = app.context.getContext();
        model = app.data.createBean(moduleName);

    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        alertStubs.show.restore();
        alertStubs.dismiss.restore();
    });

    describe('overriding context', function() {
        it('should attach _hideAlertsOn to the model', function() {
            context.set('modelId', 1);
            context.set('hideAlertsOn', ['read', 'delete']);
            context.prepare();
            context.loadData();
            expect(context.get('model')._hideAlertsOn).toBeDefined();
            expect(context.get('model')._hideAlertsOn).toEqual(['read', 'delete']);
        });

        it('should attach _hideAlertsOn to the collection', function() {
            context.set('hideAlertsOn', 'read');
            context.prepare();
            context.loadData();
            expect(context.get('collection')._hideAlertsOn).toBeDefined();
            expect(context.get('collection')._hideAlertsOn).toEqual(['read']);
        });

        it('should not attach _hideAlertsOn to the model', function() {
            context.set('modelId', 1);
            context.prepare();
            context.loadData();
            expect(context.get('model')._hideAlertsOn).toBeUndefined();
        });

        it('should not attach _hideAlertsOn to the collection', function() {
            context.prepare();
            context.loadData();
            expect(context.get('collection')._hideAlertsOn).toBeUndefined();
        });
    });

    describe('process alerts', function() {
        it('should display an alert on app:sync', function() {
            app.events.trigger('app:sync', 'read', model, {});
            expect(alertStubs.show).toHaveBeenCalled();
        });

        it('should hide the alert on app:sync:complete', function() {
            //app.router is not defined so it will throw an error
            var routerFake = app.router;
            app.router = {
                start: sinon.stub()
            };
            app.events.trigger('app:sync:complete', 'read', model, {});
            expect(alertStubs.dismiss).toHaveBeenCalled();
            //restore what was defined as app.router
            app.router = routerFake;
        });

        it('should hide the alert on app:sync:error', function() {
            app.events.trigger('app:sync:error', 'read', model, {});
            expect(alertStubs.dismiss).toHaveBeenCalled();
        });

        it('should display an alert on data:sync:start', function() {
            app.events.trigger('data:sync:start', 'read', model, {});
            expect(alertStubs.show).toHaveBeenCalled();
        });

        it('should hide the alert on data:sync:end', function() {
            app.events.trigger('data:sync:end', 'read', model, {});
            expect(alertStubs.dismiss).toHaveBeenCalled();
        });

        it('should dismiss the alert only on the last app:sync:end', function() {
            app.events.trigger('data:sync:start', 'read', model, {});
            app.events.trigger('data:sync:start', 'read', model, {});
            app.events.trigger('data:sync:start', 'read', model, {});
            app.events.trigger('data:sync:end', 'read', model, {});
            expect(alertStubs.dismiss).not.toHaveBeenCalled();
            app.events.trigger('data:sync:end', 'read', model, {});
            expect(alertStubs.dismiss).not.toHaveBeenCalled();
            app.events.trigger('data:sync:end', 'read', model, {});
            expect(alertStubs.dismiss).toHaveBeenCalled();
        });

        it('should not display an alert if method is in _hideAlertsOn', function() {
            model._hideAlertsOn = ['read'];
            app.events.trigger('data:sync:start', 'read', model, {});
            expect(alertStubs.show).not.toHaveBeenCalled();
        });


        it('should not display an alert if options.alerts.process = false', function() {
            var options = {
                alerts: {
                    process: false
                }
            };
            app.events.trigger('data:sync:start', 'read', model, options);
            expect(alertStubs.show).not.toHaveBeenCalled();
        });

        it('should not display an alert if options.alerts = false', function() {
            var options = {
                alerts: false
            };
            app.events.trigger('data:sync:start', 'read', model, options);
            expect(alertStubs.show).not.toHaveBeenCalled();
        });

        it('should allow you to override alert options', function() {
            app.events.trigger('data:sync:start', 'read', model, {alerts: { process: { title: 'Loading the test'} }});
            expect(alertStubs.show).toHaveBeenCalled();
            expect(alertStubs.show.args[0][0]).toBe('data:sync:process');
            expect(alertStubs.show.args[0][1].title).toEqual('Loading the test');
        });
    });

    describe('success alerts', function() {
        it('should display an alert on app:sync:end', function() {
            app.events.trigger('data:sync:end', 'create', model, {});
            expect(alertStubs.show).toHaveBeenCalled();
        });

        it('should not display an alert for read method', function() {
            app.events.trigger('data:sync:end', 'read', model, {});
            expect(alertStubs.show).not.toHaveBeenCalled();
        });

        it('should not display an alert if method is in _hideAlertsOn', function() {
            model._hideAlertsOn = ['create'];
            app.events.trigger('data:sync:end', 'create', model, {});
            expect(alertStubs.show).not.toHaveBeenCalled();
        });

        it('should not display an alert if options.alerts.success = false', function() {
            var options = {
                alerts: {
                    success: false
                }
            };
            app.events.trigger('data:sync:end', 'create', model, options);
            expect(alertStubs.show).not.toHaveBeenCalled();
        });

        it('should not display an alert if options.alerts = false', function() {
            var options = {
                alerts: false
            };
            app.events.trigger('data:sync:end', 'create', model, options);
        });

        it('should allow you to override alert options', function() {
            var options = {
                alerts: {
                    success: {
                        title: 'Success', messages: 'Tests are green'
                    }
                }
            };
            app.events.trigger('data:sync:end', 'create', model, options);
            expect(alertStubs.show).toHaveBeenCalled();
            expect(alertStubs.show.args[0][0]).toBe('data:sync:success');
            expect(alertStubs.show.args[0][1].title).toEqual('Success');
            expect(alertStubs.show.args[0][1].messages).toEqual('Tests are green');
        });
    });
});
