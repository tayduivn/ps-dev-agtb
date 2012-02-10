describe("Events Hub", function() {
    var eventHub = SUGAR.App.events,
        context = _.extend({env: "test"}, Backbone.Events),
        cb1 = sinon.spy(),
        cb2 = sinon.spy(),
        cb3 = sinon.spy();

    describe("when an event is published", function() {
        it("should fire event and all listners should call their callbacks", function() {
            eventHub.publish("testEvent", context);
            eventHub.on("testEvent", cb1);
            context.trigger("testEvent");

            expect(cb1.called).toBeTruthy();
        });
    });

    describe("when an event is removed", function() {
        it("should not broadcast removed events to subscribers", function() {
            eventHub.publish("nextEvent", context);
            eventHub.on("nextEvent", cb2);
            eventHub.clear("nextEvent", context);
            context.trigger("nextEvent");
            context.trigger("testEvent");

            expect(cb2.called).not.toBeTruthy();
            expect(cb1.calledTwice).toBeTruthy();
        });
    });

    describe("when all events are removed", function() {
        it("should not broadcast any events", function() {
            eventHub.on("all", cb3);
            eventHub.clear(context);
            context.trigger("testEvent");

            expect(cb3.called).not.toBeTruthy();
        });
    })
});