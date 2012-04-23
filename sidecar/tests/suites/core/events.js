describe("Events Hub", function() {
    var eventHub = SUGAR.App.events,
        context = _.extend({env: "test"}, Backbone.Events),
        cb1 = sinon.spy(),
        cb2 = sinon.spy(),
        cb3 = sinon.spy();

    describe("when an event is registered", function() {
        it("should fire event and all listners should call their callbacks", function() {
            eventHub.register("testEvent", context);
            eventHub.on("testEvent", cb1);
            context.trigger("testEvent");

            expect(cb1).toHaveBeenCalled();
        });
    });

    describe("when an event is removed", function() {
        it("should not broadcast removed events to subscribers", function() {
            eventHub.register("nextEvent", context);
            eventHub.on("nextEvent", cb2);
            eventHub.clear("nextEvent", context);
            context.trigger("nextEvent");
            context.trigger("testEvent");

            expect(cb2).not.toHaveBeenCalled();
            expect(cb1).toHaveBeenCalledTwice();
        });
    });

    describe("when all events are removed", function() {
        it("should not broadcast any events", function() {
            eventHub.on("all", cb3);
            eventHub.clear(context);
            context.trigger("testEvent");

            expect(cb3).not.toHaveBeenCalled();
        });
    });
});
