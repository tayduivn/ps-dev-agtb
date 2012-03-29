describe("Bean", function() {

    var dm = SUGAR.App.dataManager, metadata;

    beforeEach(function() {
        dm.reset();
        metadata = SugarTest.loadFixture("metadata");
    });

    it("should be able to validate itself", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var bean = dm.createBean(moduleName, { first_name: "Super long first name"});

        var errors = bean.validate(bean.attributes);

        expect(errors).toBeDefined();

        var error;

        error = errors["first_name"];
        expect(error).toBeDefined();
        expect(error.maxLength).toEqual(20);

        error = errors["last_name"];
        expect(error).toBeDefined();
        expect(error.required).toBeTruthy();

    });

    it("should be populated with defaults upon instantiation", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata.modules[moduleName]);
        var bean = dm.createBean(moduleName);
        expect(bean.get("field_0")).toEqual(100);
    });

});