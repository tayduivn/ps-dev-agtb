describe("Base.Field.TextArea", function() {
    var app, field,
        fieldName = 'foo',
        shortText = '12345',
        longText = shortText + shortText;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        template = SugarTest.loadHandlebarsTemplate("textarea", "field", "base", "detail");
        SugarTest.testMetadata.set();
        field = SugarTest.createField("base",fieldName, "textarea", "detail");
        field.maxDisplayLength = shortText.length; //for testing
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    it('short values should not have more link', function() {
        field.model.set(fieldName, shortText);
        field.initialize(field.options);
        field.render();
        expect(field.$('.show-more-text').length).toEqual(0);
        expect(field.isTruncated).toBeFalsy();
    });

    it('long values should have more link and text truncated with ellipse', function() {
        field.model.set(fieldName, longText);
        field.initialize(field.options);
        field.render();
        assertTruncated();
    });

    it('clicking on more link should show more text', function() {
        field.model.set(fieldName, longText);
        field.initialize(field.options);
        field.render();
        field.$('.show-more-text').trigger('click'); //click more
        assertExpanded();
    });

    it('clicking on less link should show less text', function() {
        field.model.set(fieldName, longText);
        field.initialize(field.options);
        field.render();
        field.$('.show-more-text').trigger('click'); //click more
        field.$('.show-more-text').trigger('click'); //click less
        assertTruncated();
    });

    var assertTruncated = function() {
        expect(field.$('.show-more-text').length).toEqual(1);
        expect(field.isTruncated).toBeTruthy();
        expect(field.$('.textarea-text').text()).toEqual(shortText + '...');
    };

    var assertExpanded = function() {
        expect(field.$('.show-more-text').length).toEqual(1);
        expect(field.isTruncated).toBeFalsy();
        expect(field.$('.textarea-text').text()).toEqual(longText);
    };
});