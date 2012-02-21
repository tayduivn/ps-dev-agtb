describe("Bean", function() {

    var dm = SUGAR.App.dataManager, metadata;

    beforeEach(function() {
        dm.reset();
        metadata = SugarTest.loadJson("metadata");
    });

    it("should be able to validate itself", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata[moduleName]);
        var bean = dm.createBean(moduleName, { first_name: "Super long first name"});

        var errors = bean.validate(bean.attributes);

        expect(errors).toBeDefined();

        var error;

        error = errors["first_name"];
        expect(error).toBeDefined();
        expect(error.length).toEqual(1);
        expect(error[0].maxLength).toBeDefined();

        error = errors["last_name"];
        expect(error).toBeDefined();
        expect(error.length).toEqual(1);
        expect(error[0].required).toBeDefined();

    });

    it("should be populated with defaults upon instantiation", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata[moduleName]);
        var bean = dm.createBean(moduleName);
        expect(bean.get("field_0")).toEqual(100);
    });

});