describe("subnavView", function() {
    var app;

    beforeEach(function() {
        app = SugarTest.app;
    });

    it("should show/hide the view when the app route change", function() {

        var components = {subnav:{target:'#subnav'}};
        app.loadAdditionalComponents(components);
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