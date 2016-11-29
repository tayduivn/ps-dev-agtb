describe('Base.Field.Name', function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', 'name', 'name', 'detail');
    });

    afterEach(function() {
        field.dispose();
    });

    describe('Render', function() {
        using('different view names and values', [
            ['audit', undefined, false],
            ['preview', undefined, true],
            ['preview', false, false],
            ['preview', true, true],
            ['other', undefined, undefined]
        ], function(view, linkValue, expected) {
            it('should set `def.link` appropriately based on view', function() {
                field.view.name = view;
                field.def.link = linkValue;
                field.render();
                expect(field.def.link).toBe(expected);
            });
        });
    });
});
