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
describe('Opportunities.Base.Fields.DateCascade', function() {
    var app;
    var context;
    var model;
    var moduleName;
    var field;
    var fieldDef;

    beforeEach(function() {
        app = SugarTest.app;
        moduleName = 'Opportunities';
        model = app.data.createBean(moduleName, {
            id: '123test',
            name: 'Lórem ipsum dolor sit àmêt, ut úsu ómnés tatión imperdiet.'
        });
        context = new app.Context();
        context.set({model: model});

        fieldDef = {
            name: 'date_closed',
            type: 'date-cascade',
            label: 'LBL_LIST_DATE_CLOSED',
        };

        field = SugarTest.createField('base', 'date_closed', 'date-cascade',
            'detail', fieldDef, 'Opportunities', model, context, true);
        sinon.collection.stub(field, '_super');
    });

    afterEach(function() {
        sinon.collection.restore();
        app = null;
        context = null;
        fieldDef = null;
        model = null;
        field = null;
        moduleName = null;
    });

    describe('initialize', function() {
        var options;
        beforeEach(function() {
            options = {};
            field.plugins = [];

            sinon.collection.stub(app.lang, 'get')
                .withArgs('LBL_UPDATE_OPPORTUNITIES_RLIS', 'Opportunities').returns('Update Open');

            sinon.collection.stub(app.lang, 'getModuleName').withArgs('RevenueLineItems', {plural: true})
                .returns('Revenue Line Items');

            field.initialize([options]);
        });
        afterEach(function() {
            options = null;
        });
        it('should add Cascade to the plugins', function() {

            expect(field.plugins).toEqual(['Cascade']);
        });

        it('should call _super with initialize', function() {

            expect(field._super).toHaveBeenCalledWith('initialize');
        });

        it('should set the lblString to Update Open Revenue Line Items', function() {

            expect(field.def.lblString).toEqual('Update Open Revenue Line Items');
        });
    });

    describe('_getAppendToTarget', function() {
        it('should call _super with _getAppendToTarget when view is of list type', function() {
            field.view = 'recordlist';
            field._getAppendToTarget();

            expect(field._super).toHaveBeenCalledWith('_getAppendToTarget');
        });

        it('should find the parent element and position it relatively', function() {
            sinon.collection.stub(field.$el, 'attr')
                .withArgs('data-type')
                .returns('date-cascade');
            var $result = field._getAppendToTarget();

            expect($result.css('position')).toEqual('relative');
        });

        it('should call _super with _getAppendToTarget when data type is not found', function() {
            sinon.collection.stub(field.$el, 'attr')
                .withArgs('data-type')
                .returns('test');
            field._getAppendToTarget();

            expect(field._super).toHaveBeenCalledWith('_getAppendToTarget');
        });
    });
});
