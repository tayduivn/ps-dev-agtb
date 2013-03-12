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
        expected = "#bwc/index.php?module=" + module + "&action=" + action + "&record=" +id;
        actual = app.bwc.buildRoute(module, id, action);
        expect(actual).toEqual(expected);
    });
    it("should build a bwc route for just module", function() {
        var actual, expected;
        expected = "#bwc/index.php?module=" + module;
        actual = app.bwc.buildRoute(module);
        expect(actual).toEqual(expected);
    });
    it("should build a bwc route for just module and id", function() {
        var actual, expected;
        expected = "#bwc/index.php?module=" + module + "&record=" + id;
        actual = app.bwc.buildRoute(module, id);
        expect(actual).toEqual(expected);
    });
});
