
var SugarTest = {};

(function(test) {

    SUGAR.App.config.logLevel = SUGAR.App.logger.Levels.TRACE;
    SUGAR.App.config.env = "test";

    test.loadJson = function(jsonFile) {
      var json = null;
      $.ajax({
        async:    false, // must be synchronous to guarantee that a test doesn't run before the fixture is loaded
        cache:    false,
        dataType: 'json',
        url:      "../fixtures/" + jsonFile + ".json",
        success:  function(data) {
          json = data;
        },
        failure:  function() {
          console.log('Failed to load json fixture: ' + jsonFile);
        }
      });

      return json;
    };

    test.waitFlag = false;
    test.wait = function() { waitsFor(function() { return test.waitFlag; }); };
    test.resetWaitFlag = function() { this.waitFlag = false; };
    test.setWaitFlag = function() { this.waitFlag = true; };

})(SugarTest);