describe('Quotes.Base.Views.QuoteDataGrandTotalsHeader', function() {
    var app;
    var view;
    var viewMeta;
    var viewModel;
    var ctx;

    beforeEach(function() {
        app = SugarTest.app;
        viewModel = new Backbone.Model({
            deal_tot: '100',
            new_sub: '50'
        });
        ctx = app.context.getContext();
        ctx.set('model', viewModel);
        viewMeta = {
            buttons: [
                {
                    type: 'quote-data-actiondropdown',
                    name: 'panel_dropdown',
                    no_default_action: true,
                    buttons: [
                        {
                            name: 'create_qli_button',
                            type: 'button',
                            icon: 'fa-plus',
                            label: 'LBL_CREATE_QLI_BUTTON_LABEL'
                        },
                        {
                            name: 'create_comment_button',
                            type: 'button',
                            icon: 'fa-plus',
                            label: 'LBL_CREATE_COMMENT_BUTTON_LABEL'
                        },
                        {
                            name: 'create_group_button',
                            type: 'button',
                            icon: 'fa-plus',
                            label: 'LBL_CREATE_GROUP_BUTTON_LABEL'
                        }
                    ]
                }
            ],
            panels: [{
                fields: [{
                    name: 'deal_tot',
                    label: 'LBL_DEAL_TOT'
                },
                {
                    name: 'new_sub',
                    label: 'LBL_NEW_SUB'
                }]
            }]
        };
        sinon.collection.stub(app.currency, 'formatAmountLocale', function() {
            return '$0.00';
        });
        view = SugarTest.createView('base', 'Quotes', 'quote-data-grand-totals-header', viewMeta, null, true);
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        it('should set className', function() {
            expect(view.className).toBe('quote-data-grand-totals-header-wrapper quote-totals-row');
        });

        it('should set panelFields', function() {
            expect(view.panelFields).toEqual([{
                name: 'deal_tot',
                label: 'LBL_DEAL_TOT',
                value: '$0.00'
            },
            {
                name: 'new_sub',
                label: 'LBL_NEW_SUB',
                value: '$0.00'
            }]);
        });

        it('should set panelFieldNames', function() {
            expect(view.panelFieldNames).toEqual(['deal_tot', 'new_sub']);
        });

        it('should set panelFieldsObj', function() {
            expect(view.panelFieldsObj).toEqual({
                deal_tot: {
                    name: 'deal_tot',
                    label: 'LBL_DEAL_TOT',
                    value: '$0.00'
                },
                new_sub: {
                    name: 'new_sub',
                    label: 'LBL_NEW_SUB',
                    value: '$0.00'
                }
            });
        });
    });

    describe('_onCreateGroupBtnClicked()', function() {
        it('should trigger quotes:group:create event', function() {
            sinon.collection.spy(view.context, 'trigger');
            view._onCreateGroupBtnClicked();

            expect(view.context.trigger).toHaveBeenCalledWith('quotes:group:create');
        });
    });
});
