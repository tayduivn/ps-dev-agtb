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
        app.controller.layout = origLayout;
        sinon.collection.restore();
    });

    describe('422 Handle validation error', function() {
        it('should show an alert on error', function() {
            var alertStub = sinon.collection.stub(app.alert, "show");

            app.error.handleValidationError({});
            expect(alertStub).toHaveBeenCalled();
        });

        it('should call the layout error handler if it exists', function() {
            var layoutStub = sinon.collection.stub(app.controller.layout.error, "handleValidationError").returns(null),
                alertStub = sinon.collection.stub(app.alert, "show");
            app.error.handleValidationError({});
            expect(layoutStub).toHaveBeenCalled();
            expect(alertStub).toHaveBeenCalled();
            alertStub.restore();
            layoutStub.restore();
        });

        it('should not show an alert if the layout handler returns false', function() {
            var layoutStub = sinon.collection.stub(app.controller.layout.error, "handleValidationError").returns(false),
                alertStub = sinon.collection.stub(app.alert, "show");
            app.error.handleValidationError({});
            expect(layoutStub).toHaveBeenCalled();
            expect(alertStub).not.toHaveBeenCalled();
            alertStub.restore();
            layoutStub.restore();
        });

        it('should do nothing when passed a bean', function() {
            var alertStub = sinon.collection.stub(app.alert, "show"),
                bean = new SugarTest.app.data.beanModel();
            app.error.handleValidationError(bean);
            expect(alertStub).not.toHaveBeenCalled();
            alertStub.restore();
        });
    });

    describe('400 invalid request error', function() {
        it('should show an error page on error', function() {
            var errorPageStub = sinon.collection.stub(app.controller, 'loadView');

            app.error.handleUnspecified400Error({});
            expect(errorPageStub).toHaveBeenCalledWith({
                layout: 'error',
                errorType: '400',
                module: 'Error',
                create: true
            });
            errorPageStub.restore();
        });
    });
});
