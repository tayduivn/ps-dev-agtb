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
describe('RevenueLineItems.Base.Views.PanelTop', function() {
    var app;
    var view;
    var viewMeta;
    var viewLayoutModel;
    var layout;
    var layoutDefs;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        viewLayoutModel = new Backbone.Model();
        layoutDefs = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };
        layout = SugarTest.createLayout('base', 'RevenueLineItems', 'default', layoutDefs);

        SugarTest.loadComponent('base', 'view', 'panel-top');

        var parentContext = app.context.getContext();

        parentContext.set('module', 'Opportunities');
        context = app.context.getContext();
        context.parent = parentContext;

        app.routing.start();

        viewMeta = {
            panels: [{
                fields: ['field1', 'field2']
            }]
        };
        view = SugarTest.createView('base', 'RevenueLineItems', 'panel-top', viewMeta, context, true, layout);
        sinon.collection.stub(view, '_super', function() {});
        sinon.collection.stub(view, 'closestComponent', function() {
            return {
                cid: 'c37'
            };
        });
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
        app.router.stop();
    });

    describe('initialize()', function() {
        beforeEach(function() {
            sinon.collection.stub(view.context.parent, 'on', $.noop);
            sinon.collection.stub(app.controller.context, 'on', $.noop);
        });

        it('should listen on context parent for editablelist:save if parent module is Accounts', function() {
            view.parentModule = 'Accounts';
            view.initialize({});

            expect(view.context.parent.on).toHaveBeenCalledWith('editablelist:save');
        });

        describe('setting listener on app.controller.context', function() {
            it('should not set listener when user does not have access to Opps Edit', function() {
                sinon.collection.stub(app.user, 'getAcls', function() {
                    return {
                        Opportunities: {
                            edit: 'no'
                        },
                        RevenueLineItems: {}
                    };
                });
                view.initialize({});

                var viewDetails = view.closestComponent('record') ?
                    view.closestComponent('record') :
                    view.closestComponent('create');
                if (!_.isUndefined(viewDetails)) {
                    expect(app.controller.context.on)
                        .not
                        .toHaveBeenCalledWith(viewDetails.cid + ':productCatalogDashlet:add');
                }

            });

            it('should not set listener when user does not have access to RLIs', function() {
                sinon.collection.stub(app.user, 'getAcls', function() {
                    return {
                        Opportunities: {},
                        RevenueLineItems: {
                            access: 'no'
                        }
                    };
                });
                view.initialize({});

                var viewDetails = view.closestComponent('record') ?
                    view.closestComponent('record') :
                    view.closestComponent('create');
                if (!_.isUndefined(viewDetails)) {
                    expect(app.controller.context.on)
                        .not
                        .toHaveBeenCalledWith(viewDetails.cid + ':productCatalogDashlet:add');
                }

            });

            it('should not set listener when user does not have access to RLIs Edit', function() {
                sinon.collection.stub(app.user, 'getAcls', function() {
                    return {
                        Opportunities: {},
                        RevenueLineItems: {
                            edit: 'no'
                        }
                    };
                });
                view.initialize({});

                var viewDetails = view.closestComponent('record') ?
                    view.closestComponent('record') :
                    view.closestComponent('create');
                if (!_.isUndefined(viewDetails)) {
                    expect(app.controller.context.on)
                        .not
                        .toHaveBeenCalledWith(viewDetails.cid + ':productCatalogDashlet:add');
                }

            });

            it('should set listener when user has correct ACLs', function() {
                sinon.collection.stub(app.user, 'getAcls', function() {
                    return {
                        Opportunities: {},
                        RevenueLineItems: {}
                    };
                });
                view.initialize({});

                var viewDetails = view.closestComponent('record') ?
                    view.closestComponent('record') :
                    view.closestComponent('create');
                if (!_.isUndefined(viewDetails)) {
                    expect(app.controller.context.on)
                        .toHaveBeenCalledWith(viewDetails.cid + ':productCatalogDashlet:add');
                }
            });
        });
    });

    describe('createRelatedClicked()', function() {
        beforeEach(function() {
            sinon.collection.stub(app.alert, 'dismiss', $.noop);

            view.createRelatedClicked({});
        });

        it('should trigger list:massquote:fire on view layout', function() {
            expect(app.alert.dismiss).toHaveBeenCalledWith('opp-rli-create');
        });

        it('should call super createRelatedClicked', function() {
            expect(view._super).toHaveBeenCalledWith('createRelatedClicked');
        });
    });

    describe('openRLICreate()', function() {
        var linkModel;
        var prodData;

        beforeEach(function() {
            prodData = {
                base_rate: '0.75',
                currency_id: 'gbp-id-hash',
                product_template_name: 'ptName',
                product_template_id: 'ptId',
                discount_price: '100'
            };
            linkModel = app.data.createBean('RevenueLineItems');
            linkModel.fields = [{
                name: 'likely_case',
                type: 'currency'
            }, {
                name: 'best_case',
                type: 'currency'
            }, {
                name: 'worst_case',
                type: 'currency'
            }];
            sinon.collection.stub(view, 'createLinkModel', function() {
                return linkModel;
            });
            app.drawer = {
                close: $.noop,
                count: $.noop,
                reset: $.noop,
                open: $.noop
            };
            sinon.collection.stub(app.drawer, 'open', $.noop);
        });

        afterEach(function() {
            linkModel = null;
            prodData = null;
            delete app.drawer;
        });

        it('should call app.drawer.open', function() {
            sinon.collection.stub(app.router, 'getFragment', function() {
                return 'Opportunities/record';
            });
            sinon.collection.stub(app.drawer, 'count', function() {
                return 0;
            });
            view.openRLICreate(prodData);

            expect(app.drawer.open).toHaveBeenCalled();
        });

        it('should not call app.drawer.open if already in a drawer', function() {
            sinon.collection.stub(app.router, 'getFragment', function() {
                return 'Opportunities/record';
            });
            sinon.collection.stub(app.drawer, 'count', function() {
                return 1;
            });
            view.openRLICreate(prodData);

            expect(app.drawer.open).not.toHaveBeenCalled();
        });

        it('should not call app.drawer.open if this is create view', function() {
            sinon.collection.stub(app.router, 'getFragment', function() {
                return 'Opportunities/create';
            });
            view.openRLICreate(prodData);

            expect(app.drawer.open).not.toHaveBeenCalled();
        });

        it('should convert rli to users preferred currency if currency_create_in_preferred is set', function() {
            var result;

            sinon.collection.stub(app.router, 'getFragment', function() {
                return 'Opportunities/record';
            });
            sinon.collection.stub(app.user, 'getCurrency', function() {
                return {
                    currency_id: '-99',
                    currency_rate: '1.0',
                    currency_create_in_preferred: true
                };
            });
            view.openRLICreate(prodData);
            result = app.drawer.open.args[0][0].context.model.toJSON();

            expect(result.currency_id).toBe('-99');
            expect(result.base_rate).toBe('1.0');
            expect(result.likely_case).toBe('133.333333');
            expect(result.best_case).toBe('133.333333');
            expect(result.worst_case).toBe('133.333333');
        });
    });

    describe('rliCreateClose()', function() {
        beforeEach(function() {
            sinon.collection.stub(view.context, 'resetLoadFlag', $.noop);
            sinon.collection.stub(view.context, 'set', $.noop);
            sinon.collection.stub(view.context, 'loadData', $.noop);

            view.rliCreateClose(app.data.createBean('RevenueLineItems'));
        });

        it('should call context.resetLoadFlag', function() {
            expect(view.context.resetLoadFlag).toHaveBeenCalled();
        });

        it('should call context.set', function() {
            expect(view.context.set).toHaveBeenCalled();
        });

        it('should call context.loadData', function() {
            expect(view.context.loadData).toHaveBeenCalled();
        });
    });

    describe('_dispose()', function() {
        it('should call off on controller.context', function() {
            sinon.collection.stub(app.controller.context, 'off', $.noop);
            view._dispose();

            expect(app.controller.context.off).toHaveBeenCalled();
        });
    });
});
