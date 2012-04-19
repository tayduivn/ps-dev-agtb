describe("Error module", function() {
    var app = SUGAR.App,
        server;

    beforeEach(function() {
        server = sinon.fakeServer.create();
    });

    afterEach(function() {
        server.restore();
    });

    it("should inject custom http error handlers and should handle http code errors", function() {
        var bean = app.data.createBean("Cases");
        var handled = false;

        // The reason we don't use a spy in this case is because
        // the status codes are copied instead of passed in by
        // by reference, thus the spied function will never be called.
        var statusCodes = {
            404: function() {
                console.log(handled);
                handled = true;
            }
        };

        app.error.initialize({statusCodes: statusCodes});

        server.respondWith([404, {}, ""]);
        bean.save();
        server.respond();
        expect(handled).toBeTruthy();
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
});