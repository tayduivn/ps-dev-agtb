describe("Twitter View", function() {

    var app, view, sinonSandbox;

    beforeEach(function() {
        sinonSandbox = sinon.sandbox.create();
        if (!$.fn.tooltip) {
            $.fn.tooltip = sinon.stub();
        }
        app = SugarTest.app;
        var context = app.context.getContext();
        view = SugarTest.createView("base","Home", "twitter", {}, context, true);
        view.model = new Backbone.Model();
        view.settings = new Backbone.Model();
        view.settings.set('twitter','test');
        SugarTest.clock.restore();
    });

    afterEach(function() {
        sinonSandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view.model = null;
        view = null;
    });

    it("should set date labels", function() {
        // workaround since dashlet config not testable atm
        view.meta.config = false;

        var rightNow = new Date();
        var twoDaysFromToday = new Date(rightNow.getTime()-(1000*60*60*24*3));
        var tweets = [{
            created_at: rightNow.toString(),
            text:'test1',
            user:{
                name:'test1'
            }
        },{
            created_at: twoDaysFromToday.toString(),
            text:'test1',
            user:{
                name:'test1'
            }
        }];
        SugarTest.seedFakeServer();

        SugarTest.server.respondWith("GET", /.*rest\/v10\/connector\/twitter\/currentUser.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify({})]);
        SugarTest.server.respondWith("GET", /.*rest\/v10\/connector\/twitter\/test.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify(tweets)]);


        view.loadData();
        SugarTest.server.respond();

        expect(view.tweets[0].timeLabel).toEqual('LBL_TIME_RELATIVE_TWITTER_SHORT');
        expect(view.tweets[1].timeLabel).toEqual('LBL_TIME_RELATIVE_TWITTER_LONG');
    });

    it("should set current user info", function() {
        // workaround since dashlet config not testable atm
        view.meta.config = false;

        SugarTest.seedFakeServer();

        SugarTest.server.respondWith("GET", /.*rest\/v10\/connector\/twitter\/currentUser.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify({
                screen_name : 'testName',
                profile_image_url: 'testURL'
            })]);
        SugarTest.server.respondWith("GET", /.*rest\/v10\/connector\/twitter\/test.*/,
            [200, { "Content-Type": "application/json"}, JSON.stringify([])]);


        view.loadData();
        SugarTest.server.respond();

        expect(view.current_twitter_user_name).toEqual('testName');
        expect(view.current_twitter_user_pic).toEqual('testURL');
    });

    it("should pull twitter field from config when context parent is null", function() {
        // workaround since dashlet config not testable atm
        view.meta.config = false;
        var apiStub = sinonSandbox.stub(SugarTest.app.api, 'call');
        var settingsStub = sinonSandbox.stub(view.settings, 'get', function(){return 'bob';});

        view.loadData();

        expect(apiStub.getCall(1).args[1].indexOf("bob")).toBeGreaterThan(-1);

        view.context.parent = new Backbone.Model();
        view.model.set('name','test');
        view.loadData();
        expect(apiStub.getCall(3).args[1].indexOf("bob")).toEqual(-1);
    });

    it("should pull twitter field from config when context parent is null", function() {
        // workaround since dashlet config not testable atm
        view.meta.config = false;
        var apiStub = sinonSandbox.stub(SugarTest.app.api, 'call');
        var settingsStub = sinonSandbox.stub(view.settings, 'get', function(){return 'bob';});
        var getConnectorStub = sinonSandbox.stub(view, "getConnector");
        var getUserStub = sinonSandbox.stub(app.user, "get");
        getConnectorStub.returns({"auth": 1});
        getUserStub.returns(1);

        view.loadData();

        expect(apiStub.getCall(1).args[1].indexOf("bob")).toBeGreaterThan(-1);

        view.context.parent = new Backbone.Model();
        view.model.set('name','test');
        view.loadData();
        expect(apiStub.getCall(3).args[1].indexOf("bob")).toEqual(-1);
    });
});
