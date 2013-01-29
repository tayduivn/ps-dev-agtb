describe("Subnav View", function() {

    var app, view;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
        var context = app.context.getContext();
        view = SugarTest.createView("base","Cases", "subnav", null, context);
        view.model = new Backbone.Model();
    });
    
    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view.model = null;
        view = null;
    });

    it("should add a tooltip if the window is too small to display the title", function() {
        jasmine.Clock.useMock();
        //add to the body otherwise offsetWidth and scrollWidth will be 0
        $('body').append(view.$el);
        var title = 'This is a very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very big title';
        //add a long title to the view
        $('<h1></h1>')
            .css({'text-overflow':'ellipsis','overflow':'hidden','white-space':'nowrap','visibility':'hidden'})
            .text(title).appendTo(view.$el);
        //call resize and wait because there is a setInterval
        view.resize();
        jasmine.Clock.tick(500);
        expect(view.$('h1').attr('data-original-title')).toEqual(title);
        //change for a very little title
        view.$('h1').text('t');
        view.resize();
        jasmine.Clock.tick(500);
        expect(view.$('h1').attr('data-original-title')).toBeUndefined();
        view.$el.remove();
    });
});
