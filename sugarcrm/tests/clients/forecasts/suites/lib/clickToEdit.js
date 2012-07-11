describe("ClickToEdit", function(){
    var field, view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile("../../../../clients/forecasts/lib", "ClickToEdit", "js", function(d) { return eval(d); });
        view = {
            $el: $('<div class="testview"></div>'),
            url: "/test"
        };
        field = {
            $el: $('<div class="testfield"></div>'),
            viewName:'testView',
            def: {
                clickToEdit:true
            },
            delegateEvents: function() {}
        };
        view.$el.append(field.$el);
    });

    afterEach(function() {
        view = {};
        field = {};
    });

    it("should add the editable plugin on the element", function() {
        var jqSpy = sinon.spy(field.$el, "editable");
        new app.view.ClickToEditField(field, view);
        expect(jqSpy).toHaveBeenCalled();
    });

    it("should add the show/hide icon handlers", function(){
        expect(field.showCteIcon).not.toBeDefined();
        expect(field.hideCteIcon).not.toBeDefined();
        new app.view.ClickToEditField(field, view);
        expect(field.showCteIcon).toBeDefined();
        expect(field.hideCteIcon).toBeDefined();
    });

    it("should add the mouseenter and mouseleave events", function() {
        expect(field.events).not.toBeDefined();
        new app.view.ClickToEditField(field, view);
        expect(field.events).toBeDefined();
        expect(field.events.mouseenter).toBeDefined();
        expect(field.events.mouseleave).toBeDefined();
    });

    describe("pencil icon", function() {
        beforeEach(function(){
            new app.view.ClickToEditField(field, view);
        });

        it("should be added on mouseenter events and removed on mouseleave events", function() {
            expect(field.$el.parent()).not.toContain("i.icon-pencil");
            field.showCteIcon();
            expect(field.$el.parent()).toContain("i.icon-pencil");
            field.hideCteIcon();
            expect(field.$el.parent()).not.toContain("i.icon-pencil");
        });
    });
});