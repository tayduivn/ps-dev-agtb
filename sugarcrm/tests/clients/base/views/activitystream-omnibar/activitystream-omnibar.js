describe("Activity Stream Omnibar", function() {
    var app, view, moduleName = 'Cases', viewName = 'activitystream-omnibar';

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        view = SugarTest.createView("base", moduleName, viewName, null, null);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
    });

    describe("taggable behaviour", function() {
        beforeEach(function() {
            view.render();
        });

        it("gets a list of entities when a leader and some text is entered", function() {
            sinon.spy(view, "getEntities");
            // The following line is needed, as Backbone has already attached
            // the events to the functions using jQuery, so the spy will never
            // be called unless the events are redelegated.
            view.delegateEvents();
            view.$(".taggable").html("@Sall").trigger("keyup");
            expect(view.getEntities).toHaveBeenCalled();
        });

        describe("_lastLeaderPosition", function() {
            // Assumption: @ and # are both leaders.

            it("returns -1 when there is no valid leader", function () {
                var cases = [
                    {
                        text: "",
                        result: -1
                    },
                    // The leader is followed by a terminator.
                    {
                        text: "I am so angry :@!",
                        result: -1
                    }
                ];
                _.each(cases, function(el) {
                    expect(view._lastLeaderPosition(el.text)).toBe(el.result);
                });
            });

            it("returns the location of the last leader position", function() {
                var cases = [
                    {
                        text: "@",
                        result: 0
                    },
                    {
                        text: "@sal #ste",
                        result: 5
                    },
                    {
                        text: "and @joe",
                        result: 4
                    },
                    {
                        text: "test@example.com #boo",
                        result: 17
                    }
                ];
                _.each(cases, function(el) {
                    expect(view._lastLeaderPosition(el.text)).toBe(el.result);
                });
            });
        });

        describe("_getTerm", function() {
            it("returns nothing if there is no leader", function() {
                var ret = view._getTerm(null, "@foo");
                expect(ret).toBeUndefined();
            });

            it("returns nothing if the length of the word is less than 3", function() {
                var ret = view._getTerm("@", "@f");
                expect(ret).toBeUndefined();
            });

            it("returns the term if all conditions are met", function() {
                var ret = view._getTerm("@", "@foo");
                expect(ret).toBe("foo");
            });

            it("does not expect a space before subsequent terms (MAR-765)", function() {
                var ret = view._getTerm("@", "foo@example.com@sally");
                expect(ret).toBe("sally");
            });
        });

        describe("_getLeader", function() {
            it("returns null if no leader is found", function() {
                var mock = sinon.mock(view), ret;
                mock.expects("_lastLeaderPosition").once().returns(-1);
                ret = view._getLeader("foobar");
                expect(ret).toBeNull();
                mock.verify();
            });

            it("returns the leader character if a leader is found", function() {
                var mock = sinon.mock(view), ret;
                mock.expects("_lastLeaderPosition").once().returns(0);
                ret = view._getLeader("@foobar");
                expect(ret).toBe("@");
                mock.verify();
            });
        });
    });
});
