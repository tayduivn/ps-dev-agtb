// FILE SUGARCRM flav=ent ONLY
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

describe('Opportunities.Base.Views.PipelineRecordlistContent', function() {
    var view;
    var app;
    var viewMeta;

    beforeEach(function() {
        app = SUGAR.App;
        var context = new app.Context({
            module: 'Opportunities',
            model: app.data.createBean('Opportunities'),
            layout: 'pipeline-records'
        });
        viewMeta = {
            fields: {
                label: 'LBL_PIPELINE_TYPE',
                name: 'pipeline_type',
                type: 'pipeline-type'
            }
        };
        view = SugarTest.createView(
            'base',
            'Opportunities',
            'pipeline-recordlist-content',
            viewMeta,
            context,
            true
        );

        sinon.collection.stub(view.context, 'on', function() {});
        sinon.collection.stub(view, '_super', function() {});
    });

    afterEach(function() {
        app.view.reset();
        view = null;
        sinon.collection.restore();
    });

    describe('saveModel', function() {
        var model;
        var ui;
        beforeEach(function() {
            view.headerField = 'testHeader';
            ui = {
                item: 'test'
            };
            model = app.data.createBean('Opportunities');
            sinon.collection.stub(jQuery.fn, 'parent', function() {
                return {
                    data: function() {
                        return 'May 2019';
                    }
                };
            });

            sinon.collection.stub(model, 'set', function() {});
            sinon.collection.stub(model, 'save', function() {});
        });

        it('should call getModule method', function() {
            sinon.collection.stub(app.metadata, 'getModule').withArgs('VisualPipeline', 'config').returns(
                {
                    table_header: {
                        Leads: 'status',
                        Opportunities: 'sales_status'
                    },
                    header_colors: ['#FFFFFF', '#000000']
                }
            ).withArgs(view.module, 'config').returns(
                {
                    opps_view_by: 'Opportunities'
                }
            );
            view.saveModel(model, ui);

            expect(app.metadata.getModule).toHaveBeenCalledWith(view.module, 'config');
        });

        describe('when pipeline_type is date_closed', function() {
            it('should set date_closed in the model', function() {
                sinon.collection.stub(app.metadata, 'getModule')
                    .withArgs(view.module, 'config').returns(
                    {
                        opps_view_by: 'Opportunities'
                    });
                sinon.collection.stub(view.context, 'get', function() {
                    return {
                        get: function() {
                            return 'date_closed';
                        }
                    };
                });
                view.saveModel(model, ui);

                expect(model.set).toHaveBeenCalledWith('date_closed', '2019-05-31');
            });
        });

        describe('when config.opps_view_by is Opportunities', function() {
            it('should save the model', function() {
                sinon.collection.stub(app.metadata, 'getModule')
                    .withArgs(view.module, 'config').returns(
                        {
                            opps_view_by: 'Opportunities'
                        });
                view.saveModel(model, ui);

                expect(model.save).toHaveBeenCalled();
            });
        });

        describe('when config.opps_view_by is not Opportunities', function() {
            beforeEach(function() {
                sinon.collection.stub(app.metadata, 'getModule')
                    .withArgs(view.module, 'config').returns(
                        {
                            opps_view_by: 'test'
                        });
                sinon.collection.stub(model, 'get')
                    .withArgs('id').returns('testId')
                    .withArgs('date_closed').returns('2019-05-31');
                sinon.collection.stub(app.api, 'buildURL', function() {
                    return 'testUrl';
                });
                sinon.collection.stub(app.api, 'call', function() {});
                view.saveModel(model, ui);
            });

            it('should call app.api.call with update, url and parameters', function() {

                expect(app.api.call).toHaveBeenCalledWith('update', 'testUrl', {
                    id: 'testId',
                    date_closed: '2019-05-31'
                });
            });
        });
    });
});
