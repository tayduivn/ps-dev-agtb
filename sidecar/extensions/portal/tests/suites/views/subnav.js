describe("Subnav View", function() {
    var app, SubNav;

    beforeEach(function() {
        var controller;
        //SugarTest.app.config.env = "dev"; // so I can see app.data ;=)
        controller = SugarTest.loadFile('../../../../../sugarcrm/clients/base/views/subnav', 'subnav', 'js', function(d){ return d;});
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        SubNav = app.view.declareComponent('view', 'Subnav', null, controller);
    });

    it("should show/hide the view when the app route change", function() {
        var components = {subnav:{target:'#subnav'}};
        app.controller.context.set('module',null);
        app.controller.loadAdditionalComponents(components);
        var view = app.additionalComponents.subnav;
        expect(view).toBeDefined();
        expect(view instanceof app.view.View).toBeTruthy();

        var cbSpyShow = sinon.spy(view, "show");
        var cbSpyHide = sinon.spy(view, "hide");

        //triggering the event for detail layout
        app.trigger("app:view:change", "detail");
        //should show the subnav
        expect(cbSpyShow).toHaveBeenCalled();

        //triggering the event for list layout
        app.trigger("app:view:change", "list");
        //should hide the subnav
        expect(cbSpyHide).toHaveBeenCalled();
    });
});
