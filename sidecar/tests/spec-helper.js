
var SugarTest = {};

(function(test) {

    test.loadFile = function(path, file, ext, parseData, dataType) {
        dataType = dataType || 'text';

        var fileContent = null;

        $.ajax({
            async:    false, // must be synchronous to guarantee that a test doesn't run before the fixture is loaded
            cache:    false,
            dataType: dataType,
            url:      path + "/" + file + "." + ext,
            success:  function(data) {
                fileContent = parseData(data);
            },
            failure:  function() {
                console.log('Failed to load file: ' + file);
            }
        });

        return fileContent;
    };

    test.loadFixture = function(file) {
        return test.loadFile("../fixtures", file, "json", function(data) { return data; }, "json");
    }

    test.loadSugarField = function(file) {
        return test.loadFile("../../../sugarcrm/clients/base/fields", file, "js", function(data) { return eval("(" + data + ")"); });
    }

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
