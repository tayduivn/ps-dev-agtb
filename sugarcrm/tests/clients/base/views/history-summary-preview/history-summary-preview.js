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
describe('Base.View.HistorySummaryPreview', function() {
    var app,
        view,
        sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();

        var context = app.context.getContext();
        view = SugarTest.createView('base', null, 'history-summary-preview', null, context, false, null, true);

        sandbox.stub(app.lang, 'get', function() {
            return 'More history...';
        });
        sandbox.stub(app.metadata, 'getModule', function() {
            return {
                isBwcEnabled: false
            };
        });
        sandbox.stub(app.alert, 'show', function() {});
        sandbox.stub(app.metadata, 'getView', function() {});

        // "stub" these functions from the parent class, don't need them
        // but we need them defined, but we don't care at all about them
        view._previewifyMetadata = function() {};
        view.renderPreview = function() {};
        app.drawer = {
            isActive: function() {
                return true;
            }
        };
    });

    afterEach(function() {
        sandbox.restore();
        app = null;
        view = null;
    });

    describe('_renderPreview()', function() {
        it('should use app.api.call not model.fetch', function() {
            sandbox.stub(app.api, 'call').returns(true);

            var model = new Backbone.Model();
            model.module = 'Notes';
            view._renderPreview(model, null, true);

            expect(app.api.call).toHaveBeenCalled();
        });
    });
});
