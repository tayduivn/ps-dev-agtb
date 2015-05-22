describe('View.Layouts.Base.SearchLayout', function() {
    var app, layout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        layout = SugarTest.createLayout('base', null, 'search');
        layout.initialize(layout.options);
        layout.initComponents();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        layout.context.clear();
        layout.dispose();
        layout = null;
    });

    describe('tags filters', function() {
        var sampleTag, stub;

        beforeEach(function(){
            sampleTag = {name: 'tag1', id: '1234'};
            layout.context.set('tags', [sampleTag]);
            stub = sinon.stub(layout, '_super');
        });

        afterEach(function() {
            stub.restore();
        });

        it('sets tag_filters in options on load', function() {
            var options = {};
            var setFields = false;
            layout.loadData(options, setFields);
            expect(options.apiOptions).toEqual({data:{tag_filters: [sampleTag.id]}, fetchWithPost: true});
        });

        it('adds tag_filters to search collection', function() {
            layout.search();
            expect(layout.collection.options.apiOptions.data.tag_filters).toEqual([sampleTag.id]);
        });

        it('adds tag_filters to filter collection', function() {
            layout.filter();
            expect(layout.collection.options.apiOptions.data.tag_filters).toEqual([sampleTag.id]);
        });
    });
});
