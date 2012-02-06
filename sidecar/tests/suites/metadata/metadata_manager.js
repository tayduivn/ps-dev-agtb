describe("MetadataManager", function() {
  var mm = SUGAR.App.metadataManager;
  var metadata;

  beforeEach(function() {
    // TODO: Put it into fixture file
    metadata = {
      "Accounts": {},
      "Contacts": {},
      "Leads":    {}
    };
  });

  it("should be able to load module definitions", function() {
    mm.load(metadata);
    var names = _.keys(metadata);

    expect(SUGAR.App.modules).toBeDefined();
    expect(_.keys(SUGAR.App.modules).length).toEqual(names.length);

  });
});