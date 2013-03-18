describe("forecasts_layout_inspector", function() {
    var app, view, context, layout, parent, actual;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        actual = null;
        SugarTest.loadComponent("base", "layout", "inspector");
        SugarTest.loadComponent("base", "view", "inspector-header");
        SugarTest.loadComponent("base", "layout", "inspector", "Forecasts");
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
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        layout.context = null;
        layout = null;
        parent = null;
        actual = null;
    });

    describe("events", function() {
        var _stubs = [];
        beforeEach(function(){
            layout = app.view.createLayout({
                name : "ForecastsInspector",
                context : context,
                module : null,
                meta : {},
                layout: parent
            });
        });

        afterEach(function() {
            _.each(_stubs, function(stub) {
                stub.restore();
            });

            _stubs = [];
        });

        it("showing should fire onBeforeShow and onShow", function() {
            layout.unbind();

            layout.onShow = function() {
                sinon.stub();
            };

            layout.onBeforeShow = function() {
                sinon.stub();
            };

            sinon.spy(layout, "onShow");
            sinon.spy(layout, "onBeforeShow");

            layout.bind();

            layout.show();

            expect(layout.onBeforeShow).toHaveBeenCalled();
            expect(layout.onShow).toHaveBeenCalled();
        });

        it("hiding should fire onBeforeHide and onHide", function() {

            layout.unbind();

            layout.onHide = function() {
                sinon.stub();
            };

            layout.onBeforeHide = function() {
                sinon.stub();
            };

            sinon.spy(layout, "onHide");
            sinon.spy(layout, "onBeforeHide");

            layout.bind();

            _stubs.push(sinon.stub(layout, 'isVisible', function(){
                return true;
            }));

            layout.hide();


            expect(layout.onBeforeHide).toHaveBeenCalled();
            expect(layout.onHide).toHaveBeenCalled();
        })
    });

    describe("Highlight Rows", function() {
        var _stubs = [], trs = [];
        beforeEach(function(){
            layout = app.view.createLayout({
                name : "ForecastsInspector",
                context : context,
                module : null,
                meta : {},
                layout: parent
            });


            var i = 0;
            while(i<5) {
                trs.push($('<tr><td>Row' + i + '</td><td><a rel="inspector"><i data-uid="test' + i + '"></i></a></td></tr>'));
                i++;
            }

            layout.setRows(trs);
        });

        afterEach(function() {
            _.each(_stubs, function(stub) {
                stub.restore();
            });

            _stubs = [];
            trs = [];
        });

        it("should highlight first row with below highlight", function() {
            layout.highlight(0);
            expect($(trs[0]).hasClass("current highlighted")).toBeTruthy();
            expect($(trs[1]).hasClass("highlighted below")).toBeTruthy();
        });

        it("should highlight second row with above and below highlights", function() {
            layout.highlight(1);
            expect($(trs[0]).hasClass("highlighted above")).toBeTruthy();
            expect($(trs[1]).hasClass("current highlighted")).toBeTruthy();
            expect($(trs[2]).hasClass("highlighted below")).toBeTruthy();
        });
        it("should highlight last row with above highlights", function() {
            var index = trs.length-1;
            layout.highlight(index);
            expect($(trs[index-1]).hasClass("highlighted above")).toBeTruthy();
            expect($(trs[index]).hasClass("current highlighted")).toBeTruthy();
        });

        it("should move highlight to next row", function() {
            layout.highlight(0);
            layout.moveHighlightedRow('next');
            expect($(trs[0]).hasClass("current highlighted")).toBeFalsy();
            expect($(trs[1]).hasClass("current highlighted")).toBeTruthy();
        });

        it("should move highlight to previous row", function() {
            layout.highlight(1);
            layout.moveHighlightedRow('previous');
            expect($(trs[1]).hasClass("current highlighted")).toBeFalsy();
            expect($(trs[0]).hasClass("current highlighted")).toBeTruthy();
        });

        it("should not move highlight to previous row", function() {
            layout.highlight(0);
            layout.moveHighlightedRow('previous');
            expect($(trs[0]).hasClass("current highlighted")).toBeTruthy();
        });

        it("should not move highlight to next row", function() {
            var index = trs.length-1;
            layout.highlight(index);
            layout.moveHighlightedRow('next');
            expect($(trs[trs.length-1]).hasClass("current highlighted")).toBeTruthy();
        });

        it("should remove highlight from first row and below highlight", function() {
            layout.highlight(0);
            layout.removeHighlight(0);
            expect($(trs[0]).hasClass("current highlighted")).toBeFalsy();
            expect($(trs[1]).hasClass("highlighted below")).toBeFalsy();
        });

        it("should remove highlight from second row and above and below highlights", function() {
            layout.highlight(1);
            layout.removeHighlight(1);
            expect($(trs[0]).hasClass("highlighted above")).toBeFalsy();
            expect($(trs[1]).hasClass("current highlighted")).toBeFalsy();
            expect($(trs[2]).hasClass("highlighted below")).toBeFalsy();
        });
        it("should remove highlight from last row and above highlights", function() {
            var index = trs.length-1;
            layout.highlight(index);
            layout.removeHighlight(index);
            expect($(trs[index-1]).hasClass("highlighted above")).toBeFalsy();
            expect($(trs[index]).hasClass("current highlighted")).toBeFalsy();
        });

        it("should not find highlighted record", function() {
            expect(layout.findHighlighted()).toEqual(-1);
        });

        it("should find highlight record", function() {
            layout.highlight(2);
            expect(layout.findHighlighted()).toEqual(2);
        });

        it("setRows should not highlight a row", function() {
            layout.setRows(trs);

            expect(layout.findHighlighted()).toEqual(-1);
        });

        it("setRows should highlight a row", function() {
            layout.setRows(trs, trs[2]);

            expect(layout.findHighlighted()).toEqual(2);
        });

        it("setRows should call hide if row is not in passed in rows", function() {
            hideSpy = sinon.spy(layout, "hide");
            layout.setRows(trs, $('<tr><td>Fake Row</td></tr>'));

            expect(hideSpy).toHaveBeenCalled();

            hideSpy.restore();
        })

    })
});
