describe("Base.Layout.DupeCheck", function() {
    var app, defaultMeta,
        moduleName = 'Contacts';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate("dupecheck-header", "view", "base", "dupecheck-header");
        SugarTest.loadComponent("base", "view", "dupecheck-header");
        SugarTest.testMetadata.addViewDefinition('list', {
            "panels":[
                {
                    "name":"panel_header",
                    "fields":[
                        {
                            "name":"name",
                            "label":"",
                            "placeholder":"LBL_LIST_NAME"
                        },
                        {
                            "name":"status",
                            "label":"",
                            "placeholder":"LBL_LIST_STATUS"
                        }
                    ]
                }
            ]
        }, moduleName);
        SugarTest.loadHandlebarsTemplate("baselist", "view", "base", "baselist");
        SugarTest.loadComponent('base', 'view', 'baselist');
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent("base", "view", "dupecheck-list");
        SugarTest.testMetadata.set();
        defaultMeta = {
            "type": "dupecheck",
            "components": [
                {"view":"dupecheck-header"},
                {"view":"dupecheck-list", "name":"dupecheck-list"}
            ]
        };
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        SugarTest.testMetadata.dispose();
    });

    it("should be able to switch list view", function(){
        //default initialize uses list view from viewdef
        var layout = SugarTest.createLayout("base", moduleName, "dupecheck", defaultMeta);
        expect(layout._components[1].name).toEqual(defaultMeta.components[1].name);

        //but if you set dupelisttype, the list view will be overridden.
        var expectedListView = 'dupecheck-list-select';
        defaultMeta.dupelisttype = expectedListView;
        layout = SugarTest.createLayout("base", moduleName, "dupecheck", defaultMeta);
        expect(layout._components[1].name).toEqual(expectedListView);
    });

    it("should be calling the duplicate check api", function() {
        var loadDataStub, ajaxStub;

        var layout = SugarTest.createLayout("base", moduleName, "dupecheck", defaultMeta);
        loadDataStub = sinon.stub(layout.context, 'loadData', function(options) {
            options.endpoint(options, {'success':$.noop})
        })
        ajaxStub = sinon.stub($, 'ajax', $.noop)
        layout.loadData();

        expect(ajaxStub.lastCall.args[0].url).toMatch(/.*\/Contacts\/duplicateCheck/);
        loadDataStub.restore();
        ajaxStub.restore();
    });

});