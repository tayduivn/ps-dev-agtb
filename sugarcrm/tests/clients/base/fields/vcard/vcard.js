describe('vcard field', function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.testMetadata.set();
        field = SugarTest.createField("base", 'vcard', "vcard", "vcard");
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.model = null;
        field = null;
    });

    it('should download the vcard of current record and log an error if uri is empty', function () {
        var error, buildURLStub, callStub;

        sinon.stub(field, '_loadTemplate', function() {
            this.template = function() {
                return '<a class="btn" href="javascript:void(0);"></a>';
            };
        });

        error = sinon.spy(SugarTest.app.logger, 'error');
        buildURLStub = sinon.stub(SugarTest.app.api, 'buildURL', function () {
            return '';
        });

        callStub = sinon.stub(SugarTest.app.api, 'call', function (method, url, data, callbacks, options) {
            expect(callbacks.success).toBeDefined();
            expect(typeof callbacks.success === 'function').toBeTruthy();
            expect(callbacks.error).toBeDefined();
            expect(typeof callbacks.error === 'function').toBeTruthy();

            callbacks.success.apply(this);

            return null;
        });

        field.rowActionSelect();

        expect(buildURLStub.called).toBeTruthy();
        expect(callStub.called).toBeTruthy();
        expect(error.calledOnce).toBeTruthy();

        error.restore();
        buildURLStub.restore();
        callStub.restore();
    });
});
