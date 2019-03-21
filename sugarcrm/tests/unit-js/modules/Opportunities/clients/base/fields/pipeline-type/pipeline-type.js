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

describe('Opportunities.Base.Fields.PipelineType', function() {
    var app;
    var sandbox;
    var context;
    var model;
    var moduleName;
    var field;
    var fieldDef;
    var config;
    var fields;
    var getStub;
    var getModStub;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        moduleName = 'Opportunities';
        model = app.data.createBean(moduleName, {
            id: '123test',
            name: 'Lórem ipsum dolor sit àmêt, ut úsu ómnés tatión imperdiet.'
        });
        context = new app.Context();
        context.set({model: model});

        fieldDef = {
            label: 'LBL_PIPELINE_TYPE',
            name: 'pipeline_type',
            type: 'pipeline-type'
        };

        config = {
            enabled_modules: ['Opportunities', 'Tasks'],
            is_setup: 1,
            records_per_column: 10,
            table_header: {Opportunities: 'sales_status', Tasks: 'status'},
            tile_header: {Opportunities: 'name', Tasks: 'name'}
        };
        fields = {
            date_closed: {
                vname: 'LBL_DATE_CLOSED',
            },
            sales_status: {
                vname: 'LBL_SALES_STATUS'
            }
        };

        getStub = sandbox.stub(app.lang, 'get');
        getModStub = sandbox.stub(app.lang, 'getModString');

        sinon.collection.stub(app.metadata, 'getModule').withArgs('VisualPipeline').returns(config)
            .withArgs('Opportunities').returns(fields);
        field = SugarTest.createField('base', 'pipeline-type', 'pipeline-type',
            'detail', fieldDef, 'Opportunities', model, context, true);
        sinon.collection.stub(field.context, 'get', function() {
            return {
                get: function() {
                    return 'testField';
                }
            };
        });
        field.getTabs();
    });

    afterEach(function() {
        sandbox.restore();
        sinon.collection.restore();
        getStub = null;
        getModStub = null;
        app = null;
        context = null;
        model = null;
        field = null;
        fieldDef = null;
        moduleName = null;
    });

    describe('getTabs()', function() {
        var metaObject;

        beforeEach(function() {
            metaObject = [
                {
                    'headerLabel': 'Time',
                    'moduleField': 'date_closed',
                    'tabLabel': 'Test String Time'
                },
                {
                    'headerLabel': 'Status',
                    'moduleField': 'sales_status',
                    'tabLabel': 'Test String Status'
                }
            ];
            getStub.withArgs('LBL_PIPELINE_VIEW_TAB_NAME', field.module).returns('Test String ');
            getModStub.withArgs('LBL_SALES_STATUS', field.module).returns('Status');
        });

        afterEach(function() {
            metaObject = null;
        });

        it('should push metaObject into field.tabs', function() {
            field.getTabs();
            expect(field.tabs).toEqual(metaObject);
        });
    });
});

