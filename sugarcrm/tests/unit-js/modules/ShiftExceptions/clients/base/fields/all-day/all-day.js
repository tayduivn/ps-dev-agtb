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
describe('ShiftExceptions.Fields.AllDay', function() {
    var app;
    var field;
    var fieldName = 'test_all_day';
    var model;
    var module = 'ShiftExceptions';
    var options;

    beforeEach(function() {
        app = SugarTest.app;
        model = app.data.createBean(module);
        options = {};
        SugarTest.loadComponent('base', 'field', 'all-day');

        field = SugarTest.createField(
            'base',
            fieldName,
            'all-day',
            'edit',
            {},
            module,
            model,
            null,
            true
        );

        sinon.collection.stub(field.model, 'set');
    });

    afterEach(function() {
        sinon.collection.restore();
        model = null;
        field = null;
        options = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            sinon.collection.stub(field, '_super');
            sinon.collection.stub(field.view, 'once');
        });

        it('should call the _super method with initialize', function() {
            field.initialize(options);
            expect(field._super).toHaveBeenCalledWith('initialize', [options]);
        });

        it('should call field.view.once with render', function() {
            field.initialize(options);
            expect(field.view.once).toHaveBeenCalledWith('render');
        });
    });

    describe('_restoreTime', function() {
        beforeEach(function() {
            field._currentDayStartEnd = {
                start_hour_test: 0,
            };
        });

        it('should set model by _currentDayStartEnd', function() {
            field._restoreTime();
            expect(field.model.set).toHaveBeenCalledWith(field._currentDayStartEnd);
        });
    });

    describe('_clearTime', function() {
        beforeEach(function() {
            sinon.collection.stub(field, '_saveTime');

            field._defaultDayStartEnd = {
                start_hour_test: 0,
            };
        });

        it('should save time', function() {
            field._clearTime(true);
            expect(field._saveTime).toHaveBeenCalled();
        });

        it('shouldn\'t save time', function() {
            field._clearTime(false);
            expect(field._saveTime).not.toHaveBeenCalled();
        });

        it('should set model by _defaultDayStartEnd', function() {
            field._clearTime(true);
            expect(field.model.set).toHaveBeenCalledWith(field._defaultDayStartEnd);
        });
    });

    describe('_updateTimeFields', function() {
        beforeEach(function() {
            field._timeFields = [];

            sinon.collection.stub(field, '_clearTime');
            sinon.collection.stub(field, '_restoreTime');
        });

        it('should clear time if allDay id checked', function() {
            sinon.collection.stub(field, 'getValue').returns(true);
            field._updateTimeFields(true);
            expect(field._clearTime).toHaveBeenCalledWith(true);
        });

        it('should restore time if allDay id checked', function() {
            sinon.collection.stub(field, 'getValue').returns(false);
            field._updateTimeFields(false);
            expect(field._restoreTime).toHaveBeenCalled();
        });
    });
});
