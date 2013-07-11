describe("Login View", function() {

    var view, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('login', {
            "panels": [
                {
                    "fields": [
                        {
                            "name": "username"
                        },
                        {
                            "name": "password"
                        }
                    ]
                }
            ]
        });
        SugarTest.testMetadata.set();
        view = SugarTest.createView("base", "Login", "login");
        app = SUGAR.App;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe("Declare Login Bean", function() {

        //Internet Explorer
        it("should have declared a Bean with the fields metadata", function() {
            expect(view.model.fields).toBeDefined();
            expect(_.size(view.model.fields)).toBeGreaterThan(0);
            expect(_.size(view.model.fields.username)).toBeDefined();
            expect(_.size(view.model.fields.password)).toBeDefined();
        });
    });

    describe("Browser support", function() {

        var alertStub, originalBrowser;

        beforeEach(function() {
            alertStub = sinon.stub(app.alert, "show");
            originalBrowser = $.browser;
        });

        afterEach(function() {
            $.browser = originalBrowser;
            alertStub.restore();
        });
        //Internet Explorer
        it("should deem IE8 as an unsupported browser", function() {
            $.browser = {
                'version': '8',
                'msie': true
            };
            expect(view._isSupportedBrowser()).toBeFalsy();
        });
        it("should deem IE9 as a supported browser", function() {
            $.browser = {
                'version': '9',
                'msie': true
            };
            expect(view._isSupportedBrowser()).toBeTruthy();
        });
        it("should deem IE10 as a supported browser", function() {
            $.browser = {
                'version': '10',
                'msie': true
            };
            expect(view._isSupportedBrowser()).toBeTruthy();
        });
        //Mozilla Firefox
        it("should deem Firefox 17 as an unsupported browser", function() {
            $.browser = {
                'version': '17',
                'mozilla': true
            };
            expect(view._isSupportedBrowser()).toBeFalsy();
        });
        it("should deem Firefox 18 as a supported browser", function() {
            $.browser = {
                'version': '18',
                'mozilla': true
            };
            expect(view._isSupportedBrowser()).toBeTruthy();
        });
        //Safari
        it("should deem Safari 5 as an unsupported browser", function() {
            $.browser = {
                'version': '533',
                'safari': true,
                'webkit': true
            };
            expect(view._isSupportedBrowser()).toBeFalsy();
        });
        it("should deem Safari 6 as a supported browser", function() {
            $.browser = {
                'version': '536',
                'safari': true,
                'webkit': true
            };
            expect(view._isSupportedBrowser()).toBeTruthy();
        });
        //Chrome
        it("should deem Chrome 21 as an unsupported browser", function() {
            $.browser = {
                'version': '536',
                'chrome': true,
                'webkit': true
            };
            expect(view._isSupportedBrowser()).toBeFalsy();
        });
        it("should deem Chrome 25 as a supported browser", function() {
            $.browser = {
                'version': '537.22',
                'chrome': true,
                'webkit': true
            };
            expect(view._isSupportedBrowser()).toBeTruthy();
        });
    });
})
;
