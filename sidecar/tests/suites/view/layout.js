describe("View.Layout", function(){
    var app;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
    });


    it("should get a component by name", function() {
        var layout = app.view.createLayout({
            name : "edit",
            module: "Contacts"
        });

        layout.addComponent(app.view.createView({
            name: "subedit"
        }));

        expect(layout._components.length).toEqual(2);

        var component = layout.getComponent("edit");
        expect(component).toBeDefined();
        expect(component.name).toEqual("edit");
        expect(component instanceof app.view.View).toBeTruthy();

        expect(layout.getComponent("foo")).toBeUndefined();
    });

    // TODO: Test Layout class: render method
    // TODO: Need to defined tests for sublayout, complex layouts, and inline defined layouts

});
