describe("Module", function() {

  it("should be able to declare bean model and bean collection", function() {
    var module = new SUGAR.App.Module("accounts", {});

    expect(module.name).toEqual("accounts");
    expect(module.beanModel).toBeDefined();
    expect(module.beanCollection).toBeDefined();
  });

  it("should be able to instantiate a new bean", function() {
    var module = new SUGAR.App.Module("Accounts", {});
    var account = module.createBean({ id: "xyz", name: "Acme" });

    expect(account.module).toEqual(module);
    expect(account.id).toEqual("xyz");
    expect(account.get("name")).toEqual("Acme");
  });

  it("should be able to instantiate a new bean collection", function() {
    var module = new SUGAR.App.Module("accounts", {});
    var collection = module.createBeanCollection();

    expect(collection.module).toEqual(module);
    expect(collection.model).toEqual(module.beanModel);

  });

});