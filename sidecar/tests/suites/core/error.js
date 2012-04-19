describe("Error module", function() {
    var app = SUGAR.App,
        server;

    beforeEach(function() {
        server = sinon.fakeServer.create();
    });

    afterEach(function() {
        server.restore();
    });

    it("should handle http code errors", function() {
        var bean = app.data.createBean("Cases");
        var statusCodes = {
            404: function() {
                console.log("Error test");
            }
        };

        app.error.initialize({statusCodes: statusCodes});

        console.log(app.error.statusCodes);
        sinon.spy(statusCodes, "404");
        server.respondWith([404, {}, ""]);
        bean.save();
        server.respond();
        expect(statusCodes['404'].called).toBeTruthy();
        console.log(statusCodes['404']);
    });

    xit("should inject custom http error handlers", function() {
        var statusCodes = {
            404: function() {}
        }

        app.error.initialize({statusCodes: statusCodes});
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