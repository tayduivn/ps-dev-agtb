describe("Subnav View", function() {

    var app, view, clock, sinonSandbox;

    beforeEach(function() {
        sinonSandbox = sinon.sandbox.create();
        clock = sinon.useFakeTimers();
        if (!$.fn.tooltip) {
            $.fn.tooltip = sinon.stub();
        }
        app = SugarTest.app;
        var context = app.context.getContext();
        view = SugarTest.createView("base","Cases", "subnav", {}, context);
        view.model = new Backbone.Model();
    });

    afterEach(function() {
        sinonSandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view.model = null;
        view = null;
        clock.restore();
    });

    it("should add a tooltip if the window is too small to display the title", function() {
        //add to the body otherwise offsetWidth and scrollWidth will be 0
        $('body').append(view.$el);
        var title = 'This is a very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very very big title';
        //add a long title to the view
        $('<h1></h1>')
            .css({'text-overflow':'ellipsis','overflow':'hidden','white-space':'nowrap','visibility':'hidden'})
            .text(title).appendTo(view.$el);
        //call resize and wait because there is a setInterval
        view.resize();
        clock.tick(500);
        expect(view.$('h1').attr('data-original-title')).toEqual(title);
        //change for a very little title
        view.$('h1').text('t');
        view.resize();
        clock.tick(500);
        expect(view.$('h1').attr('data-original-title')).toBeUndefined();
        view.$el.remove();
    });
});
