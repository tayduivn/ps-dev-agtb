describe('ProductBundles.Base.Layouts.QuoteDataGroup', function() {
    var app;
    var layout;
    var layoutModel;
    var layoutContext;
    var layoutGroupId;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        layoutGroupId = 'layoutGroupId1';
        layoutModel = new Backbone.Model({
            id: layoutGroupId,
            product_bundle_items: new Backbone.Collection([
                {id: 'test1', _module: 'Products', position: 0},
                {id: 'test2', _module: 'Products', position: 1},
                {id: 'test3', _module: 'Products', position: 2}
            ])
        });

        layoutContext = app.context.getContext();
        layoutContext.set({
            module: 'ProductBundles',
            model: layoutModel
        });

        sinon.collection.stub(app.metadata, 'getView', function() {
            return {
                panels: [{
                    fields: [
                        'field1', 'field2', 'field3', 'field4'
                    ]
                }]
            };
        });

        layout = SugarTest.createLayout('base', 'ProductBundles', 'quote-data-group', null, layoutContext, true);
        sinon.collection.stub(layout, '_super', function() {});
    });

    afterEach(function() {
        sinon.collection.restore();
        layout.dispose();
        layout = null;
    });

    describe('initialize()', function() {
        var lastCall;

        it('should have className', function() {
            expect(layout.className).toBe('quote-data-group');
        });

        it('should have tagName', function() {
            expect(layout.tagName).toBe('tbody');
        });

        it('should set the groupId from the model ID', function() {
            expect(layout.groupId).toBe(layoutGroupId);
        });

        it('should set this.collection to be the product_bundle_items', function() {
            expect(layout.collection).toBe(layoutModel.get('product_bundle_items'));
        });

        it('should call app.metadata.getView with first param Products module', function() {
            layout.initialize({});
            lastCall = app.metadata.getView.lastCall;
            expect(lastCall.args[0]).toBe('Products');
        });

        it('should call app.metadata.getView with second param quote-data-group-list', function() {
            layout.initialize({});
            lastCall = app.metadata.getView.lastCall;
            expect(lastCall.args[1]).toBe('quote-data-group-list');
        });

        it('should set listColSpan if metadata exists', function() {
            layout.initialize({});
            expect(layout.listColSpan).toBe(4);
        });
    });

    describe('bindDataChange()', function() {
        it('will subscribe the model to listen for a change on product_bundle_items and call render', function() {
            sinon.collection.stub(layout.model, 'on');
            layout.bindDataChange();
            expect(layout.model.on).toHaveBeenCalledWith('change:product_bundle_items', layout.render, layout);
        });
    });

    describe('_render()', function() {
        var $elAttrSpy;
        var $attrSpy;
        var $oldEl;

        beforeEach(function() {
            $elAttrSpy = sinon.collection.spy();
            $attrSpy = sinon.collection.spy();

            $oldEl = layout.$el;
            layout.$el = {
                attr: $elAttrSpy
            };

            sinon.collection.stub(layout, '$', function() {
                return {
                    attr: $attrSpy
                };
            });

            layout._render();
        });

        afterEach(function() {
            $elAttrSpy = null;
            $attrSpy = null;
            delete layout.$el.attr;
            layout.$el = $oldEl;
            $oldEl = null;
        });

        it('should call super _render', function() {
            expect(layout._super).toHaveBeenCalledWith('_render');
        });

        it('should call $el.attr and set the data-group-id to the groupId', function() {
            expect($elAttrSpy).toHaveBeenCalledWith('data-group-id', layoutGroupId);
        });

        it('should call this.$ to get the table rows', function() {
            expect(layout.$).toHaveBeenCalledWith('tr.quote-data-group-list');
        });

        it('should call $.attr and set the data-group-id to the groupId', function() {
            expect($attrSpy).toHaveBeenCalledWith('data-group-id', layoutGroupId);
        });
    });
});
