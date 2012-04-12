describe("headerView", function() {
    describe("should set current module", function() {
            var options = {
                context: {get: function() {
                    return 'cases'
                }},
                id: "1",
                template: function() {
                    return 'asdf'
                }
            };
            var view = new SUGAR.App.view.views.HeaderView(options);
            view.render();
            expect(view.currentModule).toEqual('cases');
        }
    ),
        describe("should set the current module list", function() {
                var result = fixtures.metadata.moduleList;
                delete result._hash;
                var options = {
                    context: {get: function() {
                        return 'cases'
                    }},
                    id: "1",
                    template: function() {
                        return 'asdf'
                    }
                };


                var view = new SUGAR.App.view.views.HeaderView(options);
                view.render();

                expect(view.moduleList).toEqual(_.toArray(result));
            }
        )
});