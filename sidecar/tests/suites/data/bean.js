describe("Bean", function() {

    var dm = SUGAR.App.dataManager, metadata;

    beforeEach(function() {
        dm.reset();
        metadata = SugarTest.loadJson("metadata");
    });

    it("should be able to validate itself", function() {
        var moduleName = "Contacts";
        dm.declareModel(moduleName, metadata[moduleName]);
        var bean = dm.createBean(moduleName, { first_name: "Super long first name", last_name: "Smith" });

        var result = bean.validate(bean.attributes);

        expect(result).toBeDefined();
        expect(result.length).toEqual(1);

        var error = result[0];
        expect(error.maxLength).toBeDefined();
        expect(error.attribute).toEqual("first_name");

    });

});