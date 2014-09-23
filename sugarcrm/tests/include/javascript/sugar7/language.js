describe('Sugar7 Language', function() {
    var app;
    beforeEach(function() {
        app = SugarTest.app;
    });
    afterEach(function() {
        app.cache.cutAll();
        sinon.collection.restore();
    });
    describe('Language select', function() {
        it('should toggle rtl class based on language changes', function() {
            sinon.collection.stub(app.lang, 'setLanguage', function(lang) {
                app.cache.set('lang', lang);
                app.user.setPreference('lang', lang);
                app.events.trigger('app:locale:change');
            });
            app.lang.setLanguage('en_us');
            expect($('html').hasClass('rtl')).toBeFalsy();

            //Only enable the rtl class when Hebrew is selected
            app.lang.setLanguage('he_IL');
            expect($('html').hasClass('rtl')).toBeTruthy();
        });
    });
});
