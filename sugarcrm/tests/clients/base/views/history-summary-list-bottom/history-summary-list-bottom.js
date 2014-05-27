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
describe('Base.View.HistorySummaryListBottom', function() {
    var app,
        view,
        sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();

        var context = app.context.getContext();
        view = SugarTest.createView('base', null, 'history-summary-list-bottom', null, context, false, null, true);

        sandbox.stub(app.lang, 'get', function() {
            return 'More history...';
        });
    });

    afterEach(function() {
        sandbox.restore();
        app = null;
        view = null;
    });

    describe('setShowMoreLabel()', function() {
        it('should populate showMoreLabel with lang string', function() {
            view.setShowMoreLabel();
            expect(view.showMoreLabel).toBe('More history...');
        });
    });
});
