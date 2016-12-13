describe('Quotes.Base.Views.QuoteDataListHeader', function() {
    var app;
    var view;
    var viewMeta;
    var metaPanels;
    var layout;
    var layoutDefs;

    beforeEach(function() {
        app = SugarTest.app;

        viewMeta = {
            selection: {
                type: 'multi',
                actions: [
                    {
                        name: 'edit_row_button',
                        type: 'button'
                    },
                    {
                        name: 'delete_row_button',
                        type: 'button'
                    }
                ]
            }
        };

        layoutDefs = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };
        layout = SugarTest.createLayout('base', 'Quotes', 'default', layoutDefs);
        layout.isCreateView = false;
        layout.moveMassCollectionItemsToNewGroup = function() {};

        metaPanels = [{
            fields: [
                'field1', 'field2', 'field3', 'field4'
            ]
        }];

        sinon.collection.stub(app.metadata, 'getView', function() {
            return {
                panels: metaPanels
            };
        });

        view = SugarTest.createView('base', 'Quotes', 'quote-data-list-header', viewMeta, null, true, layout);
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
        layout.dispose();
        layout = null;
        metaPanels = null;
        viewMeta = null;
    });

    describe('initialize()', function() {
        it('should set className', function() {
            expect(view.className).toBe('quote-data-list-header');
        });

        it('should set this.meta.panels', function() {
            expect(view.meta.panels).toEqual(metaPanels);
        });

        it('should set this._fields', function() {
            expect(view._fields).toEqual(_.flatten(_.pluck(metaPanels, 'fields')));
        });

        describe('when on create view', function() {
            var initMeta;
            beforeEach(function() {
                layout.isCreateView = true;
                initMeta = {
                    meta: viewMeta,
                    layout: layout
                };

                view.dispose();
                view.initialize(initMeta);
            });

            afterEach(function() {
                initMeta = null;
            });

            it('should clear out left column select buttons', function() {
                expect(view.leftColumns[0].fields.length).toBe(0);
            });
        });
    });

    describe('bindDataChange()', function() {
        var massCollection;

        beforeEach(function() {
            massCollection = new Backbone.Collection();
            view.massCollection = massCollection;
            sinon.collection.spy(view.massCollection, 'reset');
        });

        afterEach(function() {
            massCollection = null;
        });

        it('should reset mass collection if it exists', function() {
            view.bindDataChange();

            expect(view.massCollection.reset).toHaveBeenCalled();
        });
    });

    describe('_onCreateGroupBtnClicked()', function() {
        var massCollection;

        beforeEach(function() {
            massCollection = new Backbone.Collection();
            view.massCollection = massCollection;

            sinon.collection.stub(view.context, 'on', function() {});
            sinon.collection.stub(view.context, 'trigger', function() {});
            sinon.collection.stub(app.alert, 'show', function() {});
        });

        afterEach(function() {
            massCollection = null;
        });

        it('should trigger events on context if massCollection has items', function() {
            massCollection.add(app.data.createBean('Products'));
            view._onCreateGroupBtnClicked({});

            expect(view.context.on).toHaveBeenCalledWith('quotes:group:create:success');
            expect(view.context.trigger).toHaveBeenCalledWith('quotes:group:create');
        });

        it('should display an alert if massCollection is empty', function() {
            view._onCreateGroupBtnClicked({});

            expect(app.alert.show).toHaveBeenCalledWith('quote_grouping_message');
        });
    });

    describe('_onNewGroupedItemsCreateSuccess()', function() {
        beforeEach(function() {
            sinon.collection.stub(view.context, 'off', function() {});
            sinon.collection.stub(view.layout, 'moveMassCollectionItemsToNewGroup', function() {});

            view._onNewGroupedItemsCreateSuccess({});
        });

        afterEach(function() {

        });

        it('should call context.off quotes:group:create:success', function() {
            expect(view.context.off).toHaveBeenCalledWith('quotes:group:create:success');
        });

        it('should call layout.moveMassCollectionItemsToNewGroup', function() {
            expect(view.layout.moveMassCollectionItemsToNewGroup).toHaveBeenCalled();
        });
    });

    describe('_dispose()', function() {
        beforeEach(function() {
            sinon.collection.stub(view.context, 'off', function() {});
            sinon.collection.stub(view, '_super', function() {});

            view._dispose();
        });

        it('should call context.off quotes:group:create:success', function() {
            expect(view.context.off).toHaveBeenCalledWith('quotes:group:create:success');
        });

        it('should call _super', function() {
            expect(view._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
