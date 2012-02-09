(function(app) {

  /**
   * SUGAR.App application configuration.
   */
  app.augment("config", {
    // Possible values: 'dev', 'test', 'prod'
    env: 'dev',

    logLevel: app.logger.Levels.DEBUG,
    logWriter: app.logger.ConsoleWriter,
    logFormatter: app.logger.SimpleFormatter,

    //restApiUrl: 'http://localhost:8888/rest/10/'
  }, false);

})(SUGAR.App);
