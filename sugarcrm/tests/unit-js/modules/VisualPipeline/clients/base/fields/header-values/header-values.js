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

describe('VisualPipeline.Base.Fields.HeaderValuesField', function() {
    var app;
    var sandbox;
    var context;
    var model;
    var moduleName;
    var field;

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

        field = SugarTest.createField('base', 'header-values', 'header-values',
            'detail', {}, 'VisualPipeline', model, context, true);
    });

    afterEach(function() {
        sandbox.restore();
        sinon.collection.restore();
        app = null;
        context = null;
        model = null;
        field = null;
        moduleName = null;
    });

    describe('bindDataChange', function() {
        it('should call field.model.on method with change:table_header', function() {
            sinon.collection.stub(field.model, 'on', function() {});
            field.bindDataChange();

            expect(field.model.on).toHaveBeenCalledWith('change:table_header');
        });
    });

    describe('_render', function() {
        beforeEach(function() {
            sinon.collection.stub(field, 'populateHeaderValues');
            sinon.collection.stub(field, 'handleDraggableActions');
            sinon.collection.stub(field.context, 'set', function() {});
            sinon.collection.stub(field, '_super', function() {});
            field._render();
        });

        describe('when field.context is not empty', function() {
            it('should call field.context.set with selectedValues', function() {

                expect(field.context.set).toHaveBeenCalledWith('selectedValues', {});
            });
        });

        it('should call field.populateHeaderValues method', function() {

            expect(field.populateHeaderValues).toHaveBeenCalled();
        });

        it('should call field._super method wtih _render', function() {

            expect(field._super).toHaveBeenCalledWith('_render');
        });

        it('should call field.handleDraggableActions method', function() {

            expect(field.handleDraggableActions).toHaveBeenCalled();
        });
    });

    describe('populateHeaderValues', function() {
        beforeEach(function() {
            sinon.collection.stub(field, 'getBlackListedArray', function() {
                return ['Closed', 'New'];
            });

            sinon.collection.stub(field.model, 'set', function() {});
        });

        it('should call this.model.get with enabled_modules', function() {
            sinon.collection.stub(field.model, 'get').withArgs('enabled_module').returns(['Opportunities', 'Tasks'])
                .withArgs('table_header').returns('status')
                .withArgs('available_columns').returns({'Assigned': 'Assigned', 'Duplicate': 'Duplicate'});
            sinon.collection.stub(app.metadata, 'getModule')
                .withArgs(['Opportunities', 'Tasks'], 'fields')
                .returns({
                    status: {
                        name: 'status',
                        options: 'case_status_dom'
                    }
                });
            field.populateHeaderValues();

            expect(field.model.get).toHaveBeenCalledWith('enabled_module');
        });

        it('should call app.metadata.getModule with an array and fields', function() {
            sinon.collection.stub(field.model, 'get').withArgs('enabled_module').returns(['Opportunities', 'Tasks'])
                .withArgs('table_header').returns('status')
                .withArgs('available_columns').returns({'Assigned': 'Assigned', 'Duplicate': 'Duplicate'});
            sinon.collection.stub(app.metadata, 'getModule')
                .withArgs(['Opportunities', 'Tasks'], 'fields')
                .returns({
                    status: {
                        name: 'status',
                        options: 'case_status_dom'
                    }
                });
            field.populateHeaderValues();

            expect(app.metadata.getModule).toHaveBeenCalledWith(['Opportunities', 'Tasks'], 'fields');
        });

        describe('when tableHeader is empty', function() {
            beforeEach(function() {
                sinon.collection.stub(field.model, 'get').withArgs('enabled_module').returns(['Opportunities', 'Tasks'])
                    .withArgs('table_header').returns()
                    .withArgs('available_columns').returns({'Assigned': 'Assigned', 'Duplicate': 'Duplicate'});
                sinon.collection.stub(app.metadata, 'getModule')
                    .withArgs(['Opportunities', 'Tasks'], 'fields')
                    .returns({
                        status: {
                            name: 'status',
                            options: 'case_status_dom'
                        }
                    });
                field.populateHeaderValues();
            });

            it('should call this.model.set with empty whiteListed and blackListed values', function() {

                expect(field.model.set).toHaveBeenCalledWith({
                    'white_listed_header_vals': [],
                    'black_listed_header_vals': []
                });
            });

            it('should not call field.getBlackListArray method', function() {

                expect(field.getBlackListedArray).not.toHaveBeenCalled();
            });
        });

        describe('when tableHeader is not empty', function() {
            beforeEach(function() {
                sinon.collection.stub(field.model, 'get').withArgs('enabled_module').returns(['Opportunities', 'Tasks'])
                    .withArgs('table_header').returns('status')
                    .withArgs('available_columns').returns({'Assigned': 'Assigned', 'Duplicate': 'Duplicate'});
            });

            it('should call field.getBlackListArray method', function() {
                sinon.collection.stub(app.metadata, 'getModule')
                    .withArgs(['Opportunities', 'Tasks'], 'fields')
                    .returns({
                        status: {
                            name: 'status',
                            options: 'case_status_dom'
                        }
                    });
                field.populateHeaderValues();

                expect(field.getBlackListedArray).toHaveBeenCalled();
            });

            it('should call app.lang.getAppListStrings method', function() {
                sinon.collection.stub(app.metadata, 'getModule')
                    .withArgs(['Opportunities', 'Tasks'], 'fields')
                    .returns({
                        status: {
                            name: 'status',
                            options: 'case_status_dom'
                        }
                    });
                sinon.collection.stub(app.lang, 'getAppListStrings').returns(
                    {
                        'New': 'New',
                        'Assigned': 'Assigned',
                        'Duplicate': 'Duplicate',
                        'Lost': 'Lost'
                    }
                );
                field.populateHeaderValues();

                expect(app.lang.getAppListStrings).toHaveBeenCalledWith('case_status_dom');
            });

            describe('when table_header.option is empty', function() {
                beforeEach(function() {
                    sinon.collection.stub(app.metadata, 'getModule')
                        .withArgs(['Opportunities', 'Tasks'], 'fields')
                        .returns({
                            status: {
                                name: 'status',
                                options: ''
                            }
                        });
                    sinon.collection.stub(app.lang, 'getAppListStrings', function() {
                        return {};
                    });
                    field.populateHeaderValues();
                });

                it('should call this.model.set with empty whiteListed and blackListed values', function() {

                    expect(field.model.set).toHaveBeenCalledWith({
                        'white_listed_header_vals': [],
                        'black_listed_header_vals': []
                    });
                });
            });

            describe('when table_header.option is not empty', function() {
                beforeEach(function() {
                    sinon.collection.stub(app.metadata, 'getModule')
                        .withArgs(['Opportunities', 'Tasks'], 'fields')
                        .returns({
                            status: {
                                name: 'status',
                                options: ''
                            }
                        });
                    sinon.collection.stub(app.lang, 'getAppListStrings', function() {
                        return {
                            Assigned: 'Assigned',
                            Closed: 'Closed',
                            Duplicate: 'Duplicate',
                            New: 'New'
                        };
                    });
                    field.populateHeaderValues();
                });

                it('should call this.model.set with empty whiteListed and blackListed values', function() {

                    expect(field.model.set).toHaveBeenCalledWith({
                        'white_listed_header_vals': [
                            {
                                key: 'Assigned',
                                translatedLabel: 'Assigned'
                            },
                            {
                                key: 'Duplicate',
                                translatedLabel: 'Duplicate'
                            }
                        ],
                        'black_listed_header_vals': [
                            {
                                key: 'Closed',
                                translatedLabel: 'Closed'
                            },
                            {
                                key: 'New',
                                translatedLabel: 'New'
                            }
                        ]
                    });
                });
            });
        });
    });

    describe('handleDraggableActions', function() {
        it('should call this.$.sortable method', function() {
            sinon.collection.stub(jQuery.fn, 'sortable', function() {});
            field.handleDraggableActions();

            expect(jQuery.fn.sortable).toHaveBeenCalled();
        });
    });

    describe('getBlackListArray', function() {
        it('should call field.model.set with hidden_values', function() {
            sinon.collection.stub(field.model, 'get', function() {
                return ['Closed', 'New'];
            });
            field.getBlackListedArray();

            expect(field.model.get).toHaveBeenCalledWith('hidden_values');
        });

        describe('when there are no hidden values', function() {
            var res;
            it('should return an empty array', function() {
                sinon.collection.stub(field.model, 'get', function() {
                    return [];
                });
                res = field.getBlackListedArray();

                expect(res).toEqual([]);
            });
        });

        describe('when the hidden values are not an array', function() {
            var res;
            it('should still return the result as an array', function() {
                sinon.collection.stub(field.model, 'get', function() {
                    return '{"Closed": "Closed", "New": "New"}';
                });
                res = field.getBlackListedArray();

                expect(res).toEqual({'Closed': 'Closed', 'New': 'New'});
            });
        });
    });

    describe('_dispose', function() {
        beforeEach(function() {
            sinon.collection.stub(field.model, 'off', function() {});
            sinon.collection.stub(field, '_super', function() {});
            field._dispose();
        });

        it('should call view.model.off method with change:table_header', function() {

            expect(field.model.off).toHaveBeenCalledWith('change:table_header');
        });

        it('should call view._super with _dispose', function() {

            expect(field._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
