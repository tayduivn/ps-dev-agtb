describe("List Column Ellipsis Plugin", function() {
    var app, plugin;

    beforeEach(function () {
        app = SugarTest.app;
        // Load plugin directly so completely orthogonal to SUGAR.App
        SugarTest.loadPlugin("list-column-ellipsis");
        plugin = app.plugins._get('list-column-ellipsis', 'view');
        plugin._fields = {};
        plugin._fields.visible = [{name: 'email'}];
    });
    afterEach(function() {
        app = null;
    });

    it("Should determine if field being toggled is last visible column", function() {
        var actual = plugin.isLastColumnVisible('email');
        expect(actual).toEqual(true);
    });
    it("Should not toggle field if more than one field is visible", function() {
        plugin._fields.visible = [{name: 'email'}, {name: 'foo'}];//add one extra
        var actual = plugin.isLastColumnVisible('email');
        expect(actual).toEqual(false);
    });
    it("Should set fields toggling selected from true to false", function() {
        var opts = [{name: 'no'}, {name: 'no'}, {name: 'yes', selected: true}, {name: 'no'}];
        plugin._fields.options = opts;
        plugin._toggleColumn('yes');
        expect(opts[2].selected).toEqual(false);
        expect(plugin._fields.visible.length).toBeFalsy();
    });
    it("Should set fields toggling selected from false to true", function() {
        var opts = [{name: 'no'}, {name: 'yes', selected: false}, {name: 'no'}];
        plugin._fields.options = opts;
        plugin._toggleColumn('yes');
        expect(opts[1].selected).toEqual(true);
        expect(plugin._fields.visible.length).toEqual(1);
    });
});
