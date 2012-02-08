describe("DataManager", function() {
    var metadata;

    beforeEach(function() {
        // TODO: Put this into a fixture file once we have metadata format more or less stable
        metadata = {
            Teams: {
                primary_bean: "Team",
                beans:        {
                    Team: { vardefs: {} },
                    TeamSet: {}
                }
            },
            Contacts: {
                primary_bean: "Contact",
                beans:        {
                    Contact: {vardefs: {}}
                }
            },
            Leads:    {

                primary_bean: "Lead",
                beans:        {
                    Lead: {vardefs: {}}
                }
            }
        };
    });

    it("should be able to create an instance primary bean", function() {
        var dm = SUGAR.App.dataManager;
        dm.declareModels(metadata);

        _.each(_.keys(metadata), function(moduleName) {
            var bean = dm.createBean(moduleName, { someAttr: "Some attr value"});
            expect(bean.module).toEqual(moduleName);
            expect(bean.beanType).toEqual(metadata[moduleName].primary_bean);
            expect(bean.vardefs).toBeDefined();
            expect(bean.get("someAttr")).toEqual("Some attr value");
        });

    });
});