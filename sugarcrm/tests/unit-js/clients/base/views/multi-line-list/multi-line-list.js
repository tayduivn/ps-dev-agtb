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
describe('Base.View.MultiLineListView', function() {
    var view;
    var app;

    beforeEach(function() {
        view = SugarTest.createView('base', 'Cases', 'multi-line-list');
        app = SUGAR.App;
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
    });

    describe('initialize', function() {
        it('should initialize with module-specified view metadata', function() {
            var initializedStub = sinon.collection.stub(view, '_super');
            var panels = [
                {
                    'label': 'LBL_PANEL_1',
                    'fields': [
                        {
                            'name': 'case_number',
                            'label': 'LBL_LIST_NUMBER',
                            'subfields': [
                                {'name': 'name_1', 'label': 'label_1'},
                                {'name': 'name_2', 'label': 'label_2'},
                            ],
                        },
                        {
                            'name': 'status',
                            'label': 'LBL_STATUS',
                            'subfields': [
                                {'name': 'name_3', 'label': 'label_3'},
                                {'name': 'name_4', 'label': 'label_4'},
                            ],
                        }
                    ]
                }
            ];

            sinon.collection.stub(app.metadata, 'getView')
                .withArgs('Cases', 'multi-line-list')
                .returns({panels: panels});

            view.initialize({
                module: 'Cases',
            });

            expect(initializedStub).toHaveBeenCalledWith('initialize', [{
                module: 'Cases',
                meta: {panels: panels},
            }]);
        });
    });
});
