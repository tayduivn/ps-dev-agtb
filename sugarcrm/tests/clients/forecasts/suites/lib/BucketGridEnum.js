describe("ClickToEdit", function(){
    var field, view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile("../../../../clients/forecasts/lib", "BucketGridEnum", "js", function(d) { return eval(d); });
        view = {
            $el: $('<div class="testview"></div>'),
            url: "/test"
        };
        field = {
            $el: $('<div class="testfield"></div>'),
            viewName:'testView',
            delegateEvents: function() { return true; }
        };
        view.$el.append(field.$el);
    });

    afterEach(function() {
        view = {};
        field = {};
    });

    it("should add the mouseenter and mouseleave events", function() {
        expect(field.events).not.toBeDefined();
        new app.view.BucketGridEnum(field, view);
        expect(field.events).toBeDefined();
        expect(field.events.mouseenter).toBeDefined();
        expect(field.events.mouseleave).toBeDefined();
    });

});