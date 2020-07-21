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
describe('Products.Base.Fields.Date', function() {
    var app;
    var layout;
    var view;
    var field;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'record');

        var def = {
            name: 'service_start_date',
            type: 'date'
        };

        layout = SugarTest.createLayout('base', 'Products', 'record', {});
        view = SugarTest.createView('base', 'Products', 'record', null, null, true, layout);
        field = SugarTest.createField({
            name: 'service_start_date',
            type: 'date',
            viewName: 'record',
            fieldDef: def,
            module: 'Products',
            model: view.model,
            loadFromModule: true
        });

        view.model.set('add_on_to_id', 'dummy_id');

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

    // BEGIN SUGARCRM flav=ent ONLY

    describe('bindDataChange()', function() {
        it('should set a field change listener for service_start_date', function() {
            sinon.collection.spy(field.model, 'on');
            field.bindDataChange();

            expect(field.model.on).toHaveBeenCalledWith('change:service_start_date');
        });
    });

    describe('handleRecalculateServiceDuration()', function() {
        it('should not recalculate when add on to PLI has not been selected', function() {
            view.model.set('add_on_to_id', null);
            view.model.set('service_start_date', '2020-01-01');
            view.model.set('service_end_date', '2021-01-01');
            view.model.set('service_duration_unit', 'year');
            view.model.set('service_duration_value', 1);

            field.handleRecalculateServiceDuration();

            expect(view.model.get('service_duration_unit')).toBe('year');
            expect(view.model.get('service_duration_value')).toBe(1);
        });

        using('different start and end dates', [
            {
                startDate: '2020-01-01',
                endDate: '2020-01-01',
                expectedDiff: 1
            },
            {
                startDate: '2020-01-01',
                endDate: '2020-01-02',
                expectedDiff: 2
            },
            {
                startDate: '2020-01-01',
                endDate: '2020-02-01',
                expectedDiff: 32
            },
            {
                startDate: '2020-01-01',
                endDate: '2021-01-01',
                expectedDiff: 367 // leap year
            },
            {
                startDate: '2021-01-01',
                endDate: '2022-01-01',
                expectedDiff: 366 // non leap year
            },
            {
                startDate: '2020-07-06',
                endDate: '2020-09-15',
                expectedDiff: 72
            },
            {
                startDate: '2020-01-02',
                endDate: '2020-01-01',
                expectedDiff: -1
            }
        ], function(provider) {

            it('should correctly recalculate service duration', function() {
                view.model.set('service_start_date', provider.startDate);
                view.model.set('service_end_date', provider.endDate);

                field.handleRecalculateServiceDuration();

                expect(view.model.get('service_duration_unit')).toBe('day');
                expect(view.model.get('service_duration_value')).toBe(provider.expectedDiff);
            });
        });
    });

    // END SUGARCRM flav=ent ONLY
});
