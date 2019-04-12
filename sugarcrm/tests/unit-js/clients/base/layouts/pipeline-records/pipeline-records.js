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

describe('Base.Layouts.PipelineRecords', function() {
    var app;
    var context;
    var layout;
    var options;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        context.prepare();

        options = {
            context: context,
        };

        sinon.collection.stub(app.metadata, 'getModule').withArgs('VisualPipeline', 'config').returns({
            enabled_modules: [
                'Cases',
                'Leads',
                'Opportunities',
                'Tasks',
            ]
        });

        layout = SugarTest.createLayout('base', 'Opportunities', 'pipeline-records', {}, context, false);
        layout.collection = app.data.createBeanCollection('Opportunities');
    });

    afterEach(function() {
        sinon.collection.restore();
        layout = null;
    });

    describe('initialize()', function() {
        it('should assign enabled modules to layout.pipelineModules', function() {
            layout.initialize(options);

            expect(layout.pipelineModules).toEqual(app.metadata.getModule('VisualPipeline', 'config').enabled_modules);
        });
    });

    describe('loadData', function() {
        var filter;

        beforeEach(function() {
            filter = {
                '$or': [
                    {
                        'assigned_user_id': {
                            '$equals': 'testId'
                        }
                    },
                ],
            };
            sinon.collection.stub(app.user, 'get', function() {
                return 'testId';
            });
            sinon.collection.spy(layout.collection, 'setOption');
            sinon.collection.spy(layout.collection, 'fetch');
            layout.loadData(options);
        });

        it('should set options for layout.collection', function() {
            expect(layout.collection.setOption).toHaveBeenCalledWith('filter', filter);
            expect(layout.collection.setOption).toHaveBeenCalledWith('params', {
                erased_fields: true, order_by: 'date_modified:DESC'
            });
            expect(layout.collection.setOption).toHaveBeenCalledWith('limit', 2);
            expect(layout.collection.fetch).toHaveBeenCalled();
        });
    });
});
