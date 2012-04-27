describe("headerView", function() {
    var app;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        app = SugarTest.app;
    });

    it("should set current module", function() {
        var options = {
                context: {get: function() {
                    return 'cases';
                }},
                id: "1",
                template: function() {
                    return 'asdf';
                }
            },
            view = new SUGAR.App.view.views.HeaderView(options);
        view.setModuleInfo();
        expect(view.currentModule).toEqual('cases');
    });
    it("should set the current module list", function() {
        var result = fixtures.metadata.moduleList, options, view;
        delete result._hash;
        options = {
            context: {get: function() {
                return 'cases';
            }},
            id: "1",
            template: function() {
                return 'asdf';
            }
        };

        view = new SUGAR.App.view.views.HeaderView(options);
        view.setModuleInfo();
        expect(view.moduleList).toEqual(_.toArray(result));
    });
});
