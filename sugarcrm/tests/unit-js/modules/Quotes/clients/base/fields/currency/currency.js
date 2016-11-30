describe('Quotes.Base.Fields.Currency', function() {
    var app;
    var layout;
    var view;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'record');

        var def = {
            name: 'deal_tot',
            type: 'currency',
        };

        layout = SugarTest.createLayout('base', 'Quotes', 'record', {});
        view = SugarTest.createView('base', 'Quotes', 'record', null, null, true, layout);
        field = SugarTest.createField({
            name: 'deal_tot',
            type: 'currency',
            viewName: 'detail',
            fieldDef: def,
            module: 'Quotes',
            model: view.model,
            loadFromModule: true
        });
        sinon.collection.stub(field, '_super');
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        layout.dispose();
        field.dispose();
        view = null;
        layout = null;
        app = null;
    });

    describe('format()', function() {
        it('should leave valuePercent undefined if deal_tot_discount_percentage is undefined', function() {
            field.model.set('deal_tot_discount_percentage', undefined);
            field.format(30);

            expect(field.valuePercent).toBeUndefined();
        });

        it('should leave valuePercent undefined if field name is not deal_tot', function() {
            field.view.name = 'quote-data-grand-totals-header';
            field.name = 'testField';

            field.model.set('deal_tot_discount_percentage', 10);
            field.format(30);

            expect(field.valuePercent).toBeUndefined();
        });

        it('should leave valuePercent undefined if field view name is not quote-data-grand-totals-header', function() {
            field.name = 'deal_tot';

            field.model.set('deal_tot_discount_percentage', 10);
            field.format(30);

            expect(field.valuePercent).toBeUndefined();
        });

        describe('when field name is deal_tot and field view name is header view', function() {
            var oldLangDir;
            beforeEach(function() {
                field.name = 'deal_tot';
                field.view.name = 'quote-data-grand-totals-header';
                field.model.set('deal_tot_discount_percentage', 0.1);
                oldLangDir = app.lang.direction;
            });

            afterEach(function() {
                app.lang.direction = oldLangDir;
                oldLangDir = null;
            });

            it('should set valuePercent using deal_tot_discount_percentage in LTR', function() {
                app.lang.direction = 'ltr';
                field.format(30);

                expect(field.valuePercent).toBe('10%');
            });

            it('should set valuePercent using deal_tot_discount_percentage in RTL', function() {
                app.lang.direction = 'rtl';
                field.format(30);

                expect(field.valuePercent).toBe('%10');
            });
        });
    });
});
