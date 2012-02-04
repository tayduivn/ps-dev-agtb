describe("Logger", function() {

  var clock,
    logger = SUGAR.App.logger,
    config = SUGAR.App.config;

  beforeEach(function() {
    config.logFormatter = logger.SimpleFormatter;
    config.logWriter = logger.ConsoleWriter;
  });

  afterEach(function() {
    if (clock) clock.restore();
    if (typeof logger.log.restore === "function") logger.log.restore();
    if (typeof console.log.restore === "function") console.log.restore();
    if (typeof console.info.restore === "function") console.info.restore();
    if (typeof console.warn.restore === "function") console.warn.restore();
    if (typeof console.error.restore === "function") console.error.restore();
  });

  it("should be able to log a message", function() {
    var spy = sinon.spy(console, "error");

    var date = new Date(Date.UTC(2012, 2, 3, 6, 15, 32));
    clock = sinon.useFakeTimers(date.getTime());

    config.logLevel = logger.Levels.ERROR;
    logger.error("Test message");
    expect(spy).toHaveBeenCalledWith("ERROR[2012-2-3 6:15:32]: Test message");
  });

  it("should be able to log a closure", function() {
    var spy = sinon.spy(console, "info");

    config.logLevel = logger.Levels.INFO;
    var a = "foo";
    logger.info(function() {
      return "Test message " + a;
    });
    expect(spy.firstCall.args[0]).toMatch(/INFO\[.{15,20}\]: Test message foo/);
  });

  it("should not log a message if log level is below the configured one", function() {
    var spy = sinon.spy(console, "info");
    config.logLevel = logger.Levels.INFO;
    logger.debug("");
    expect(spy).not.toHaveBeenCalled();
  });

  it("should be able to log a message with a given log level", function() {
    config.logLevel = logger.Levels.TRACE;

    var spy = sinon.spy(logger, "log");

    // TODO: Perhaps it should be split up into separate specs

    logger.trace("");
    expect(spy).toHaveBeenCalledWith(logger.Levels.TRACE);

    logger.debug("");
    expect(spy).toHaveBeenCalledWith(logger.Levels.DEBUG);

    logger.info("");
    expect(spy).toHaveBeenCalledWith(logger.Levels.INFO);

    logger.warn("");
    expect(spy).toHaveBeenCalledWith(logger.Levels.WARN);

    logger.error("");
    expect(spy).toHaveBeenCalledWith(logger.Levels.ERROR);

    logger.fatal("");
    expect(spy).toHaveBeenCalledWith(logger.Levels.FATAL);

  });

});