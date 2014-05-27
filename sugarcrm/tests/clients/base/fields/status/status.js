/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
describe('Base.Field.Status', function() {
    var app,
        field,
        sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();

        var model = new Backbone.Model();
        model.set({
            status: 'testStatus'
        });

        field = SugarTest.createField('base', 'status', 'status', 'list', null, null, model);

        sandbox.stub(field, '_super', function() {});
    });

    afterEach(function() {
        sandbox.restore();
        app = null;
        field = null;
    });

    describe('initialize()', function() {
        it('should call buildCSSClasses to set css class', function() {
            sandbox.spy(field, 'buildCSSClasses');
            field.initialize();
            expect(field.buildCSSClasses).toHaveBeenCalled();
        });
    });

    describe('buildCSSClasses()', function() {
        it('should populate cssClasses properly based on field name and value', function() {
            field.buildCSSClasses();
            expect(field.cssClasses).toBe('field_status_testStatus');
        });
    });
});
