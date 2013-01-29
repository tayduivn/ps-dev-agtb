describe("Base.Field.TextArea", function() {
    var app, field,
        fieldName = 'foo',
        shortText = '12345',
        langLblStub, template,
        moreText = 'more', lessText = 'less',
        longText = shortText + shortText;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        template = SugarTest.loadHandlebarsTemplate("textarea", "field", "base", "detail");
        SugarTest.testMetadata.set();
        field = SugarTest.createField("base",fieldName, "textarea", "detail");
        field.maxDisplayLength = shortText.length; //for testing

        langLblStub = sinon.stub(app.lang, 'get', function(label) {
            if (label === 'LBL_MORE') {
                return moreText;
            } else {
                return lessText;
            }
        });

    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
        langLblStub.restore();
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

    it('should not call show less if list view', function() {
        var spy = sinon.spy(app.view.Field.prototype, '_render');
        template = SugarTest.loadHandlebarsTemplate("textarea", "field", "base", "list");
        SugarTest.testMetadata.set();
        field = SugarTest.createField("base",fieldName, "textarea", "list");
        field.maxDisplayLength = shortText.length; //for testing
        field.model.set(fieldName, longText);
        field.initialize(field.options);
        field.render();
        // should be untruncated longtext since on a list view
        expect(field.$el.text().trim()).toEqual(longText);
        expect(spy).toHaveBeenCalled();
        spy.restore();
    });
    it('should return to last "more" or "less" state if coming from textarea edit mode', function() {
        field.lastMode = 'less';
        field.tplName  = 'edit';
        field.model.set(fieldName, longText);
        field.initialize(field.options);
        field.render();
        assertTruncated();
        field.lastMode = 'more';
        field.tplName  = 'edit';
        field.render();
        assertExpanded();
    });

    var assertTruncated = function() {
        expect(field.$('.show-more-text').length).toEqual(1);
        expect(field.isTruncated).toBeTruthy();
        expect(field.$el.text().trim()).toEqual(shortText + '...' + moreText);
    };

    var assertExpanded = function() {
        expect(field.$('.show-more-text').length).toEqual(1);
        expect(field.isTruncated).toBeFalsy();
        expect(field.$el.text().trim()).toEqual(longText + '...' + lessText);
    };
});