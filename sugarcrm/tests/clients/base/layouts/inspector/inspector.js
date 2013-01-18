describe("Base.Layout.Inspector", function() {
    var app, view, context, layout, parent, actual;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        actual = null;
        SugarTest.loadComponent("base", "layout", "inspector");
        SugarTest.loadComponent("base", "view", "inspector-header");
        parent = (function(){
            return {
                caller : {},
                on : function(event, caller) {
                    actual = event;
                    this.caller[event] = caller;
                },
                trigger : function(event, options) {
                    this.caller[event].call(this, options);
                }
            };
        })();
        if (!$.fn.modal) {
            $.fn.modal = function(options) {};
        }
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        $.fn.modal = null;
        layout.context = null;
        layout = null;
        parent = null;
        actual = null;
    });

    it("should delegate triggers at construction time", function(){
        var expected = 'app:layout:inspector:open1',
            options = {
                'showEvent' : expected
            };
        sinon.spy(parent, "on");
        layout = app.view.createLayout({
            name : "inspector",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });
        expect(actual).toEqual(expected);
        expect(parent.on).toHaveBeenCalledOnce();
        expect(parent.on.calledWith(actual)).toBe(true);
    });

    it("inspector-header component should contain title", function(){
        var options = {
                'components' : [ { view: 'blah' }]
            },
            expectedTitle = "Test Title";
        layout = app.view.createLayout({
            name : "inspector",
            context : context,
            module : null,
            meta : options,
            layout: parent
        });

        layout.display({title: expectedTitle});

        var actualTitle = layout.getComponent("inspector-header").title;

        expect(actualTitle).toEqual(expectedTitle);
    });

    describe("should set and fire", function() {
        beforeEach(function(){
            var options = {
                'components' : [ { view: 'blah' }]
            };
            layout = app.view.createLayout({
                name : "inspector",
                context : context,
                module : null,
                meta : options,
                layout: parent
            });
        });
        afterEach(function(){
            // clean up any events that were added
            layout.off();
            layout.offBefore();
        });

        it("show events", function() {
            var eventStub = sinon.spy(function() {}),
                beforeEventStub = sinon.spy(function() {}),
                params = {
                    events: {
                        show : eventStub,
                        before : {
                            show : beforeEventStub
                        }
                    }
                };

            // displayed registers the events
            layout.display(params);
            expect(beforeEventStub).toHaveBeenCalled();
            expect(eventStub).toHaveBeenCalled();

        });
        it("hide event", function() {
            var eventStub = sinon.spy(function() {}),
                beforeEventStub = sinon.spy(function() {}),
                params = {
                    events: {
                        hide : eventStub,
                        before : {
                            hide : beforeEventStub
                        }
                    }
                };

            // displayed registers the events
            layout.display(params);
            // hide it to test if the hide events have fired
            layout.hide();
            expect(beforeEventStub).toHaveBeenCalled();
            expect(eventStub).toHaveBeenCalled();
        });
    });

    describe("should invoke before/after", function(){

        var _stubs = [];

        afterEach(function() {
            _.each(_stubs, function(stub){
                stub.restore();
            });
            _stubs = [];
        });

        it("while inspector is showing and hiding", function() {
            var options = {
                    'components' : [ { view: 'blah' }]
                };
            layout = app.view.createLayout({
                name : "inspector",
                context : context,
                module : null,
                meta : options,
                layout: parent
            });
            layout.triggerBefore = function(event) {
                sinon.stub();
            };

            layout.trigger = function(event) {
                sinon.stub();
            };

            _stubs.push(sinon.stub(layout, 'isVisible', function(){
                return true;
            }));

            var showOptions = {'blah' : 'yeahhh'};
            sinon.spy(layout, "triggerBefore");
            sinon.spy(layout, "trigger");

            layout.display({options: showOptions});
            expect(layout.triggerBefore.calledWith('show')).toBe(true);
            expect(layout.triggerBefore.calledWith('hide')).toBe(false);
            layout.hide();
            expect(layout.triggerBefore.calledWith('hide')).toBe(true);
        });
    });
});