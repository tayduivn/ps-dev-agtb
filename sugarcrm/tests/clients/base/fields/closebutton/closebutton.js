describe('Close Button Field', function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('rowaction', 'field', 'base', 'list');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'closebutton');
        SugarTest.testMetadata.set();
        app.data.declareModels();

        field = SugarTest.createField('base', 'record-close', 'closebutton', 'list', {
            'name': 'record-close',
            'type': 'closebutton',
            'acl_action': 'edit'
        }, 'Contacts', app.data.createBean('Contacts'));
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.view.reset();
    });

    it('should show the button if the record is not closed', function() {
        field.model.set('status', 'Not Started');
        expect(field.isVisible()).toBe(true);
    });

    it('should hide the button if the record is closed', function() {
        field.model.set('status', 'Completed');
        expect(field.isVisible()).toBe(false);
    });

    it('should set record to completed on success', function() {
        var saveStub = sinon.stub(field.model, 'save', function(dummy, callbacks) {
            callbacks.success();
        });

        field.closeClicked();

        expect(saveStub.calledOnce).toBe(true);
        expect(field.model.get('status')).toBe('Completed');

        saveStub.restore();
    });

    it('should revert status to previous value on error', function() {
        var saveStub = sinon.stub(field.model, 'save', function(dummy, callbacks) {
            callbacks.error();
        });

        field.model
            .set('status', 'Not Started')
            .trigger('sync');

        field.closeClicked();

        expect(saveStub.calledOnce).toBe(true);
        expect(field.model.get('status')).toBe('Not Started');

        saveStub.restore();
    });

    it('should call method to open drawer to create a new record when closeNewClicked() is called', function() {
        var saveStub = sinon.stub(field.model, 'save', function(dummy, callbacks) {
            callbacks.success();
        }),
            openDrawerToCreateNewRecordStub = sinon.stub(field, 'openDrawerToCreateNewRecord');

        field.closeNewClicked();

        expect(openDrawerToCreateNewRecordStub.calledOnce).toBe(true);

        saveStub.restore();
        openDrawerToCreateNewRecordStub.restore();
    });
});
