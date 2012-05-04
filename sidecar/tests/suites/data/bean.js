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
        errors = bean.validate(bean.attributes);
        expect(errors).toBeDefined();
        error = errors["first_name"];
        expect(error).toBeDefined();
        expect(error.maxLength).toEqual("ERROR: The max number of characters for this field is 20");
    });

    it("should trigger validation errors on model if errors exist", function(){
        var moduleName = "Contacts", bean, error, errors;
        var triggerFn = function(error){
        };
        var triggerSpy = sinon.spy(triggerFn);
        var errors = {first_name: "this is some error string"};


        dm.declareModel(moduleName, metadata.modules[moduleName]);

        bean = dm.createBean(moduleName, { first_name: "Super long first name"});
        bean.on("model.validation.disableSave", triggerSpy);
        bean.on("model.validation.error.first_name", triggerSpy);
        bean.processValidationErrors(errors);
        expect(triggerSpy).toHaveBeenCalledTwice();
    });

    it("should trigger save enabled if no errors is empty", function(){
        var moduleName = "Contacts", bean, error, errors;
        var triggerFn = function(error){
        };
        var triggerSpy = sinon.spy(triggerFn);
        var errors = {};


        dm.declareModel(moduleName, metadata.modules[moduleName]);

        bean = dm.createBean(moduleName, { first_name: "Super long first name"});
        bean.on("model.validation.enableSave", triggerSpy);
        bean.processValidationErrors(errors);
        expect(triggerSpy).toHaveBeenCalledOnce();
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
