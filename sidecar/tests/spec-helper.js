
var SugarTest = {};

(function(test) {

    test.storage = {};
    test.keyValueStore = {
        set: function(key, value) {
            test.storage[key] = value;
        },
        add: function(key, value) {
            test.storage[key] += value;
        },
        get: function(key) {
            return test.storage[key];
        },
        cut: function(key) {
            delete test.storage[key];
        },
        cutAll: function() {
            test.storage = {};
        },
        getAll: function() {
            return test.storage;
        }
    };

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
        SUGAR.App.config.appId = "portal";
        SUGAR.App.config.maxQueryResult = 20;

        SugarTest.storage = {};
        SUGAR.App.cache.store = SugarTest.keyValueStore;
    }
});

afterEach(function() {
    if (typeof Backbone != "undefined" && !_.isUndefined(Backbone.history)) Backbone.history.stop();
});