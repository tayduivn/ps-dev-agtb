describe("Alert View", function() {
    var moduleName = 'Cases',
        app,
        sinonSandbox, view;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'alert');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();

        view = SugarTest.createView('base', moduleName, 'alert');
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        sinonSandbox.restore();
    });

    describe('getTranslatedLabels()', function() {
        it("Should return a translated string when a string is given", function() {
            sinonSandbox.stub(app.metadata, 'getStrings', function() {
                return {
                    FOO: 'bar'
                }
            });

            expect(view.getTranslatedLabels('FOO')).toBe('bar');
        });

        it("Should return a translated array of strings when an array is given", function() {
            sinonSandbox.stub(app.metadata, 'getStrings', function() {
                return {
                    FOO: 'bar'
                }
            });
            var result = view.getTranslatedLabels(['FOO','FOO','FOO']);

            expect(_.isArray(result)).toBe(true);
            _.each(result , function(text) {
                expect(text).toBe('bar');
            });
        });
    });

    describe('getAlertTemplate()', function() {
        it("Should return the correct class when success level is given", function() {
            sinonSandbox.stub(app.metadata, 'getStrings', function() {
                return {
                    FOO: 'foo',
                    BAR: 'bar'
                }
            });

            var dataProvider = {};
            dataProvider[view.LEVEL.SUCCESS] = 'alert-success';
            dataProvider[view.LEVEL.PROCESS] = 'alert-process';
            dataProvider[view.LEVEL.WARNING] = 'alert-warning';
            dataProvider[view.LEVEL.INFO] = 'alert-info';
            dataProvider[view.LEVEL.ERROR] = 'alert-danger';
            dataProvider[view.LEVEL.CONFIRMATION] = 'alert-warning';

            _.each(dataProvider, function(className, level) {
                var result = view.getAlertTemplate(level, 'BAR', 'FOO');
                expect($('<div></div>').append(result).find('.alert').hasClass(className)).toBe(true);
            });
        });

        it("Should return the default title if title is not given", function() {
            sinonSandbox.stub(app.metadata, 'getStrings', function() {
                return {
                    LBL_ALERT_TITLE_SUCCESS: 'foo bar'
                }
            });

            var result = view.getAlertTemplate(view.LEVEL.SUCCESS, 'BAR');
            expect(result.indexOf('foo bar')).not.toBe(-1);
        });
    });
});
