describe('Sugar7 field extensions', function() {
    var app,
        viewName = 'record',
        field;


    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        app = SugarTest.app;
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        if(field) field.dispose();
        field = null;
    });
    describe('decorating required fields', function() {

        it("should call decorateRequired only on required fields on edit mode", function() {
            field = SugarTest.createField("base", "description", "base", "edit", {required: true});
            var spy = sinon.spy(field, 'decorateRequired');
            field.render();
            expect(spy.called).toBe(true);
            spy.reset();
            field.dispose();

            field = SugarTest.createField("base", "description", "base", "edit");
            field.render();
            expect(spy.called).toBe(false);
            spy.reset();
            field.dispose();

            field = SugarTest.createField("base", "description", "base", "detail", {required: true});
            field.render();
            expect(spy.called).toBe(false);
            spy.restore();
        });

        it("should call clearRequiredLabel prior to calling decorateRequired on a field", function() {
            field = SugarTest.createField("base", "description", "base", "edit", {required: true});
            var clearSpy = sinon.spy(field, 'clearRequiredLabel');
            var reqSpy = sinon.spy(field, 'decorateRequired');
            field.render();
            expect(clearSpy.called).toBe(true);
            expect(reqSpy.called).toBe(true);
            expect(clearSpy.calledBefore(reqSpy)).toBe(true);

            clearSpy.restore();
            reqSpy.restore();
        });
    });

});
