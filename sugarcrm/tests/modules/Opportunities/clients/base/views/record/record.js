//FILE SUGARCRM flav=ent ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

describe("Opportunities.Base.Views.Record", function() {
    var app, view, options, sinonSandbox;

    afterEach(function() {
        sinonSandbox.restore();
    });

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();
        options = {
            meta: {
                panels: [
                    {
                        fields: [
                            {
                                name: "name"
                            }
                        ]
                    }
                ]
            }
        };

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.testMetadata.set();
        SugarTest.seedMetadata(true, './fixtures');


        var context = app.context.getContext();

        var model = app.data.createBean('Opportunities'),
            tmpModel = new Backbone.Model();
        model.getRelatedCollection = function() { return tmpModel; };
        sinonSandbox.stub(tmpModel, 'fetch', function() {});
        context.set({
            model: model,
            module: 'Opportunities'
        });
        context.prepare();


        view = SugarTest.createView('base', 'Opportunities', 'record', options.meta, context, true);
    });

    describe('rliCreateClose', function() {
        afterEach(function() {
            view.context.children = [];
        });

        it('should find and call child context loadData method', function() {
            var rli_ctx = app.context.getContext();
            rli_ctx.set('module', 'RevenueLineItems');
            sinon.stub(rli_ctx, 'loadData', function() {});
            view.context.children.push(rli_ctx);

            view.rliCreateClose({});

            expect(rli_ctx.loadData).toHaveBeenCalled();

            rli_ctx.loadData.restore();
        });
    });

    describe('createLinkModel', function() {
        var parentModel, createBeanStub, relateFieldStub;

        beforeEach(function() {
            parentModel = new Backbone.Model({
                id: '101-model-id',
                name: 'parent product name',
                account_id: 'abc-111-2222',
                account_name: 'parent account name',
                assigned_user_name: 'admin'
            }),
                createBeanStub = sinonSandbox.stub(app.data, 'createRelatedBean', function() {
                    return new Backbone.Model();
                }),
                relateFieldStub = sinonSandbox.stub(app.data, 'getRelateFields', function() {
                    return [
                        {
                            name: 'product_template_name',
                            rname: 'name',
                            id_name: 'product_template_id',
                            populate_list: {
                                account_id: 'account_id',
                                account_name: 'account_name',
                                assigned_user_name: 'user_name'
                            }
                        }
                    ];
                });
        });
        afterEach(function() {
            parentModel = null;
        });

        it('should populate related fields when it creates linked record', function() {
            var newModel = view.createLinkModel(parentModel, 'blah');
            expect(newModel.get('product_template_id')).toBe(parentModel.get('id'));
            expect(newModel.get('product_template_name')).toBe(parentModel.get('name'));
            expect(newModel.get('account_id')).toBe(parentModel.get('account_id'));
            expect(newModel.get('account_name')).toBe(parentModel.get('account_name'));
            expect(newModel.get('user_name')).toBe(parentModel.get('assigned_user_name'));
        });
        it('should store the relate fields in default to keep the values for [Save and create new]', function() {
            var newModel = view.createLinkModel(parentModel, 'blah');
            expect(newModel.relatedAttributes['product_template_id']).toBe(parentModel.get('id'));
            expect(newModel.relatedAttributes['product_template_name']).toBe(parentModel.get('name'));
            expect(newModel.relatedAttributes['account_id']).toBe(parentModel.get('account_id'));
            expect(newModel.relatedAttributes['account_name']).toBe(parentModel.get('account_name'));
            expect(newModel.relatedAttributes['user_name']).toBe(parentModel.get('assigned_user_name'));
        });
    });


});
