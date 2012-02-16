describe("DataManager", function() {

    var metadata,
        app = SUGAR.App,
        dm = SUGAR.App.dataManager;

    beforeEach(function() {
        dm.reset();
        metadata = SugarTest.loadJson("metadata");
    });

    afterEach(function() {
    });

    it("should be able to create an instance of primary bean and collection", function() {
        dm.declareModels(metadata);

        _.each(_.keys(metadata), function(moduleName) {
            expect(dm.createBean(moduleName, {})).toBeDefined();
            expect(dm.createBeanCollection(moduleName)).toBeDefined();
        });

    });

    it("should be able to create an instance of default bean and collection", function() {
        var moduleName = "Contacts",
            beanType = "Contact";

        dm.declareModel(moduleName, metadata[moduleName]);

        var bean = dm.createBean(moduleName, { someAttr: "Some attr value"});
        expect(bean.module).toEqual(moduleName);
        expect(bean.beanType).toEqual(beanType);
        expect(bean.fields).toEqual(metadata[moduleName].beans[beanType].vardefs.fields);
        expect(bean.get("someAttr")).toEqual("Some attr value");

        var collection = dm.createBeanCollection(moduleName);
        expect(collection.module).toEqual(moduleName);
        expect(collection.beanType).toEqual(beanType);
        expect(collection.model).toBeDefined();

    });

    it("should be able to create an instance of non-default bean and collection", function() {
        var moduleName = "Teams",
            beanType = "TeamSet";

        dm.declareModel(moduleName, metadata[moduleName]);

        var bean = dm.createBean(moduleName, { someAttr: "Some attr value"}, beanType);
        expect(bean.module).toEqual(moduleName);
        expect(bean.beanType).toEqual(beanType);
        expect(bean.fields).toEqual(metadata[moduleName].beans[beanType].vardefs.fields);
        expect(bean.get("someAttr")).toEqual("Some attr value");

        var collection = dm.createBeanCollection(moduleName, undefined, undefined, beanType);
        expect(collection.module).toEqual(moduleName);
        expect(collection.beanType).toEqual(beanType);
        expect(collection.model).toBeDefined();

    });

    it("should be able to fetch a bean by ID", function() {
        var moduleName = "Teams",
            beanType = "TeamSet";

        dm.declareModel(moduleName, metadata[moduleName]);

        var mock = sinon.mock(Backbone);
        mock.expects("sync").once().withArgs("read");

        var bean = dm.fetchBean(moduleName, "xyz", null, beanType);

        expect(bean.module).toEqual(moduleName);
        expect(bean.beanType).toEqual(beanType);
        mock.verify();
    });

    it("should be able to fetch beans", function() {
        var moduleName = "Teams",
            beanType = "TeamSet";
        dm.declareModel(moduleName, metadata[moduleName]);

        var mock = sinon.mock(Backbone);
        mock.expects("sync").once().withArgs("read");

        var collection = dm.fetchBeans(moduleName, null, beanType);

        expect(collection.module).toEqual(moduleName);
        expect(collection.beanType).toEqual(beanType);
        expect(collection.model).toBeDefined();
        mock.verify();
    });


});