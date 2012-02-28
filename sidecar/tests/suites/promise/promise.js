describe("A promise state machine object", function() {
    var handler = function(task, promise) {
        promise.completeTask(task);
    };

    describe("when initialized with immutability", function() {
        xit("should set its initial incomplete states with the provided state object", function() {
            var states = {}, promise;

            promise = new SUGAR.App.promise({
                tasks: states,
                callback: function() {
                }
            });

            expects(promise.addTask).toBeFalsy();
            expects(promise.getRemainingTasks()).toEqual(states);
        });
    });

    describe("when initialized with mutability", function() {
        var promise;

        beforeEach(function() {
            promise = new SUGAR.App.promise({
                mutable: true
            });
        });

        xit("should add states", function() {
            var task = "doSomething";

            promise.addTask();
            expect(promise.getTasks()).toEqual([task]);
        });

        xit("should remove states when they have been resolved", function() {
            var handlerSpy = sinon.spy(handler);

            promise.addTask("firstTask");
            promise.addTask("secondTask");

            handler("secondTask", promise);

            expect(handlerSpy.called).toBeTruthy();
            expect(promise.getTasks()).toEqual(["firstTask"]);

            handlerSpy.restore();
        });
    });

    xit("should fire relevant events and execute callback when states have been resolved", function() {
        var finalState = false,
            callback = function() {
            finalState = true;
        };
        var callbackSpy = sinon.spy(callback),
            eventSpy = sinon.spy(function() {});
            promise = new SUGAR.App.promise({
                tasks: ["first"],
                callback: callback
            });

        promise.bind("resolved", eventSpy);

        expect(finalState).toBeFalsy();
        promise.completeTask("first");
        expect(finalState).toBeTruthy();
        expect(callbackSpy.called).toBeTruthy();
        expect(eventSpy.called).toBeTruthy();
    });

    xit("should reset states to initial state", function() {
        var states = ["firstTask", "secondTask"];
        var promise = new SUGAR.App.promise({
            tasks: states
        });

        handler("firstTask", promise);
        promise.reset();

        expect(promise.getTasks()).toEqual(states);
    });
});