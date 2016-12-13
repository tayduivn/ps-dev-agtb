describe('ProductBundleNotes.Base.Fields.QuoteDataActionmenu', function() {
    var field;
    var fieldDef;
    beforeEach(function() {
        fieldDef = {
            type: 'quote-data-actionmenu',
            label: 'testLbl',
            css_class: '',
            buttons: ['button1'],
            no_default_action: true
        };
        field = SugarTest.createField('base', 'quote-data-actionmenu', 'quote-data-actionmenu',
            'detail', fieldDef, 'ProductBundleNotes', null, null, true);
    });

    afterEach(function() {
        sinon.collection.restore();
        field.dispose();
        field = null;
    });

    describe('_getChildFieldsMeta()', function() {
        it('should return a copy of the buttons', function() {
            expect(field._getChildFieldsMeta()).toEqual(['button1']);
        });
    });

    describe('toggleSelect()', function() {
        var parentTriggerStub;
        beforeEach(function() {
            parentTriggerStub = sinon.collection.stub();
            field.context.parent = {
                trigger: parentTriggerStub
            };
        });

        afterEach(function() {
            parentTriggerStub = null;
        });

        it('should call trigger on the context.parent', function() {
            field.toggleSelect(true);

            expect(parentTriggerStub).toHaveBeenCalled();
        });

        it('should trigger event mass_collection:add when checked is true', function() {
            field.toggleSelect(true);

            expect(parentTriggerStub).toHaveBeenCalledWith('mass_collection:add', field.model);
        });

        it('should trigger event mass_collection:remove when checked is false', function() {
            field.toggleSelect(false);

            expect(parentTriggerStub).toHaveBeenCalledWith('mass_collection:remove', field.model);
        });
    });
});
