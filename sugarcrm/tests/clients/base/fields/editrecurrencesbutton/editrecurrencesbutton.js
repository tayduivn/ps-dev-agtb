describe("Base.Fields.Editrecurrencesbutton", function() {
    var app, field, fieldName = 'editrecurrencesbutton';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        field = SugarTest.createField('base', fieldName, fieldName);
    });

    afterEach(function() {
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    it('should show button when repeat_type field is set', function() {
        field.model.set('repeat_type', 'foo');
        expect(field.hasAccess()).toEqual(true);
    });

    it('should hide button when repeat_type field is not set', function() {
        field.model.set('repeat_type', '');
        expect(field.hasAccess()).toEqual(false);
    });

    it('should re-render field when repeat_type field is changed', function() {
        var spy = sinon.spy(field, '_render');
        field.model.set('repeat_type', '', {silent: true});
        field.model.set('repeat_type', 'true');
        expect(spy.callCount).toBe(1);
    });
});
