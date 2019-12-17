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
        SugarTest.loadComponent('base', 'view', 'pipeline-recordlist-content');
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

            sinon.collection.stub(model, 'set', function() {});
            sinon.collection.stub(model, 'save', function() {});
            sinon.collection.stub(view, '_getFieldsToValidate');

            // Mock a successful validation of the model fields
            sinon.collection.stub(model, 'isValidAsync', function(fields, callback) {
                callback(true, {});
            });
        });

        describe('when pipeline_type is date_closed', function() {
            it('should set date_closed to the last day of the month selected', function() {
                sinon.collection.stub(jQuery.fn, 'parent', function() {
                    return {
                        data: function() {
                            return 'May 2019';
                        }
                    };
                });

                sinon.collection.stub(app.user, 'getPreference')
                    .withArgs('datepref').returns('YYYY-MM-DD');

                sinon.collection.stub(app.date, 'getUserDateFormat')
                    .returns('YYYY-MM-DD');

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

        describe('when pipeline_type is a dropdwon field in Opportunities', function() {
            it('should save the model', function() {
                var status = 'In Progress';
                sinon.collection.stub(jQuery.fn, 'parent', function() {
                    return {
                        data: function() {
                            return status;
                        }
                    };
                });

                view.saveModel(model, ui);
                expect(model.set).toHaveBeenCalledWith(view.headerField, status);
                expect(model.save).toHaveBeenCalled();
            });
        });
    });
});
