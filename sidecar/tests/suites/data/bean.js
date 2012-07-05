describe("Bean", function() {

    var app, dm, metadata;

    beforeEach(function() {
        app = SugarTest.app;
        dm = app.data;
        dm.reset();
        metadata = SugarTest.loadFixture("metadata");
    });

    it("should be able to validate itself", function() {
        var moduleName = "Opportunities", bean, error, errors;

        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, { account_name: "Super long account name"});
        errors = bean._doValidate();
        expect(errors).toBeDefined();
        error = errors["account_name"];
        expect(error).toBeDefined();
        expect(error.maxLength).toEqual(20);

        error = errors["name"];
        expect(error).toBeDefined();
        expect(error.required).toBeTruthy();

        var spy = sinon.spy();
        bean.on("error:validation:account_name", spy);
        bean.on("error:validation:name", spy);
        expect(bean.isValid()).toBeFalsy();
        expect(spy).toHaveBeenCalledTwice();
    });

    it("should be populated with defaults upon instantiation", function() {
        var moduleName = "Contacts", bean;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, { first_name: "John" });
        expect(bean.get("field_0")).toEqual(100);
        expect(bean.get("first_name")).toEqual("John");
    });

    it("should not be populated with defaults upon instantiation if the model exists", function() {
        var moduleName = "Contacts", bean;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName, { id: "xyz ", first_name: "John" });
        expect(bean.has("field_0")).toBeFalsy();
        expect(bean.get("first_name")).toEqual("John");
    });

    it("should be able to create a collection of related beans", function() {
        dm.declareModels(metadata.modules);
        var opportunity = dm.createBean("Opportunities");
        opportunity.id = "opp-1";

        var contacts = opportunity.getRelatedCollection("contacts");

        expect(contacts.module).toEqual("Contacts");
        expect(contacts.link).toBeDefined();
        expect(contacts.link.name).toEqual("contacts");
        expect(contacts.link.bean).toEqual(opportunity);
        expect(opportunity._relatedCollections["contacts"]).toEqual(contacts);

        // Make sure we get the same instance (cached)
        expect(opportunity.getRelatedCollection("contacts")).toEqual(contacts);
    });

    it("should skip validation upon save if fieldsToValidate param is not specified", function() {
        var moduleName = "Contacts", bean;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName);

        var mock = sinon.mock(bean);
        mock.expects("isValid").never();

        bean.save();
        mock.verify();
    });

    it("should not skip validation upon save if fieldsToValidate param is specified", function() {
        var moduleName = "Contacts", bean;
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        bean = dm.createBean(moduleName);

        var mock = sinon.mock(bean);
        mock.expects("isValid").once();

        bean.save(null, { fieldsToValidate: bean.fields });
        mock.verify();
    });


});
