describe("alert", function() {
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
        SugarTest.app.controller.loadAdditionalComponents(components);
    });

    afterEach(function() {
        options = null; view = null; components = null;
    });

    it("should use alert in framework to call show then delegate alert view", function() {
        var showSpy;

        // Spy on alert view implementation show method
        showSpy = sinon.spy(app.additionalComponents.alert, 'show');

        // Framework's alert show should delegate to alert view implementation
        app.alert.show("fubar", {level:'info', title:'foo', message:"message", autoclose: true});
        expect(showSpy).toHaveBeenCalled();
    });

    it("should use alert in framework to call dismiss with key", function() {
        var closeSpy, alertView;

        app.alert.show("mykey", {level:'info', title:'foo', message:"message", autoclose: true});
        alertView = app.alert._get('mykey'); // kludgy kludge ;-)

        // Spy on alert view implementation show method
        closeSpy = sinon.spy(alertView, 'close');

        app.alert.dismiss('mykey');

        expect(closeSpy).toHaveBeenCalled();
        app.alert.dismiss('mykey');
        expect(closeSpy.calledOnce).toBeTruthy();
    });
    
    it("should do nothing if dismiss called with invalid key", function() {
        var closeSpy, alertView, success;

        app.alert.show("valid_key", {level:'info', title:'foo', message:"message", autoclose: true});
        // Spy on alert view implementation show method
        closeSpy = sinon.spy(alertView, 'close');
        success = app.alert.dismiss('bogus_key');
        expect(success).toBeFalsy();
    });

    it("should dismiss all", function() {
        var closeSpy1, closeSpy2, av1, av2;

        app.alert.show("mykey", {level:'info', title:'foo', message:"message", autoclose: true});
        app.alert.show("mykey2", {level:'info', title:'foo', message:"message", autoclose: true});
        av1 = app.alert._get('mykey'); // kludgy kludge ;-)
        av2 = app.alert._get('mykey2'); 

        // Spy on alert view implementation show method
        closeSpy1 = sinon.spy(av1, 'close');
        closeSpy2 = sinon.spy(av2, 'close');

        app.alert.dismissAll();
        expect(closeSpy1).toHaveBeenCalled();
        expect(closeSpy2).toHaveBeenCalled();

        app.alert.dismissAll();
        expect(closeSpy1.calledOnce).toBeTruthy();
        expect(closeSpy2.calledOnce).toBeTruthy();
    });


});
