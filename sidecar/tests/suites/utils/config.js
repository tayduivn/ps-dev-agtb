describe("Config", function() {

  var config = SUGAR.App.config;

  it("should provide default configuration", function() {
    expect(config.env).toBeDefined();
    expect(config.logLevel).toBeDefined();
    expect(config.logFormatter).toBeDefined();
    expect(config.logWriter).toBeDefined();
  });

});