describe("Error module", function() {
    var app = SUGAR.App;

    xit("should handle http code errors", function() {

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