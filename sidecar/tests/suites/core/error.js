describe("Error module", function() {
    var app;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        SugarTest.seedFakeServer();
    });

    it("should inject custom http error handlers and should handle http code errors", function() {
        var bean = app.data.createBean("Cases"),
            handled = false, statusCodes;

        // The reason we don't use a spy in this case is because
        // the status codes are copied instead of passed in by
        // by reference, thus the spied function will never be called.
        statusCodes = {
            404: function() {
                handled = true;
            }
        };

        app.error.initialize({statusCodes: statusCodes});

        sinon.spy(app.error, "handleHTTPError");
        SugarTest.server.respondWith([404, {}, ""]);
        bean.save();
        SugarTest.server.respond();
        expect(handled).toBeTruthy();
        expect(app.error.handleHTTPError.called).toBeTruthy();

        app.error.handleHTTPError.restore();
    });

    it("should handle validation errors", function() {
        var bean;

        // Set the length arbitrarily low to force validation error
        fixtures.metadata.modules.Cases.fields.name.len = 1;
        app.data.declareModel("Cases", fixtures.metadata.modules.Cases);
        bean = app.data.createBean("Cases");

        app.error.initialize();
        sinon.spy(app.error, "handleValidationError");

        bean.set({name: "This is a test"});
        bean.save();

        expect(app.error.handleValidationError.called).toBeTruthy();

        // Restore previous states
        fixtures.metadata.modules.Cases.fields.name.len = 255;
        app.data.declareModel("Cases", fixtures.metadata.modules.Cases);
        app.error.handleValidationError.restore();
    });

    it("overloads window.onerror", function() {
        // Remove on error
        window.onerror = false;

        // Initialize error module
        app.error.overloaded = false;
        app.error.initialize();

        // Check to see if onerror was overloaded
        expect(_.isFunction(window.onerror)).toBeTruthy();
    });

    it("should get error strings", function(){
        var errorKey = "ERROR_TEST";
        var context = "10";
        var string = app.error.getErrorString(errorKey, context);
        expect(string).toEqual("Some error string 10");
    });
});
