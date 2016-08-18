describe('ProductBundles.Base.Layouts.QuoteDataGroup', function() {
    var app;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        sinon.collection.stub(app.metadata, 'getView', function() {
            return {
                panels: [{
                    fields: [
                        'field1', 'field2', 'field3', 'field4'
                    ]
                }]
            };
        });

        layout = SugarTest.createLayout('base', 'ProductBundles', 'quote-data-group', null, null, true);
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
});
