describe("Theme Roller View", function() {

    var app, view;

    beforeEach(function() {
        if (!$.fn.colorpicker) {
            $.fn.colorpicker = function() {};
        }
        app = SugarTest.app;
        var context = app.context.getContext();
        view = SugarTest.createView("base","Cases", "themeroller", null, context);
    });
    
    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    it("should get input values and store them in the context", function() {
        $('<input>').attr({type:"text", name:"a", value:"aaaa"}).appendTo(view.$el);
        $('<input>').attr({type:"text", name:"b", value:"bbbb"}).appendTo(view.$el);
        $('<input>').attr({type:"text", name:"c", value:"cccc"}).appendTo(view.$el);
        $('<input>').attr({type:"text", name:"d", value:"dddd"}).appendTo(view.$el);
        $('<input>').attr({type:"text", name:"e", value:"eeee"}).addClass("bgvar").appendTo(view.$el);

        view.previewTheme();
        expect(view.context.get('colors')).toEqual({
            a: "aaaa",
            b: "bbbb",
            c: "cccc",
            d: "dddd",
            e: '"eeee"'
        });
    });

    it("should make right api call", function() {
        var url, platform = app.config.platform;
        $('<input>').attr({type:"text", name:"a", value:"aaaa"}).appendTo(view.$el);


        //Describe loadTheme
        var themeApiSpy = sinon.stub(app.api, "call");
        var showMessageSpy = sinon.stub(view, "showMessage");
        view.loadTheme();

        url = app.api.buildURL('theme', '', {}, {platform: platform, themeName: "default"});
        expect(themeApiSpy.lastCall.args[0]).toEqual("read");
        expect(themeApiSpy.lastCall.args[1]).toEqual(url);
        expect(themeApiSpy.lastCall.args[2]).toEqual({});

        //Describe saveTheme
        view.saveTheme();
        url = app.api.buildURL('theme', '', {}, {});
        expect(themeApiSpy.lastCall.args[0]).toEqual("create");
        expect(themeApiSpy.lastCall.args[1]).toEqual(url);
        expect(themeApiSpy.lastCall.args[2]).toEqual({a: "aaaa", platform: platform, themeName: "default"});

        //Describe resetTheme
        var alertStub = sinon.stub(app.alert, 'show', function(key, args) {
           args.onConfirm();
        });
        view.resetTheme();
        expect(themeApiSpy.lastCall.args[0]).toEqual("create");
        expect(themeApiSpy.lastCall.args[1]).toEqual(url);
        expect(themeApiSpy.lastCall.args[2]).toEqual({reset: true, platform: platform, themeName: "default"});

        //Restore stubs
        alertStub.restore();
        themeApiSpy.restore();
        showMessageSpy.restore();
    });


    it("should parse less vars and add an @ to relate variables", function() {
        view.lessVars = {
            rel: [
                {"name": "TheVar", value: "@TheRelatedVar"}
            ]
        };
        view.parseLessVars();
        expect(view.lessVars.rel[0].relname).toEqual("TheRelatedVar");
    });
});
