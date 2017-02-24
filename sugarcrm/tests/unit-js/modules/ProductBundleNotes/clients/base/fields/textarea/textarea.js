describe('ProductBundleNotes.Base.Fields.Textarea', function() {
    var app;
    var field;
    var fieldDef;
    var fieldType = 'textarea';
    var fieldModule = 'ProductBundleNotes';

    beforeEach(function() {
        app = SugarTest.app;
        fieldDef = {
            type: fieldType,
            label: 'testLbl'
        };

        field = SugarTest.createField('base', fieldType, fieldType, 'detail',
            fieldDef, fieldModule, null, null, true);
    });

    afterEach(function() {
        sinon.collection.restore();
        field.dispose();
        field = null;
    });

    describe('setMode()', function() {
        beforeEach(function() {
            sinon.collection.stub(app.view.Field.prototype.setMode, 'call', function() {});
        });

        it('should call app.view.Field.prototype.setMode.call', function() {
            field.setMode();
            expect(app.view.Field.prototype.setMode.call).toHaveBeenCalled();
        });

        it('should call app.view.Field.prototype.setMode.call with list', function() {
            field.setMode('list');
            expect(app.view.Field.prototype.setMode.call.lastCall.args[1]).toBe('list');
        });

        it('should call app.view.Field.prototype.setMode.call with edit', function() {
            field.setMode('edit');
            expect(app.view.Field.prototype.setMode.call.lastCall.args[1]).toBe('edit');
        });
    });
});
