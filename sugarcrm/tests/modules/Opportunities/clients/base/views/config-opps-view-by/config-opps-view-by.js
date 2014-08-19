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
describe('Opportunities.View.ConfigOppsViewBy', function() {
    var app,
        view,
        options,
        meta,
        context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();

        var cfgModel = new Backbone.Model({
            opps_view_by: 'Opportunities'
        });

        context.set({
            model: cfgModel,
            module: 'Opportunities'
        });

        meta = {
            label: 'testLabel',
            panels: [{
                fields: []
            }]
        };

        options = {
            meta: meta,
            context: context
        };

        sinon.collection.stub(app.metadata, 'getModule', function() {
            return {
                is_setup: false
            }
        });

        // load the parent config-panel view
        SugarTest.loadComponent('base', 'view', 'config-panel');
        view = SugarTest.createView('base', 'Opportunities', 'config-opps-view-by', meta, null, true);
    });

    afterEach(function() {
        sinon.collection.restore();
        view = null;
    });

    describe('initialize()', function() {
        it('should set currentOppsViewBySetting', function() {
            view.initialize(options);
            expect(view.currentOppsViewBySetting).toEqual('Opportunities');
        });

        it('should call createWarningEl if Forecasts is setup', function() {
            app.metadata.getModule.restore();
            sinon.collection.stub(app.metadata, 'getModule', function() {
                return {
                    is_setup: true
                }
            });
            sinon.collection.stub(view, 'createWarningEl', function() {});
            view.initialize(options);
            expect(view.createWarningEl).toHaveBeenCalled();
        });

        it('should not call createWarningEl if Forecasts is not setup', function() {
            sinon.collection.stub(view, 'createWarningEl', function() {});
            view.initialize(options);
            expect(view.createWarningEl).not.toHaveBeenCalled();
        });
    });

    describe('createWarningEl()', function() {
        it('should build $warningEl', function() {
            sinon.collection.stub(app.template, 'getView', function() {
                return function(args) {
                    return 'testEl';
                }
            });

            view.createWarningEl();
            expect(view.$warningEl).toEqual('testEl');
        });
    });

    describe('bindDataChange()', function() {
        it('should call displayWarningAlert if hasWarningText and change is different than current', function() {
            sinon.collection.stub(view, 'displayWarningAlert', function() {});
            view.hasWarningText = true;
            view.currentOppsViewBySetting = 'Opportunities';
            view.bindDataChange();
            view.model.set('opps_view_by', 'RevenueLineItems');
            expect(view.displayWarningAlert).toHaveBeenCalled();
        });

        it('should not call displayWarningAlert if hasWarningText == false', function() {
            sinon.collection.stub(view, 'displayWarningAlert', function() {});
            view.hasWarningText = false;
            view.currentOppsViewBySetting = 'Opportunities';
            view.bindDataChange();
            view.model.set('opps_view_by', 'RevenueLineItems');
            expect(view.displayWarningAlert).not.toHaveBeenCalled();
        });

        it('should not call displayWarningAlert if changing to the same as current', function() {
            sinon.collection.stub(view, 'displayWarningAlert', function() {});
            view.hasWarningText = true;
            view.currentOppsViewBySetting = 'RevenueLineItems';
            view.bindDataChange();
            view.model.set('opps_view_by', 'RevenueLineItems');
            expect(view.displayWarningAlert).not.toHaveBeenCalled();
        });
    });

    describe('_render()', function() {
        it('should call appendWarning if hasWarningText == true', function() {
            sinon.collection.stub(view, '_super', function() {});
            sinon.collection.stub(view, 'appendWarning', function() {});
            view.hasWarningText = true;
            view._render();
            expect(view.appendWarning).toHaveBeenCalled();
        });

        it('should not call appendWarning if hasWarningText == false', function() {
            sinon.collection.stub(view, '_super', function() {});
            sinon.collection.stub(view, 'appendWarning', function() {});
            view.hasWarningText = false;
            view._render();
            expect(view.appendWarning).not.toHaveBeenCalled();
        });
    });

    describe('_updateTitleValues()', function() {
        it('should set this.titleSelectedValues', function() {
            view.model.set('opps_view_by', 'testValue');
            view._updateTitleValues();
            expect(view.titleSelectedValues).toBe('testValue');
        });
    });
});
