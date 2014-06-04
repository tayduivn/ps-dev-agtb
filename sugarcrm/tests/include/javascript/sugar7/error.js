describe('Sugar7 error handler', function() {

    var app, origLayout;

    beforeEach(function() {
        app = SugarTest.app;
        origLayout = app.controller.layout;
        app.controller.layout = {error: {
            handleValidationError: function() { }
        }};
    });

    afterEach(function() {
        app.controller.layout= origLayout;
    });

    describe('422 Handle validation error', function() {
        it('should show an alert on error', function() {
            var alertStub = sinon.stub(app.alert, "show");

            app.error.handleValidationError({});
            expect(alertStub).toHaveBeenCalled();
            alertStub.restore();
        });

        it('should call the layout error handler if it exists', function() {
            var layoutStub = sinon.stub(app.controller.layout.error, "handleValidationError").returns(null),
                alertStub = sinon.stub(app.alert, "show");
            app.error.handleValidationError({});
            expect(layoutStub).toHaveBeenCalled();
            expect(alertStub).toHaveBeenCalled();
            alertStub.restore();
            layoutStub.restore();
        });

        it('should not show an alert if the layout handler returns false', function() {
            var layoutStub = sinon.stub(app.controller.layout.error, "handleValidationError").returns(false),
                alertStub = sinon.stub(app.alert, "show");
            app.error.handleValidationError({});
            expect(layoutStub).toHaveBeenCalled();
            expect(alertStub).not.toHaveBeenCalled();
            alertStub.restore();
            layoutStub.restore();
        });

        it('should do nothing when passed a bean', function() {
            var alertStub = sinon.stub(app.alert, "show"),
                bean = new SugarTest.app.data.beanModel();
            app.error.handleValidationError(bean);
            expect(alertStub).not.toHaveBeenCalled();
            alertStub.restore();
        });
    });
});