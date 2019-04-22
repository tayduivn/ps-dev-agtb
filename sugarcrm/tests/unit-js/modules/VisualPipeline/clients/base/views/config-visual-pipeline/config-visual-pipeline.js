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
describe('VisualPipeline.View.ConfigVisualPipelineView', function() {
    var app;
    var view;
    var layout;
    var context;
    var ctxModel;
    var meta;
    var parentLayout;

    beforeEach(function() {
        app = SUGAR.App;

        context = app.context.getContext();
        ctxModel = app.data.createBean('VisualPipeline');
        context.set('model', ctxModel);
        context.set('collection', app.data.createBeanCollection('VisualPipeline'));

        SugarTest.loadComponent('base', 'layout', 'config-drawer');
        parentLayout = SugarTest.createLayout('base', null, 'base');
        layout = SugarTest.createLayout('base', 'VisualPipeline', 'config-drawer', {},  context);
        layout.name = 'side-pane';
        layout.layout = parentLayout;

        view = SugarTest.createView('base', 'VisualPipeline', 'config-visual-pipeline', {}, context, true, layout);
        sinon.collection.stub(view, '_super', function() {});
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        var options;
        beforeEach(function() {
            options = {
                context: context
            };
            sinon.collection.stub(view, 'customizeMetaFields', function() {});
            view.initialize(options);
        });

        it('should call view._super method', function() {

            expect(view._super).toHaveBeenCalledWith('initialize', [options]);
        });

        it('should call view.customizeMetaFields method', function() {

            expect(view.customizeMetaFields).toHaveBeenCalled();
        });
    });

    describe('bindDataChange', function() {
        beforeEach(function() {
            sinon.collection.stub(view, 'render', function() {});
            sinon.collection.stub(view.collection, 'on', function() {});
            view.bindDataChange();
        });

        it('should call view.context.on with change', function() {

            expect(view.collection.on).toHaveBeenCalledWith('add remove reset');
        });
    });

    describe('render', function() {
        beforeEach(function() {
            sinon.collection.stub(view.context, 'get', function() {});
            sinon.collection.stub(view, '$', function() {
                return {
                    tabs: sinon.collection.stub()
                };
            });
            sinon.collection.stub(view.context, 'trigger', function() {});
            view.render();
        });

        it('should call view._super with render', function() {

            expect(view._super).toHaveBeenCalledWith('render');
        });

        it('should call view.$ method with #tabs', function() {

            expect(view.$).toHaveBeenCalledWith('#tabs');
        });

        it('should call view.context.trigger method with tabs-initialized', function() {

            expect(view.context.trigger).toHaveBeenCalledWith('pipeline:config:tabs-initialized');
        });
    });

    describe('customizeMetaFields', function() {
        describe('when field.twoColumns is true', function() {
            describe('when twoColumns has length of 2', function() {
                beforeEach(function() {
                    view.meta = {
                        panels: [{
                            fields: [
                                {
                                    twoColumns: true,
                                    name: 'test1'
                                },
                                {
                                    twoColumns: true,
                                    name: 'test2'
                                }
                            ]
                        }],
                        customizedFields: []
                    };
                    view.customizeMetaFields();
                });
                it('should push twoColumns to customizeFields', function() {

                    expect(view.meta.customizedFields).toEqual([
                        [{
                            twoColumns: true,
                            name: 'test1'
                        },
                        {
                            twoColumns: true,
                            name: 'test2'
                        }]
                    ]);
                });
            });
            describe('when twoColumns does not have length of 2', function() {
                beforeEach(function() {
                    view.meta = {
                        panels: [{
                            fields: [
                                {
                                    twoColumns: true,
                                    name: 'test1'
                                }
                            ]
                        }],
                        customizedFields: []
                    };
                    view.customizeMetaFields();
                });
                it('should not push twoColumns to customizeFields', function() {

                    expect(view.meta.customizedFields).toEqual([]);
                });
            });
        });
        describe('when field.twoColumns is not true', function() {
            beforeEach(function() {
                view.meta = {
                    panels: [{
                        fields: [
                            {
                                name: 'test1'
                            },
                            {
                                name: 'test2'
                            }
                        ]
                    }],
                    customizedFields: []
                };
                view.customizeMetaFields();
            });

            it('should push fields to customizeFields', function() {

                expect(view.meta.customizedFields).toEqual([
                    [{
                        name: 'test1'
                    }],
                    [{
                        name: 'test2'
                    }]
                ]);
            });
        });
    });
});

