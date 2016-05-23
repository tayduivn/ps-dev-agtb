describe("BaseFooterLayout", function() {
    var layout;
    var app;
    var sandbox;

    beforeEach(function() {
        sandbox = sinon.sandbox.create();
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'footer');
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Users', 'footer', {});
    });

    afterEach(function() {
        sandbox.restore();
    });

    describe('render', function() {

        it('should load the logo url when re-rendering the layout', function() {

            layout.$el.html('<span data-metadata="logo">Footer fixture</span>');

            sandbox.stub(app.metadata, 'getLogoUrl', function() {
                return 'my_logo.jpg';
            });

            layout.render();

            expect(layout.$('[data-metadata="logo"]').attr('src')).toBe('my_logo.jpg');
        });
    });
});
