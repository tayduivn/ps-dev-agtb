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
    var panels;

    beforeEach(function() {
        view = SugarTest.createView('base', 'Cases', 'multi-line-list');
        app = SUGAR.App;
        panels = [
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
                            {'name': 'name_4', 'label': 'label_4', 'related_fields': ['name_5']},
                        ],
                    }
                ]
            }
        ];
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
    });

    describe('initialize', function() {
        it('should initialize with module-specified view metadata', function() {
            var initializedStub = sinon.collection.stub(view, '_super');
            var getStub = sinon.collection.stub().returns(true);
            var rowactions = {
                'actions': [
                    {
                        'type': 'rowaction',
                        'label': 'LBL_EDIT_IN_NEW_TAB',
                        'tooltip': 'LBL_EDIT_IN_NEW_TAB',
                        'event': 'list:editrow:fire',
                        'icon': 'fa-edit',
                        'acl_action': 'edit',
                    }
                ]
            };

            var getViewStub = sinon.collection.stub(app.metadata, 'getView');
            getViewStub.withArgs(null, 'multi-line-list')
                .returns({rowactions: rowactions});
            getViewStub.withArgs('Cases', 'multi-line-list')
                .returns({panels: panels});

            view.initialize({
                module: 'Cases',
                context: {get: getStub},
            });

            expect(initializedStub).toHaveBeenCalledWith('initialize', [{
                module: 'Cases',
                meta: {rowactions: rowactions, panels: panels},
                context: {get: getStub},
            }]);
        });

        it('should set skipFetch to false if its true', function() {
            sinon.collection.stub(view, '_super');
            sinon.collection.stub(app.metadata, 'getView');
            sinon.collection.stub(view, '_setCollectionOption');
            sinon.collection.stub(view, '_extractFieldNames');
            view.context = app.context.getContext();
            view.context.set('skipFetch', 'true');
            view.initialize({module: 'Cases'});
            expect(view.context.get('skipFetch')).toBeFalsy();
        });
    });

    describe('_extractFieldNames', function() {
        it('should return an array of fields', function() {
            var meta = {panels: panels};
            var actual = view._extractFieldNames(meta);
            var expected = ['name_1', 'name_2', 'name_3', 'name_4', 'name_5'];
            expect(actual).toEqual(expected);
        });
    });

    describe('rowactions', function() {
        var model;

        beforeEach(function() {
            model = app.data.createBean('Cases', {id: 'my_case_id'});
            app.routing.start();
        });

        afterEach(function() {
            app.router.stop();
            model = null;
        });

        it('should open record view in edit mode', function() {
            var buildRouteStub = sinon.collection.stub(app.router, 'buildRoute').returns('Cases/my_case_id/edit');
            var openStub = sinon.collection.stub(window, 'open');
            view.editClicked(model);
            expect(openStub).toHaveBeenCalledWith('#Cases/my_case_id/edit', '_blank');
        });

        it('should open record view in view mode', function() {
            var buildRouteStub = sinon.collection.stub(app.router, 'buildRoute').returns('Cases/my_case_id');
            var openStub = sinon.collection.stub(window, 'open');
            view.openClicked(model);
            expect(openStub).toHaveBeenCalledWith('#Cases/my_case_id', '_blank');
        });
    });

    describe('highlightRow', function() {
        var $el;
        var $row1;
        var $row2;
        beforeEach(function() {
            $el = view.$el;
            $row1 = $('<tr class="multi-line-row"></tr>');
            $row2 = $('<tr class="multi-line-row"></tr>');
            var $mainTable = $('<table><tbody></tbody></table>');
            $row1.appendTo($mainTable);
            $row2.appendTo($mainTable);
            view.$el = $mainTable;
        });

        afterEach(function() {
            view.$el = $el;
        });

        it('should highlight the clicked row', function() {
            view.highlightRow($row1);
            expect($row1.hasClass('current highlighted')).toBeTruthy();
            expect($row2.hasClass('current highlighted')).toBeFalsy();

            view.highlightRow($row2);
            expect($row2.hasClass('current highlighted')).toBeTruthy();
            expect($row1.hasClass('current highlighted')).toBeFalsy();
        });
    });

    describe('handleRowClick', function() {
        var $el;
        var target = 'targetValue';
        var event = {target: target};

        beforeEach(function() {
            $el = {
                closest: $.noop
            };
            sinon.collection.stub(view, '$').withArgs(target).returns($el);
            sinon.collection.stub(view, 'highlightRow');
        });

        afterEach(function() {
            $el = null;
        });

        it('should not take any action when event trigger by dropdown toggle', function() {
            var closestStub = sinon.collection.stub($el, 'closest');
            var getDrawerStub = sinon.collection.stub(view, '_getSideDrawer');
            sinon.collection.stub(view, 'isDropdownToggle').withArgs($el).returns(true);
            view.handleRowClick(event);

            // Method not try to get closest row model id to proceed further action
            expect(closestStub).not.toHaveBeenCalled();
            expect(getDrawerStub).not.toHaveBeenCalled();
        });

        it('should not take any action when any action dropdowns are open', function() {
            var closestStub = sinon.collection.stub($el, 'closest');
            var getDrawerStub = sinon.collection.stub(view, '_getSideDrawer');

            sinon.collection.stub(view, 'isDropdownToggle').withArgs($el).returns(false);
            sinon.collection.stub(view, 'isActionsDropdownOpen').returns(true);
            view.handleRowClick(event);

            // Method not try to get closest row model id to proceed further action
            expect(closestStub).not.toHaveBeenCalled();
            expect(getDrawerStub).not.toHaveBeenCalled();
        });

        describe('open drawer', function() {
            var model1;
            var model2;
            var layout;

            beforeEach(function() {
                model1 = app.data.createBean('Cases', {id: '1234'});
                model2 = app.data.createBean('Cases', {id: '9999'});
                view.collection = app.data.createBeanCollection('Cases', [model1, model2]);
                sinon.collection.stub(view, 'isDropdownToggle').withArgs($el).returns(false);
                sinon.collection.stub(view, 'isActionsDropdownOpen').returns(false);
                layout = {
                    setRowModel: sinon.collection.stub().returns(true)
                };
            });

            it('should open drawer when no existing drawer open', function() {
                sinon.collection.stub($el, 'closest').withArgs('.multi-line-row').returns({
                    data: sinon.collection.stub().withArgs('id').returns('1234')
                });
                var drawer = {
                    isOpen: function() {
                        return false;
                    },

                    open: sinon.collection.stub()
                };
                sinon.collection.stub(view, '_getSideDrawer').returns(drawer);

                view.handleRowClick(event);

                expect(drawer.open.lastCall.args[0].layout).toEqual('row-model-data');
                expect(drawer.open.lastCall.args[0].context.layout).toEqual('multi-line');
                expect(view.drawerModelId).toEqual('1234');
            });


            describe('clicking different row', function() {
                var drawer;

                beforeEach(function() {
                    view.drawerModelId = '9999';
                    sinon.collection.stub($el, 'closest').withArgs('.multi-line-row').returns({
                        data: sinon.collection.stub().withArgs('id').returns('1234')
                    });
                    drawer = {
                        isOpen: function() {
                            return true;
                        },
                        getComponent: function() {
                            return layout;
                        },
                        triggerBefore: function() {
                            return true;
                        },
                    };
                    sinon.collection.stub(view, '_getSideDrawer').returns(drawer);
                });

                it('should change model in context if different row is clicked', function() {
                    view.handleRowClick(event);

                    expect(layout.setRowModel).toHaveBeenCalled();
                    expect(view.drawerModelId).toEqual('1234');
                });

                it('should not change model in context if unsaved changes warning appears', function() {
                    sinon.collection.stub(drawer, 'triggerBefore').returns(false);

                    view.handleRowClick(event);

                    expect(layout.setRowModel).not.toHaveBeenCalled();
                    expect(view.drawerModelId).toEqual('9999');
                });
            });

            it('should not close existing drawer if same row is clicked', function() {
                view.drawerModelId = '1234';
                sinon.collection.stub($el, 'closest').withArgs('.multi-line-row').returns({
                    data: sinon.collection.stub().withArgs('id').returns('1234')
                });
                var drawer = {
                    isOpen: function() {
                        return true;
                    },
                    open: sinon.collection.stub(),
                    getComponent: function() {
                        return layout;
                    }
                };
                sinon.collection.stub(view, '_getSideDrawer').returns(drawer);

                view.handleRowClick(event);

                expect(drawer.open).not.toHaveBeenCalled();
                expect(layout.setRowModel).not.toHaveBeenCalled();
            });
        });
    });

    describe('addActions', function() {
        beforeEach(function() {
            view.leftColumns = [];
        });

        it('should not add field to leftColunms when meta is empty', function() {
            view.addActions(undefined);
            expect(view.leftColumns.length).toBe(0);
        });

        it('should not add field to leftColunms when rowactions is empty', function() {
            view.addActions({
                rowactions: undefined
            });
            expect(view.leftColumns.length).toBe(0);
        });

        it('should add field to leftColunms when meta', function() {
            var actions = ['action1', 'action2'];
            var cssClass = 'dummy_class';
            var label = 'LBL_DUMMY_LABLE';

            var expectedFieldMeta = {
                'type': 'fieldset',
                'css_class': 'overflow-visible',
                'fields': [
                    {
                        'type': 'rowactions',
                        'no_default_action': true,
                        'label': label,
                        'css_class': cssClass,
                        'buttons': actions
                    }
                ]
            };

            view.addActions({
                rowactions: {
                    actions: actions,
                    css_class: cssClass,
                    label: label
                }
            });

            expect(view.leftColumns.length).toBe(1);
            expect(view.leftColumns[0]).toEqual(expectedFieldMeta);
        });
    });

    describe('isActionDropdownOpen', function() {
        it('should return true when any elements match with selector', function() {
            var selector = '.fieldset.actions.list.btn-group.open';
            sinon.collection.stub(view, '$').withArgs(selector).returns({length: 1});

            expect(view.isActionsDropdownOpen()).toBe(true);
        });

        it('should return false when no element matchs with selector', function() {
            var selector = '.fieldset.actions.list.btn-group.open';
            sinon.collection.stub(view, '$').withArgs(selector).returns({length: 0});

            expect(view.isActionsDropdownOpen()).toBe(false);
        });
    });

    describe('isDropdownToggle', function() {
        it('should return true when element has the dropdown-toggle class', function() {
            var $el = {
                hasClass: sinon.collection.stub().withArgs('dropdown-toggle').returns(true)
            };

            expect(view.isDropdownToggle($el)).toBe(true);
        });

        it('should return true when any parents of element has the dropdown-toggle class', function() {
            var $el = {
                hasClass: sinon.collection.stub().withArgs('dropdown-toggle').returns(false),
                parent: sinon.collection.stub().returns({
                    hasClass: sinon.collection.stub().withArgs('dropdown-toggle').returns(true)
                })
            };

            expect(view.isDropdownToggle($el)).toBe(true);
        });

        it('should return false when neither the element nor its parents has the dropdown-toggle class', function() {
            var $el = {
                hasClass: sinon.collection.stub().withArgs('dropdown-toggle').returns(false),
                parent: sinon.collection.stub().returns({
                    hasClass: sinon.collection.stub().withArgs('dropdown-toggle').returns(false)
                })
            };

            expect(view.isDropdownToggle($el)).toBe(false);
        });
    });

    describe('updateDropdownDirection', function() {
        var $buttonGroup;
        var jQueryMock;
        var target = 'targetValue';
        var event = {currentTarget: target};

        beforeEach(function() {
            $buttonGroup = {
                height: sinon.collection.stub().returns(100),
                children: sinon.collection.stub().withArgs('ul').returns({
                    first: sinon.collection.stub().returns({
                        height: sinon.collection.stub().returns(100)
                    })
                }), // height of button group + children = 200
                offset: sinon.collection.stub(), // offset position determine dropup class
                toggleClass: sinon.collection.stub()
            };
            jQueryMock = sinon.collection.stub(view, '$');
            jQueryMock.withArgs('targetValue').returns({
                first: sinon.collection.stub().returns($buttonGroup)
            });
            sinon.collection.stub(window, '$').withArgs(window).returns({
                // windowHeight(865) - padding(65) = 800, making offset 600 as break point
                height: sinon.collection.stub().returns(865)
            });
        });

        afterEach(function() {
            $buttonGroup = null;
            jQueryMock = null;
        });

        it('should not update $buttonGroup with dropup class when dropdown menu not out of window', function() {
            $buttonGroup.offset.returns({top: 600});
            view.updateDropdownDirection(event);
            expect($buttonGroup.toggleClass).not.toHaveBeenCalled();
        });

        it('should update $buttonGroup with dropup class when dropdown menu would be out of window', function() {
            $buttonGroup.offset.returns({top: 601});
            view.updateDropdownDirection(event);
            expect($buttonGroup.toggleClass).toHaveBeenCalledWith('dropup');
        });
    });

    describe('_setCollectionOption', function() {
        var options;

        beforeEach(function() {
            options = {
                module: 'Cases',
                context: {
                    get: sinon.collection.stub(),
                    set: sinon.collection.stub(),
                },
            };
        });

        afterEach(function() {
            options = null;
        });

        it('should create and set collection on context', function() {
            var mockCollection = {whateverProp: 'whateverValue'};
            var createCollectionStub = sinon.collection.stub(app.data, 'createBeanCollection');
            options.context.get.withArgs('collection').returns(undefined);

            view._setCollectionOption(options);
            expect(createCollectionStub).toHaveBeenCalledWith('Cases');
            expect(options.context.set).toHaveBeenCalled();
        });

        it('should not set collection option and filterDef when not available', function() {
            var mockCollection = {
                whateverProp: 'whateverValue',
                setOption: sinon.collection.stub(),
            };
            options.context.get.withArgs('collection').returns(mockCollection);

            view._setCollectionOption(options);
            expect(mockCollection.setOption).not.toHaveBeenCalled();
            expect(mockCollection.filterDef).toBeUndefined();
        });

        it('should set collection option and filterDef when available', function() {
            var mockCollection = {
                whateverProp: 'whateverValue',
                setOption: sinon.collection.stub(),
            };
            options.context.get.withArgs('collection').returns(mockCollection);
            options.meta = {
                collectionOptions: {sampleProps: 'sampleValue'},
                filterDef: {fakeFilterDef: 'fakeFilterValue'},
            };

            view._setCollectionOption(options);
            expect(mockCollection.setOption).toHaveBeenCalledWith({sampleProps: 'sampleValue'});
            expect(mockCollection.filterDef).toEqual({fakeFilterDef: 'fakeFilterValue'});
        });
    });
});
