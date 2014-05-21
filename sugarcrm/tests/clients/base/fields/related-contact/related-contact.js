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
describe('Base.Field.RelatedContact', function() {
    var app,
        field,
        sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();

        var model = new Backbone.Model();
        model.set({
            contact_id: 'testContactId'
        });
        model.module = 'Contacts';

        field = SugarTest.createField('base', 'related-contact', 'related-contact', 'list', null, null, model);

        sandbox.stub(field, '_super', function() {});
    });

    afterEach(function() {
        sandbox.restore();
        app = null;
        field = null;
    });

    describe('buildHref()', function() {
        it('should call buildCSSClasses to set css class', function() {
            var url = field.buildHref();
            expect(url).toBe('#Contacts/testContactId');
        });
    });

    describe('onLinkClicked()', function() {
        var routerRefreshSpy,
            backboneFragmentStub;

        beforeEach(function() {
            routerRefreshSpy = sandbox.spy(app.router, 'refresh');
            backboneFragmentStub = sandbox.stub(Backbone.history, 'getFragment', function() {
                return 'Contacts/testContactId'
            });
        });

        it('should call buildCSSClasses to set css class', function() {
            var url = field.buildHref();
            field.onLinkClicked();
            expect(routerRefreshSpy).toHaveBeenCalled();
        });
    });
});
