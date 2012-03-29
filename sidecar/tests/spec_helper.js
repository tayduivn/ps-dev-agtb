
var SugarTest = {};

(function(test) {

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

    test.loadJsFile = function(jsFile) {
        var json = null;
        $.ajax({
            async:    false, // must be synchronous to guarantee that a test doesn't run before the fixture is loaded
            cache:    false,
            dataType: 'text',
            url:      "../" + jsFile + ".js",
            success:  function(data) {
                obj = eval("(" + data + ")");
            },
            failure:  function() {
                console.log('Failed to load js file: ' + jsFile);
            }
        });

        return obj;
    };

    test.waitFlag = false;
    test.wait = function() { waitsFor(function() { return test.waitFlag; }); };
    test.resetWaitFlag = function() { this.waitFlag = false; };
    test.setWaitFlag = function() { this.waitFlag = true; };

})(SugarTest);

beforeEach(function(){
    SugarTest.resetWaitFlag();
    if (SUGAR.App) {
        SUGAR.App.config.logLevel = SUGAR.App.logger.levels.TRACE;
        SUGAR.App.config.env = "test";
        SUGAR.App.config.maxQueryResult = 20;
    }
});

afterEach(function() {
    if (typeof Backbone != "undefined" && !_.isUndefined(Backbone.history)) Backbone.history.stop();
});
