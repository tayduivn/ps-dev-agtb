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
describe('Quotes.Base.Views.ProductCatalog', function() {
    var app;
    var view;
    var viewMeta;
    var context;
    var layout;
    var addClassStub;
    var removeClassStub;
    var showStub;
    var hideStub;
    var offStub;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        viewMeta = {
            config: false
        };
        layout = SugarTest.createLayout('base', 'Quotes', 'create', {});
        sinon.collection.stub(layout, 'closestComponent', function() {
            return {
                on: $.noop,
                off: $.noop
            };
        });
        view = SugarTest.createView('base', 'Quotes', 'product-catalog', viewMeta, context, true, layout);

        removeClassStub = sinon.collection.stub();
        addClassStub = sinon.collection.stub();
        showStub = sinon.collection.stub();
        hideStub = sinon.collection.stub();
        offStub = sinon.collection.stub();

        sinon.collection.stub(view, '$', function() {
            return {
                addClass: addClassStub,
                removeClass: removeClassStub,
                show: showStub,
                hide: hideStub,
                off: offStub
            };
        });
    });

    afterEach(function() {
        view.dispose();
        view = null;
        layout.dispose();
        layout = null;

        sinon.collection.restore();
        addClassStub = null;
        removeClassStub = null;
        showStub = null;
        hideStub = null;
        offStub = null;
    });

    describe('initialize()', function() {
        it('should set events for keyup product-catalog-search-term', function() {
            expect(view.events).toEqual({
                'keyup .product-catalog-search-term': 'onSearchTermChange'
            });
        });

        it('should set activeFetchCt to 0', function() {
            expect(view.activeFetchCt).toBe(0);
        });

        it('should set searchText to LBL_SEARCH_CATALOG_PLACEHOLDER', function() {
            expect(view.searchText).toBe('LBL_SEARCH_CATALOG_PLACEHOLDER');
        });

        it('should set dataLoaded to False', function() {
            expect(view.dataLoaded).toBeFalsy();
        });
    });

    describe('bindDataChange()', function() {
        beforeEach(function() {
            sinon.collection.stub(app.controller.context, 'on', function() {});
            view.bindDataChange();
        });

        it('should set an event listener on the context for productCatalogDashlet:add:complete', function() {
            expect(app.controller.context.on).toHaveBeenCalledWith('productCatalogDashlet:add:complete');
        });
    });

    describe('onSearchTermChange()', function() {
        beforeEach(function() {
            sinon.collection.stub(jQuery.fn, 'val', function() {
                return 'testTerm';
            });
            sinon.collection.stub(view, 'loadData', function() {});
        });

        it('should set previous search term to the current search term', function() {
            view.onSearchTermChange({});

            expect(view.previousSearchTerm).toBe('testTerm');
        });

        it('should call loadData with the current search term', function() {
            view.onSearchTermChange({});

            expect(view.loadData).toHaveBeenCalledWith({
                searchTerm: 'testTerm'
            });
        });

        it('should not call loadData twice if the same search term is used', function() {
            view.onSearchTermChange({});
            view.onSearchTermChange({});

            expect(view.loadData.calledOnce).toBeTruthy();
        });
    });

    describe('loadData()', function() {
        beforeEach(function() {
            sinon.collection.stub(app.api, 'buildURL', function(term) {
                return term;
            });
            sinon.collection.stub(app.api, 'call', function() {});
            sinon.collection.stub(view, 'toggleLoading', function() {});
            view.isConfig = false;
        });

        it('should hide the product catalog no results message', function() {
            view.loadData();

            expect(view.$).toHaveBeenCalledWith('.product-catalog-no-results');
            expect(addClassStub).toHaveBeenCalledWith('hidden');
        });

        it('should call toggleLoading with true, true', function() {
            view.loadData();

            expect(view.toggleLoading).toHaveBeenCalledWith(true);
        });

        it('should increment activeFetchCt', function() {
            view.activeFetchCt = 0;
            view.loadData();

            expect(view.activeFetchCt).toBe(1);
        });

        it('should add the search term to the url if used', function() {
            view.loadData({
                searchTerm: 'test1'
            });

            expect(app.api.call).toHaveBeenCalledWith('create', 'ProductTemplates/tree', {
                filter: 'test1'
            });
        });

        it('should add the search term to the url with encodeURI if used', function() {
            view.loadData({
                searchTerm: 'test%1'
            });

            expect(app.api.call).toHaveBeenCalledWith('create', 'ProductTemplates/tree', {
                filter: 'test%1'
            });
        });
    });

    describe('toggleLoading()', function() {
        it('should call show if startLoading is true', function() {
            view.toggleLoading(true);

            expect(view.$).toHaveBeenCalledWith('.loading-icon');
            expect(showStub).toHaveBeenCalled();
        });

        it('should call show if startLoading is false', function() {
            view.toggleLoading(false);

            expect(view.$).toHaveBeenCalledWith('.loading-icon');
            expect(hideStub).toHaveBeenCalled();
        });
    });

    describe('_onCatalogFetchSuccess()', function() {
        var responseData;
        var emptyResponseData;
        var checkBuildPhaserStub;
        var phaserObj;
        var dispatchStub;

        beforeEach(function() {
            checkBuildPhaserStub = sinon.collection.stub();
            dispatchStub = sinon.collection.stub();

            view.activeFetchCt = 1;
            responseData = {
                records: [{
                    id: 'record1'
                }],
                next_offset: -1
            };
            emptyResponseData = {
                records: [],
                next_offset: -1
            };
            phaserObj = {
                destroy: function() {},
                events: {
                    destroy: function() {},
                    onSetTreeData: {
                        dispatch: dispatchStub
                    }
                }
            };
            sinon.collection.stub(view, 'checkBuildPhaser', function() {});
        });

        afterEach(function() {
            responseData = null;
            emptyResponseData = null;
            checkBuildPhaserStub = null;
            dispatchStub = null;
            phaserObj = null;
            view.phaser = null;
        });

        it('should set jsTreeData', function() {
            view._onCatalogFetchSuccess(responseData);

            expect(view.jsTreeData.records[0].id).toBe('record1');
        });

        it('should decrement activeFetchCt', function() {
            view._onCatalogFetchSuccess(responseData);

            expect(view.activeFetchCt).toBe(0);
        });

        it('should show no results message if there are no results', function() {
            view._onCatalogFetchSuccess(emptyResponseData);

            expect(view.$).toHaveBeenCalledWith('.product-catalog-no-results');
            expect(removeClassStub).toHaveBeenCalledWith('hidden');
        });

        it('should hide no results if there are results', function() {
            view._onCatalogFetchSuccess(responseData);

            expect(view.$).toHaveBeenCalledWith('.product-catalog-no-results');
            expect(addClassStub).toHaveBeenCalledWith('hidden');
        });

        it('should show search terms if there are results', function() {
            view._onCatalogFetchSuccess(responseData);

            expect(view.$).toHaveBeenCalledWith('.product-catalog-search-term');
            expect(removeClassStub).toHaveBeenCalledWith('hidden');
        });

        it('should set dataLoaded to true', function() {
            view._onCatalogFetchSuccess(responseData);

            expect(view.dataLoaded).toBeTruthy();
        });

        it('should call removeClass with no data', function() {
            view._onCatalogFetchSuccess(responseData);

            expect(removeClassStub).toHaveBeenCalled();
        });

        it('should call checkBuildPhaser if phaser is undefined', function() {
            view._onCatalogFetchSuccess(responseData);

            expect(view.checkBuildPhaser).toHaveBeenCalled();
        });

        it('should call onSetTreeData if phaser is ready', function() {
            view.phaser = phaserObj;
            view._onCatalogFetchSuccess(responseData);

            expect(dispatchStub).toHaveBeenCalled();
        });
    });

    describe('checkBuildPhaser()', function() {
        beforeEach(function() {
            sinon.collection.stub(view, '_createPhaser', function() {});
        });

        it('should not call _createPhaser if only dataLoaded is true', function() {
            view.dataLoaded = true;
            view.phaserReady = false;
            view.checkBuildPhaser();

            expect(view._createPhaser).not.toHaveBeenCalled();
        });

        it('should not call _createPhaser if only phaserReady is true', function() {
            view.dataLoaded = false;
            view.phaserReady = true;
            view.checkBuildPhaser();

            expect(view._createPhaser).not.toHaveBeenCalled();
        });

        it('should call _createPhaser if both dataLoaded and phaserReady are true', function() {
            view.dataLoaded = true;
            view.phaserReady = true;
            view.checkBuildPhaser();

            expect(view._createPhaser).toHaveBeenCalled();
        });
    });

    describe('render', function() {
        beforeEach(function() {
            sinon.collection.stub(view, '_super', function() {});
            sinon.collection.stub(view, 'checkBuildPhaser', function() {});
        });

        it('should call checkBuildPhaser', function() {
            view.render();

            expect(view.checkBuildPhaser).toHaveBeenCalled();
        });
    });

    describe('_onMouseWheelChange()', function() {
        var mouseEvent;
        var phaserObj;
        var dispatchStub;
        var preventDefaultStub;

        beforeEach(function() {
            dispatchStub = sinon.collection.stub();
            preventDefaultStub = sinon.collection.stub();
            sinon.collection.stub(view, '_super', function() {});
            sinon.collection.stub(view, 'checkBuildPhaser', function() {});
            mouseEvent = {
                preventDefault: preventDefaultStub,
                originalEvent: {
                    deltaY: 4.71
                }
            };
            phaserObj = {
                destroy: function() {},
                events: {
                    destroy: function() {},
                    onScrollWheel: {
                        dispatch: dispatchStub
                    }
                }
            };
            view.phaser = phaserObj;
        });

        afterEach(function() {
            mouseEvent = null;
            preventDefaultStub = null;
            dispatchStub = null;
            phaserObj = null;
            view.phaser = null;
        });

        it('should call preventDefault on the incoming mouse event', function() {
            view._onMouseWheelChange(mouseEvent);

            expect(preventDefaultStub).toHaveBeenCalled();
        });

        it('should call preventDefault on the incoming mouse event', function() {
            view._onMouseWheelChange(mouseEvent);

            expect(dispatchStub).toHaveBeenCalledWith(4.71);
        });

        it('should send phaser the scroll delta for IE 11', function() {
            mouseEvent.type = 'mousewheel';
            mouseEvent.originalEvent.wheelDelta = 120;
            view._onMouseWheelChange(mouseEvent);

            expect(dispatchStub).toHaveBeenCalledWith(6);
        });
    });

    describe('onPhaserTreeReadyHandler()', function() {
        var phaserObj;
        var dispatchStub;
        var jsTreeData;

        beforeEach(function() {
            dispatchStub = sinon.collection.stub();
            jsTreeData = {
                records: [{
                    id: 'record1'
                }],
                next_offset: -1
            };
            phaserObj = {
                destroy: function() {},
                events: {
                    destroy: function() {},
                    onSetTreeData: {
                        dispatch: dispatchStub
                    }
                }
            };
            view.phaser = phaserObj;
        });

        afterEach(function() {
            phaserObj = null;
            dispatchStub = null;
            jsTreeData = null;
            view.phaser = null;
        });

        it('should dispatch to the phaser onSetTreeData event the jsTree data', function() {
            view.jsTreeData = jsTreeData;
            view.onPhaserTreeReadyHandler();

            expect(dispatchStub).toHaveBeenCalledWith(jsTreeData);
        });
    });

    describe('_fetchMoreRecords()', function() {
        beforeEach(function() {
            sinon.collection.stub(app.api, 'buildURL', function(term) {
                return term;
            });
            sinon.collection.stub(app.api, 'call', function() {});
            sinon.collection.stub(view, 'toggleLoading', function() {});
            view.isConfig = false;
        });

        it('should call toggleLoading with true, false', function() {
            view._fetchMoreRecords('record1', undefined, function() {});

            expect(view.toggleLoading).toHaveBeenCalledWith(true);
        });

        it('should increment activeFetchCt', function() {
            view.activeFetchCt = 0;
            view._fetchMoreRecords('record1', undefined, function() {});

            expect(view.activeFetchCt).toBe(1);
        });

        it('should add the offset to the url if used', function() {
            view._fetchMoreRecords('record1', 10, function() {});

            expect(app.api.call).toHaveBeenCalledWith('create', 'ProductTemplates/tree', {
                root: 'record1',
                offset: 10
            });
        });
    });

    describe('_fetchProductTemplate()', function() {
        beforeEach(function() {
            sinon.collection.stub(app.api, 'buildURL', function(id) {
                return 'ProductTemplates/' + id;
            });
            sinon.collection.stub(app.api, 'call', function() {});
            view._fetchProductTemplate('test', {});
        });

        it('should call app.api.buildURL', function() {
            expect(app.api.buildURL).toHaveBeenCalled();
        });

        it('should call app.api.call', function() {
            expect(app.api.call).toHaveBeenCalled();
        });
    });

    describe('_sendItemToQuote()', function() {
        var productTemplateData;

        beforeEach(function() {
            productTemplateData = {
                id: 'prodTemplateId',
                name: 'prodTemplateName',
                date_entered: 'yesterday',
                date_modified: 'today'
            };
            sinon.collection.stub(app.controller.context, 'trigger', function() {});

            view._sendItemToQuote(productTemplateData);
        });

        afterEach(function() {
            productTemplateData = null;
        });

        it('should add position 0', function() {
            expect(productTemplateData.position).toBe(0);
        });

        it('should add _forcePosition true', function() {
            expect(productTemplateData._forcePosition).toBeTruthy();
        });

        it('should add product_template_id as the template id', function() {
            expect(productTemplateData.product_template_id).toBe('prodTemplateId');
        });

        it('should add product_template_name as the template name', function() {
            expect(productTemplateData.product_template_name).toBe('prodTemplateName');
        });

        it('should remove id', function() {
            expect(productTemplateData.id).toBeUndefined();
        });

        it('should remove date_entered', function() {
            expect(productTemplateData.date_entered).toBeUndefined();
        });

        it('should remove date_modified', function() {
            expect(productTemplateData.date_modified).toBeUndefined();
        });

        it('should trigger context event', function() {
            expect(app.controller.context.trigger).toHaveBeenCalledWith('productCatalogDashlet:add');
        });
    });

    describe('_openItemInDrawer()', function() {
        beforeEach(function() {
            // todo add tests for this fn here
        });

        afterEach(function() {

        });

        it('', function() {

        });
    });

    describe('_onProductDashletAddComplete()', function() {
        beforeEach(function() {
            view.isFetchActive = true;
            view.cid = 'view123';

            view._onProductDashletAddComplete();
        });

        it('should set isFetchActive to false', function() {
            expect(view.isFetchActive).toBeFalsy();
        });

        it('should call view.$ with #product-catalog-container-view.cid', function() {
            expect(view.$).toHaveBeenCalledWith('#product-catalog-container-' + view.cid);
        });

        it('should call removeClass with disabled', function() {
            expect(removeClassStub).toHaveBeenCalledWith('disabled');
        });
    });

    describe('_dispose()', function() {
        var phaserDestroyStub;
        var eventsDestroyStub;

        beforeEach(function() {
            phaserDestroyStub = sinon.collection.stub();
            eventsDestroyStub = sinon.collection.stub();

            view.phaser = {
                destroy: phaserDestroyStub,
                events: {
                    destroy: eventsDestroyStub
                }
            };
            sinon.collection.stub(view, '_super', function() {});
        });

        afterEach(function() {
            phaserDestroyStub = null;
            eventsDestroyStub = null;
        });

        it('should call off to stop listening for the wheel event', function() {
            view.wheelEventName = 'wheel';
            view.cid = 'view123';
            view._dispose();

            expect(view.$).toHaveBeenCalledWith('.product-catalog-container-' + view.cid);
            expect(offStub).toHaveBeenCalledWith('wheel');
        });

        it('should call destroy on phaser.events and phaser', function() {
            view._dispose();

            expect(eventsDestroyStub).toHaveBeenCalled();
            expect(phaserDestroyStub).toHaveBeenCalled();
        });
    });
});
