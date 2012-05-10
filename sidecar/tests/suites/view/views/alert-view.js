describe("alertView", function() {
    var app, options, view, components;

    beforeEach(function() {
        SugarTest.seedApp();
        app = SugarTest.app;
        options = {
                context: {get: function() {
                    return 'cases';
                }},
                id: "1",
                template: function() {
                    return 'asdf';
                }
            };
        view = new SUGAR.App.view.views.AlertView(options);
        components = {alert:{target:'#alert'}};
        SugarTest.app.loadAdditionalComponents(components);
    });

    afterEach(function() {
        options = null;
    });

    it("should display an alert", function() {
        var cbSpy;
        view = SugarTest.app.additionalComponents.alert;
        expect(view).toBeDefined();
        expect(view instanceof app.view.View).toBeTruthy();

        cbSpy = sinon.spy(view, "show");

        //triggering the event
        app.alert.show("info", {level:'info', title:'foo', message:"message", autoclose: true});
        expect(cbSpy).toHaveBeenCalled();
    });
});
