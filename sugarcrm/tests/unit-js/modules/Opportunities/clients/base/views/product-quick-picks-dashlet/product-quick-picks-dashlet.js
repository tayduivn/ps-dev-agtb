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
describe('Opportunities.Base.Views.RecentUsedProductDashlet', function() {
    var app;
    var view;
    var viewMeta;
    var context;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;

        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        viewMeta = {
            config: false
        };

        layout = SugarTest.createLayout('base', 'Opportunities', 'record', {});
        view = SugarTest.createView('base', 'Opportunities',
            'product-quick-picks-dashlet', viewMeta, context, true, layout);
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
        layout.dispose();
        layout = null;
    });

    describe('initialize()', function() {
        it('should have Tooltip as one of the plugins', function() {
            expect(view.plugins).toEqual(['Tooltip']);
        });
    });

    describe('toggleLoading()', function() {
        var addClassStub;
        var removeClassStub;

        beforeEach(function() {
            addClassStub = sinon.collection.stub();
            removeClassStub = sinon.collection.stub();

            sinon.collection.stub(view.layout, '$', function() {
                return {
                    addClass: addClassStub,
                    removeClass: removeClassStub
                };
            });
        });

        afterEach(function() {
            addClassStub = null;
            removeClassStub = null;
        });

        it('should call show if startLoading is true', function() {
            view.toggleLoading(true);

            expect(view.layout.$).toHaveBeenCalledWith('i[data-action=loading]');
            expect(addClassStub).toHaveBeenCalledWith('fa-refresh fa-spin');
            expect(removeClassStub).toHaveBeenCalledWith('fa-cog');
        });

        it('should call show if startLoading is false', function() {
            view.toggleLoading(false);

            expect(view.layout.$).toHaveBeenCalledWith('i[data-action=loading]');
            expect(addClassStub).toHaveBeenCalledWith('fa-cog');
            expect(removeClassStub).toHaveBeenCalledWith('fa-refresh fa-spin');
        });
    });
});
