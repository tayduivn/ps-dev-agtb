//FILE SUGARCRM flav=ent ONLY
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

describe('Opportunities.Base.Views.SubpanelList', function() {
    var app;
    var view;
    var layout;
    var recordListMeta;
    var subpanelMeta;

    beforeEach(function() {
        app = SUGAR.App;
        recordListMeta = {
            panels: [{
                fields: [
                    {name: 'one', type: 'record-list'},
                    {name: 'two', type: 'record-list-cascade', disable_field: 'exists'},
                    {name: 'three', type: 'record-list-cascade', disable_field: 'exists'},
                    {name: 'four', type: 'record-list'}
                ]
            }]
        };
        subpanelMeta = {
            panels: [{
                fields: [
                    {name: 'one', type: 'subpanel-list'},
                    {name: 'two', type: 'subpanel-list'},
                    {name: 'three', type: 'subpanel-list'},
                    {name: 'four', type: 'subpanel-list'}
                ]
            }]
        };
        sinon.collection.stub(app.metadata, 'getModule').returns({
            opps_view_by: 'RevenueLineItems'
        });
        layout = SugarTest.createLayout('base', 'Opportunities', 'subpanel');
        view = SugarTest.createView(
            'base',
            'Opportunities',
            'subpanel-list',
            subpanelMeta,
            null,
            true,
            layout);
    });

    afterEach(function() {
        sinon.collection.restore();
        view = null;
        app = null;
        recordListMeta = null;
        subpanelMeta = null;
    });

    describe('_getCascadeMeta', function() {
        it('should return only cascade fields with disable_field set', function() {
            var cascadeMeta = view._getCascadeMeta(recordListMeta);
            expect(cascadeMeta).toMatch({
                two: {name: 'two', type: 'record-list-cascade', disable_field: 'exists'},
                three: {name: 'three', type: 'record-list-cascade', disable_field: 'exists'}
            });
        });
    });

    describe('combineMeta', function() {
        it('should overwrite only the subpanel fields that need cascade metadata', function() {
            var combinedMeta = view.combineMeta(recordListMeta, subpanelMeta);
            expect(combinedMeta).toMatch({
                panels: [{
                    fields: [
                        {name: 'one', type: 'subpanel-list'},
                        {name: 'two', type: 'record-list-cascade', disable_field: 'exists'},
                        {name: 'three', type: 'record-list-cascade', disable_field: 'exists'},
                        {name: 'four', type: 'subpanel-list'}
                    ]
                }]
            });
        });
    });
});
