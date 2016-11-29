describe('Activity Stream Bottom View', function() {
    var app, view, superStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('list-bottom', 'view', 'base');
        SugarTest.testMetadata.set();

        view = SugarTest.createView('base', 'Cases', 'activitystream-bottom');
        superStub = sinon.stub(view, '_super');
    });

    afterEach(function() {
        superStub.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
    });

    it('Should hide when there is no more data to fetch and collection is not empty', function() {
        view.collection = {
            next_offset: -1,
            length: 1,
            off: $.noop
        };

        view.render();

        expect(superStub.calledOnce).toBe(false);
        expect(view.$el.hasClass('hide')).toBe(true);
    });

    it('Should render and be visible when there is more data to fetch', function() {
        view.collection = {
            next_offset: 10,
            length: 10,
            off: $.noop
        };

        view.render();

        expect(superStub.calledOnce).toBe(true);
        expect(view.$el.hasClass('hide')).toBe(false);
    });

    it('Should render and be visible when there is no more data to fetch and collection is empty', function() {
        view.collection = {
            next_offset: -1,
            length: 0,
            off: $.noop
        };

        view.render();

        expect(superStub.calledOnce).toBe(true);
        expect(view.$el.hasClass('hide')).toBe(false);
    });
});
