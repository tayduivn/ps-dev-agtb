describe("Base.View.List", function () {
    var view, layout, app;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.testMetadata.addViewDefinition("list", {
            "panels":[
                {
                    "name":"panel_header",
                    "header":true,
                    "fields":["name", "case_number", "type", "created_by", "date_entered", "date_modified", "modified_user_id"]
                }
            ],
            last_state: {
                id: 'record-list'
            }
        }, "Cases");
        SugarTest.testMetadata.set();
        view = SugarTest.createView("base", "Cases", "list", null, null);
        layout = SugarTest.createLayout('base', "Cases", "list", null, null);
        view.layout = layout;
        app = SUGAR.App;
    });

    afterEach(function () {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe('parseFieldMetadata', function() {
        it('should parse fields set the correct align and width params', function() {
            var options = {};
            options.meta = {
                panels: [
                    {
                        fields: [
                            {
                                'name': 'test1',
                                'align': 'left'
                            },
                            {
                                'name': 'test2',
                                'align': 'center'
                            },
                            {
                                'name': 'test3',
                                'align': 'right'
                            },
                            {
                                'name': 'test4',
                                'align': 'invalid'
                            }
                        ]
                    },
                    {
                        fields: [
                            {
                                'name': 'test5',
                                'width': '20%'
                            },
                            {
                                'name': 'test6',
                                'width': '105%'
                            },
                            {
                                'name': 'test7',
                                'width': '105'
                            }
                        ]
                    }
                ]
            };
            options = view.parseFieldMetadata(options);

            expect(options.meta.panels).toEqual([
                    {
                        fields: [
                            {
                                'name': 'test1',
                                'align': 'tleft'
                            },
                            {
                                'name': 'test2',
                                'align': 'tcenter'
                            },
                            {
                                'name': 'test3',
                                'align': 'tright'
                            },
                            {
                                'name': 'test4',
                                'align': ''
                            }
                        ]
                    },
                    {
                        fields: [
                            {
                                'name': 'test5',
                                'width': '20%'
                            },
                            {
                                'name': 'test6',
                                'width': ''
                            },
                            {
                                'name': 'test7',
                                'width': ''
                            }
                        ]
                    }
                ]);
        });
    });
    it('should set the limit correctly if sorting and offset is already set', function() {
        var options1 = view.getSortOptions(view.collection);
        var offset = 5;
        expect(options1.offset).toBeUndefined();

        view.collection.offset = offset;

        var options2 = view.getSortOptions(view.collection);
        expect(options2.limit).toEqual(offset);
        expect(options2.offset).toEqual(0);

    });
    describe('setOrderBy', function() {

        var testElement = $('<th data-orderby="" data-fieldname="name" class="sorting_desc orderByname"><span>Name</span></th>');
        var event = {
            currentTarget: testElement
        };

        beforeEach(function() {
            view.$el.append(testElement);
        });
        afterEach(function() {
            view.$(testElement).remove();
        });

        it('should set orderby correctly', function() {
            view.setOrderBy(event);
            expect(view.orderBy).toEqual({field: 'name', direction: 'desc'});
        });
        it('should change direction when set order by active field', function() {
            view.setOrderBy(event);
            expect(view.orderBy.direction).toEqual('desc');
            view.setOrderBy(event);
            expect(view.orderBy.direction).toEqual('asc');

        });
        it('should set orderby correctly to collection', function() {
            view.setOrderBy(event);
            expect(view.collection.orderBy).toEqual({field: 'name', direction: 'desc'});
        });
    });

    describe('should use last state for store sorting', function() {

        it('should be orderby last state key not empty', function() {
            expect(view.orderByLastStateKey).not.toBeEmpty();
        });

        it('should call set last state when set order by', function() {
            var lastStateSetStub = sinon.stub(app.user.lastState, 'set');
            var testElement = $('<th data-orderby="" data-fieldname="name" class="sorting_desc orderByname"><span>Name</span></th>');
            var event = {
                currentTarget: testElement
            };
            view.$el.append(testElement);
            view.setOrderBy(event);

            expect(lastStateSetStub).toHaveBeenCalled();
            expect(lastStateSetStub.lastCall.args[1]).toEqual({field: 'name', direction: 'desc'});

            lastStateSetStub.restore();
        });

        it('should call get last state when initialize view', function() {
            var orderBy = {
                field: 'name',
                direction: 'desc'
            };
            var lastStateGetStub = sinon.stub(app.user.lastState, 'get', function(key) {
                return orderBy;
            });
            var testView = SugarTest.createView("base", "Cases", "list", null, null);

            expect(lastStateGetStub).toHaveBeenCalled();
            expect(testView.orderBy).toEqual(orderBy);
            expect(testView.collection.orderBy).toEqual(orderBy);

            lastStateGetStub.restore();
        })
    });
});
