describe("Filter View", function() {

    var view, app;

    beforeEach(function() {
        view = SugarTest.createView("base","Cases", "filter");
        view.model = new Backbone.Model();
        view.collection = new Backbone.Collection(view.model);
        view.collection.fields = _.keys(fixtures.metadata.modules.Cases.fields);
        view.layout = {
                trigger: function(){}
        };
        app = SUGAR.App;
    });


    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });


    describe("openPanel", function() {
        it("should have triggered filter:create:open:fire", function() {
            var triggerSpy = sinon.spy(view.layout, "trigger");
            view.openPanel();
            expect(triggerSpy).toHaveBeenCalledWith("filter:create:open:fire");
        });
    });

    describe("selectedByEnter", function() {
        it("should set changedByEnter to true on enter (13)", function() {
            view.selectedByEnter({keyCode:13});
            expect(view.changedByEnter).toBeTruthy();
        });
    });

    describe("selectedByEnter", function() {
        it("should set changedByEnter to false on other (12)", function() {
            view.selectedByEnter({keyCode:12});
            expect(view.changedByEnter).toBeFalsy();
        });
    });

});
