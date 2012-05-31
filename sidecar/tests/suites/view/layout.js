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

    it("should dispose itself", function() {
        var model = app.data.createBean("Contacts");
        var collection = app.data.createBeanCollection("Contacts");
        var context = app.context.getContext({
            model: model,
            collection: collection
        });

        var layout = app.view.createLayout({
            name: "edit",
            module: "Contacts",
            context: context
        });

        var view = layout._components[0];
        view.fallbackFieldTemplate = "edit";
        view.template = app.template.get("edit");
        view.on("foo", function() {});

        // Fake bindDataChange
        collection.on("reset", view.render, view);

        // Different scope
        var obj = {
            handler: function() {}
        };
        model.on("change", obj.handler, obj);
        collection.on("reset", obj.handler, obj);

        layout.render();
        var fields = _.clone(view.fields);

        expect(_.isEmpty(model._callbacks)).toBeFalsy();
        expect(_.isEmpty(collection._callbacks)).toBeFalsy();
        expect(_.isEmpty(view._callbacks)).toBeFalsy();

        var spy = sinon.spy(app.view.Field.prototype, "unbindDom");
        var spy2 = sinon.spy(app.view.Component.prototype, "remove");

        layout.dispose();

        // Dispose shouldn't remove callbacks that are not scoped by components
        expect(_.keys( model._callbacks).length).toEqual(1);
        expect(_.keys( model._callbacks)[0]).toEqual("change");
        expect(_.keys(collection._callbacks).length).toEqual(1);
        expect(_.keys(collection._callbacks)[0]).toEqual("reset");

        // Check if layout is disposed
        expect(layout.disposed).toBeTruthy();
        expect(layout._components.length).toEqual(0);
        expect(layout.model).toBeNull();
        expect(layout.collection).toBeNull();
        expect(function() { layout.render(); }).toThrow();

        // Check if view is disposed
        expect(view.disposed).toBeTruthy();
        expect(_.isEmpty(view.fields)).toBeTruthy();
        expect(_.isEmpty(view._callbacks)).toBeTruthy();
        expect(view.model).toBeNull();
        expect(view.collection).toBeNull();
        expect(function() { view.render(); }).toThrow();

        // Check if fields are disposed
        expect(spy.callCount).toEqual(6); // for each field
        _.each(fields, function(field) {
            expect(field.disposed).toBeTruthy();
            expect(function() { field.render(); }).toThrow();
            expect(field.model).toBeNull();
            expect(field.collection).toBeNull();
        });

        expect(spy2.callCount).toEqual(8); // 6 fields + 1 layout + 1 view

    });

    // TODO: Test Layout class: render method
    // TODO: Need to defined tests for sublayout, complex layouts, and inline defined layouts

});
