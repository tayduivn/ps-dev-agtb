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
    
    describe('Edit mode css class', function() {
        var editClass = 'edit';
        var detailClass = 'detail';

        it('should render in detail mode without the edit class', function() {
            field = SugarTest.createField("base", "description", "base", "detail");
            field.render();
            expect(field.getFieldElement().hasClass(editClass)).toBeFalsy();
            expect(field.getFieldElement().hasClass(detailClass)).toBeTruthy();
        });

        it('should render in edit mode with edit class', function() {
            field = SugarTest.createField("base", "description", "base", "edit");
            field.render();
            expect(field.getFieldElement().hasClass(editClass)).toBeTruthy();
            expect(field.getFieldElement().hasClass(detailClass)).toBeFalsy();
        });

        it('should add the edit class when toggled to edit mode', function() {
            field = SugarTest.createField("base", "description", "base", "detail");
            field.render();

            field.setMode('edit');
            expect(field.getFieldElement().hasClass(editClass)).toBeTruthy();
            expect(field.getFieldElement().hasClass(detailClass)).toBeFalsy();
        });

        it('should remove the edit class when toggled from edit to detail mode', function() {
            field = SugarTest.createField("base", "description", "base", "edit");
            field.render();

            field.setMode('detail');
            expect(field.getFieldElement().hasClass(editClass)).toBeFalsy();
            expect(field.getFieldElement().hasClass(detailClass)).toBeTruthy();
        });
    });
});
