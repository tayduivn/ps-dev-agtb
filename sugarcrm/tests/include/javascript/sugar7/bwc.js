describe("sugar7.extensions.bwc", function() {
    var app, module, id, action;

    beforeEach(function() {
        app = SugarTest.app;
        module = "Foo";
        action = "EditView";
        id = '12345';
    });
    afterEach(function() {
        module = null;
        action = null;
        id = null;
    });
    it("should have a login method", function() {
        var stub = sinon.stub(app.api, 'call');
        app.bwc.login('path/to/foo');
        expect(stub.called).toBe(true);
        expect(stub.args[0][0]).toEqual('create');
        expect(stub.args[0][1].match(/oauth2.bwc.login/)).not.toEqual(null);
        stub.restore();
    });
    it("should build a bwc route given module, action, id", function() {
        var expected, actual;
        expected = "bwc/index.php?module=" + module + "&action=" + action + "&record=" +id;
        actual = app.bwc.buildRoute(module, id, action);
        expect(actual).toEqual(expected);
    });
    it("should build a bwc route for just module (no action or id provided)", function() {
        var actual, expected;
        expected = "bwc/index.php?module=" + module + "&action=index";
        actual = app.bwc.buildRoute(module);
        expect(actual).toEqual(expected);
    });
    it("should build a bwc route for just module and id (no action provided)", function() {
        var actual, expected;
        expected = "bwc/index.php?module=" + module + "&action=DetailView&record=" + id;
        actual = app.bwc.buildRoute(module, id);
        expect(actual).toEqual(expected);
    });
    it("should build bwc for module and action (no id) respecting caller's choices unless DetailView", function() {
        var actual, expected;
        // action could be a list view or whatever and we should respect wishes in this case
        // module=Quotes&action=ListView
        // module=Quotes&action=EditView (which goes to Create)
        expected = "bwc/index.php?module=" + module + "&action=" + action;
        actual = app.bwc.buildRoute(module, null, action);
        expect(actual).toEqual(expected);

        // But! If they're asking for action DetailView, with no id, we DO force
        // to action=index since detail with no id just doesn't make sense
        expected = "bwc/index.php?module=" + module + "&action=index";
        actual = app.bwc.buildRoute(module, null, 'DetailView');
        expect(actual).toEqual(expected);
    });
});
