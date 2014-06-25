/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
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
