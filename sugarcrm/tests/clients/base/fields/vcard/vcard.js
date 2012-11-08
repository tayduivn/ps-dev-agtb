describe('vcard field', function() {

    var app;
    var field;

    beforeEach(function() {

        app = SugarTest.app;
        app.view.Field.prototype._renderHtml = function() {
        };

        field = SugarTest.createField('base', 'vcard_contact', 'vcard', 'detail');
    });

    afterEach(function() {

        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    it('should download the vcard of current record and log an error if uri is empty', function() {

        sinon.stub(field, '_loadTemplate', function() {
            this.template = function() {
                return '<i class="icon-download-alt"></i>';
            };
        });

        var error = sinon.spy(app.logger, 'error');

        var buildURLStub = sinon.stub(app.api, 'buildURL', function() {
            return '';
        });
        var callStub = sinon.stub(app.api, 'call', function(method, url, data, callbacks, options) {

            expect(callbacks.success).toBeDefined();
            expect(typeof callbacks.success === 'function').toBeTruthy();
            expect(callbacks.error).toBeDefined();
            expect(typeof callbacks.error === 'function').toBeTruthy();

            callbacks.success.apply(this);

            return null;
        });

        field.render();
        field._renderHtml();

        field.$('.icon-download-alt').trigger('click');
        expect(buildURLStub.called).toBeTruthy();
        expect(callStub.called).toBeTruthy();
        expect(error.calledOnce).toBeTruthy();

        field._loadTemplate.restore();
        error.restore();
        buildURLStub.restore();
        callStub.restore();
    });
});
