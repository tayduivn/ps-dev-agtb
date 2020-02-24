/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
describe('Base.Field.RecordDecor', function() {
    var field;
    var app;

    beforeEach(function() {
        app = SugarTest.app;
        var fieldDef = {};
        field = SugarTest.createField('base', 'record-decor', 'record-decor', 'record-decor', fieldDef);
    });

    afterEach(function() {
        sinon.collection.restore();
        field.dispose();
        app = null;
    });

    describe('_renderFields', function() {
        it('should render child fields', function() {
            var child = SugarTest.createField('base', 'name', 'name');
            sinon.collection.spy(child, 'render');
            field._renderFields([child]);
            expect(child.render).toHaveBeenCalledOnce();
            child.dispose();
        });
    });

    describe('redecorate', function() {
        it('should handle detail mode with non empty value', function() {
            var child = SugarTest.createField('base', 'name', 'name', 'detail');
            child.model.set('name', 'Name');
            var setCellStyleStub = sinon.collection.stub(field, 'setCellStyle');
            var showStub = sinon.collection.stub(child, 'show');
            child.action = 'detail';
            field.redecorate(child);
            expect(setCellStyleStub).toHaveBeenCalledWith('none');
            expect(showStub).toHaveBeenCalled();

            child.dispose();
        });

        it('should handle detail mode with empty value', function() {
            var child = SugarTest.createField('base', 'name', 'name', 'detail');
            child.model.set('name', '');
            var setCellStyleStub = sinon.collection.stub(field, 'setCellStyle');
            var hideStub = sinon.collection.stub(child, 'hide');
            child.action = 'detail';
            field.redecorate(child);
            expect(setCellStyleStub).toHaveBeenCalledWith('pill');
            expect(hideStub).toHaveBeenCalled();
            child.dispose();
        });

        it('should handle edit mode ', function() {
            var child = SugarTest.createField('base', 'name', 'name', 'detail');
            child.model.set('name', '');
            var setCellStyleStub = sinon.collection.stub(field, 'setCellStyle');
            var showStub = sinon.collection.stub(child, 'show');
            child.action = 'edit';
            field.redecorate(child);
            expect(setCellStyleStub).toHaveBeenCalledWith('none');
            expect(showStub).toHaveBeenCalled();
            child.dispose();
        });

        it('should handle edit mode with edit access in ACL', function() {
            sinon.collection.stub(app.acl, 'hasAccessToModel', function(action) {
                return action === 'edit';
            });

            const child = SugarTest.createField('base', 'name', 'name', 'detail');
            child.model.set('name', '');
            const setCellStyleStub = sinon.collection.stub(field, 'setCellStyle');
            const hideStub = sinon.collection.stub(child, 'hide');
            child.action = 'detail';
            field.redecorate(child);
            expect(setCellStyleStub).toHaveBeenCalledWith('pill');
            expect(hideStub).toHaveBeenCalled();
            child.dispose();
        });

        it('should handle edit mode without edit access in ACL', function() {
            sinon.collection.stub(app.acl, 'hasAccessToModel', function(action) {
                return action !== 'edit';
            });

            const child = SugarTest.createField('base', 'name', 'name', 'detail');
            child.model.set('name', '');
            const setCellStyleStub = sinon.collection.stub(field, 'setCellStyle');
            const showStub = sinon.collection.stub(child, 'show');
            child.action = 'detail';
            field.redecorate(child);
            expect(setCellStyleStub).toHaveBeenCalledWith('none');
            expect(showStub).toHaveBeenCalled();
            child.dispose();
        });
    });
})
