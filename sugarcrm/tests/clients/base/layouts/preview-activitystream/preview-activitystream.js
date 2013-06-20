describe("Preview Activity Stream", function() {
    var layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('preview-activitystream', 'layout', 'base');
        SugarTest.loadComponent('base', 'layout', 'preview-activitystream');
        SugarTest.loadComponent('base', 'layout', 'activitystream');
        SugarTest.testMetadata.addLayoutDefinition('preview-activitystream', {
            type: 'preview-activitystream'
        });
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        layout = SugarTest.createLayout('base', 'Contacts', 'preview-activitystream');
    });

    afterEach(function() {
        layout.dispose();
        SugarTest.testMetadata.dispose();
    });

    describe('Initialize', function() {
        it('Should not render any activities', function() {
            expect(layout.$('.activitystream-list').children().length).toBe(0);
        });
    });

    describe('fetchActivities()', function() {
        it('Should fetch a collection of activities', function() {
            var collectionStub = sinon.stub(layout.collection, 'fetch');
            layout.fetchActivities();
            expect(collectionStub.calledOnce).toBe(true);
            collectionStub.restore();
        });
    });
});
