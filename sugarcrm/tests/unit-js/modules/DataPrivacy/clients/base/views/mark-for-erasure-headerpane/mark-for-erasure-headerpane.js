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
describe('View.Views.Base.DataPrivacy.MarkForErasureHeaderpaneView', function() {
    var view;
    var app;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'headerpane');

        app = SUGAR.App;
        app.drawer = {
            close: sinon.collection.stub()
        };

        var model = app.data.createBean();
        var context = new app.Context({model: model});
        var layout = SugarTest.createLayout('base', null, 'base');
        view = SugarTest.createView(
            'base',
            'DataPrivacy',
            'mark-for-erasure-headerpane',
            null,
            context,
            'DataPrivacy',
            layout
        );
    });

    afterEach(function() {
        app.view.reset();
        view = null;
        delete app.drawer;
        app = null;
        sinon.collection.restore();
    });

    describe('close', function() {
        it('should close the drawer', function() {
            view.close();
            expect(app.drawer.close).toHaveBeenCalled();
        });
    });

    describe('markForErasure', function() {
        it('should trigger markforerasure:mark', function() {
            var triggerStub = sinon.collection.stub(view.context, 'trigger');
            view.markForErasure();
            expect(triggerStub).toHaveBeenCalledWith('markforerasure:mark');
        });
    });
});
