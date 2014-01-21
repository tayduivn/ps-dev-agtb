describe('ReorderableColumns Plugin', function() {
    var app, plugin;

    beforeEach(function() {
        app = SugarTest.app;
        // Load plugin directly so completely orthogonal to SUGAR.App
        SugarTest.loadPlugin('ReorderableColumns');
        plugin = app.plugins._get('ReorderableColumns', 'view');
    });
    afterEach(function() {
        app = null;
    });

    describe('_hasOrderChanged', function() {
        beforeEach(function() {
            plugin._listDragColumn = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        });

        it('should return false', function() {
            expect(plugin._hasOrderChanged(0, 0)).toBeFalsy();
            expect(plugin._hasOrderChanged(0, 1)).toBeFalsy();
            expect(plugin._hasOrderChanged(5, 5)).toBeFalsy();
            expect(plugin._hasOrderChanged(5, 6)).toBeFalsy();
            expect(plugin._hasOrderChanged(5, 6)).toBeFalsy();
            expect(plugin._hasOrderChanged(7, 8)).toBeFalsy();
        });

        it('should return true', function() {
            expect(plugin._hasOrderChanged(5, 0)).toBeTruthy();
            expect(plugin._hasOrderChanged(5, 1)).toBeTruthy();
            expect(plugin._hasOrderChanged(5, 3)).toBeTruthy();
            expect(plugin._hasOrderChanged(5, 7)).toBeTruthy();
        });
    });
});
