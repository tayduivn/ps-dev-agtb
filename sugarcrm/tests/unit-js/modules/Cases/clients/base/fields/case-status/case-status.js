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
describe('Base.Fields.CaseStatus', function() {
    var app;
    var field;
    var fieldName = 'test_enum';
    var model;
    var module = 'Cases';

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean(module);

        field = SugarTest.createField(
            'base',
            fieldName,
            'case-status',
            'list',
            {},
            module,
            model,
            null,
            true
        );

        field.items = {
            'New': 'New',
            'Duplicate': 'Duplicate',
        };
    });

    afterEach(function() {
        sinon.collection.restore();
        model = null;
        field = null;
    });

    describe('bindDataChange', function() {
        it('should call setColorCoding on change', function() {
            field.bindDataChange();

            var setColorCodingStub = sinon.collection.stub(field, 'setColorCoding');
            field.model.set(field.name, 'Duplicate');

            expect(setColorCodingStub).toHaveBeenCalled();
        });
    });

    describe('setColorCoding', function() {
        beforeEach(function() {
            field.$el.attr('class', ''); // remove all classes
        });

        it('should add the classes based on the status as well as the default pill classes on list view', function() {
            field.model.set(field.name, 'Duplicate', {silent: true});
            field.action = 'list';

            field.setColorCoding();

            var classes = field.$el.attr('class').split(' ');
            expect(classes.length).toEqual(3);
            expect(classes).toContain('blue');
            expect(classes).toContain('label');
            expect(classes).toContain('pill');

            // test changing from one status to another
            field.model.set(field.name, 'New', {silent: true});

            field.setColorCoding();

            classes = field.$el.attr('class').split(' ');
            expect(classes).not.toContain('blue');
            expect(classes).toContain('dark-green');
            expect(classes).toContain('label');
            expect(classes).toContain('pill');
        });

        it('should not add any classes if the action is not list', function() {
            field.action = 'detail';

            field.setColorCoding();

            var classes = field.$el.attr('class').trim();
            expect(classes.length).toEqual(0);
        });

        it('should not add any classes for an empty value', function() {
            field.model.set(field.name, '', {silent: true});
            field.action = 'list';

            field.setColorCoding();

            var classes = field.$el.attr('class').trim();
            expect(classes.length).toEqual(0);
        });

        it('should not add any classes if there is no color defined', function() {
            field.model.set(field.name, 'A status with no color', {silent: true});
            field.action = 'list';

            field.setColorCoding();

            var classes = field.$el.attr('class').trim();
            expect(classes.length).toEqual(0);
        });
    });
});
