describe('Base.View.Saved-Reports-Chart', function() {
    var view, app, sandbox, context, meta;
    beforeEach(function() {
        sandbox = sinon.sandbox.create();

        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        meta = {
            config: false
        }

        view = SugarTest.createView('base', '', 'saved-reports-chart', meta, context, false, null, true);
        view.settings = new Backbone.Model({
            saved_report_id: 'a'
        })
    });

    afterEach(function() {
        sandbox.restore();
        app = undefined;
        view = undefined;
    });

    describe('initDashlet()', function() {
        var getAllReportsStub, getReportByIdStub;
        beforeEach(function() {
            getAllReportsStub = sinon.stub(view, 'getAllSavedReports', function() {});
            getReportByIdStub = sinon.stub(view, 'getSavedReportById', function() {});
        });

        afterEach(function() {
            getAllReportsStub.restore();
            getReportByIdStub.restore();
        });

        it('should call getAllSavedReports() when in config', function() {
            view.meta.config = true;
            view.dashletConfig = {};
            view.dashletConfig.dashlet_config_panels = {};
            view.initDashlet({});
            expect(getAllReportsStub).toHaveBeenCalled();
        });

        it('should call getSavedReportById() when not in config', function() {
            view.initDashlet({});
            expect(getReportByIdStub).toHaveBeenCalled();
        });
    });

    describe('bindDataChange()', function() {
        var settingsStub;
        beforeEach(function() {
            settingsStub = sinon.stub(view.settings, 'on', function() {});
        });

        afterEach(function() {
            settingsStub.restore();
        });

        it('should add change event listener on settings only when in config', function() {
            view.meta.config = true;
            view.bindDataChange();
            expect(settingsStub).toHaveBeenCalled();
        });

        it('should call getSavedReportById() when not in config', function() {
            view.meta.config = false;
            view.bindDataChange();
            expect(settingsStub).not.toHaveBeenCalled();
        });
    });

    describe('parseAllSavedReports()', function() {
        var opts;
        beforeEach(function() {
            opts = [
                { id: 'a', name: 'A' },
                { id: 'b', name: 'B' },
                { id: 'c', name: 'C' }
            ];

        });

        afterEach(function() {
            opts = undefined;
        });

        it('should build reportOptions correctly', function() {
            view.parseAllSavedReports(opts);
            expect(view.reportOptions['a']).toEqual('A');
        });
    });
});

