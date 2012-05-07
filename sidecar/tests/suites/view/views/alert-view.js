describe("alertView", function() {
    var app;

    beforeEach(function() {
        app = SugarTest.app;
    });

    it("should fire a app:alert event", function() {
        var cbSpy = sinon.spy(function() {});
        app.events.on("app:alert", cbSpy);
        app.alert("info", "message");
        expect(cbSpy).toHaveBeenCalled();
    });

    it("should fire a app:alert elent and display an alert", function() {
        var options = {
                context: {get: function() {
                    return 'cases';
                }},
                id: "1",
                template: function() {
                    return 'asdf';
                }
            },
            view = new SUGAR.App.view.views.AlertView(options);

        var cbSpy = sinon.spy(view, "render");
        expect(view).toBeDefined();
        expect(view instanceof app.view.View).toBeTruthy();
        //triggering the event
        app.alert("info", "message");
        //should render the alert
        expect(cbSpy).toHaveBeenCalled();
    });
});
