
var SugarTest = {};

(function(test) {

    SUGAR.App.config.logLevel = SUGAR.App.logger.Levels.TRACE;

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

})(SugarTest);