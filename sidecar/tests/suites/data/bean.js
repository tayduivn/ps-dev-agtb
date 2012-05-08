describe("Bean", function() {

    var dm = SUGAR.App.data, metadata;

    beforeEach(function() {
        dm.reset();
        metadata = SugarTest.loadFixture("metadata");
    });

    it("should be able to validate itself", function() {
        var moduleName = "Contacts", bean, error, errors;

        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, { first_name: "Super long first name"});
        bean.validateFlag = true;
        errors = bean.validate(bean.attributes);
        expect(errors).toBeDefined();
        error = errors["first_name"];
        expect(error).toBeDefined();
        expect(error.maxLength).toEqual(20);
    });

    it("should be populated with defaults upon instantiation", function() {
        var moduleName = "Contacts", bean;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName);
        expect(bean.get("field_0")).toEqual(100);
    });

    it("should be able to create a collection of related beans", function() {
        dm.declareModels(metadata);
        var opportunity = dm.createBean("Opportunities");
        opportunity.id = "opp-1";

        var contacts = opportunity.getRelatedCollection("contacts");

        expect(contacts.module).toEqual("Contacts");
        expect(contacts.link).toBeDefined();
        expect(contacts.link.name).toEqual("contacts");
        expect(contacts.link.bean).toEqual(opportunity);

        // Make sure we get the same instance (cached)
        expect(opportunity.getRelatedCollection("contacts")).toEqual(contacts);
    });


});
