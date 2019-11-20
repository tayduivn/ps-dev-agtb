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
describe('Base.View.Opportunites.MultiLineListView', function() {
    var view;
    var app;

    beforeEach(function() {
        view = SugarTest.createView('base', 'Opportunities', 'multi-line-list', null, null, true);
        app = SUGAR.App;
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
    });

    describe('setFilterDef', function() {
        var mockCollection;
        var options;
        beforeEach(function() {
            mockCollection = {
                whateverProp: 'whateverValue',
                setOption: sinon.collection.stub(),
            };
            options = {
                module: 'Cases',
                context: {
                    get: sinon.collection.stub(),
                    set: sinon.collection.stub(),
                },
            };
            options.context.get.withArgs('collection').returns(mockCollection);
        });

        it('should respect forecast set up', function() {

            sinon.collection.stub(app.metadata, 'getModule').returns({
                is_setup: 1,
                sales_stage_won: ['won1', 'won2'],
                sales_stage_lost: ['lost1']
            });
            view.setFilterDef(options);
            var expected = [{
                sales_status: {
                    $not_in: ['won1', 'won2', 'lost1']
                },
                $owner: ''
            }];
            expect(mockCollection.filterDef).toEqual(expected);
        });

        it('should fallback to default values when forecast is not set up', function() {
            sinon.collection.stub(app.metadata, 'getModule').returns({
                sales_stage_won: ['won1', 'won2'],
                sales_stage_lost: ['lost1']
            });
            view.setFilterDef(options);
            var expected = [{
                sales_status: {
                    $not_in: ['Closed Won', 'Closed Lost']
                },
                $owner: ''
            }];
            expect(mockCollection.filterDef).toEqual(expected);
        });

        it('should use filterDef in meta if exists', function() {
            options.meta = {
                filterDef: 'fake_filterDef'
            };
            view.setFilterDef(options);
            expect(mockCollection.filterDef).toEqual(options.meta.filterDef);
        });
    });
})
