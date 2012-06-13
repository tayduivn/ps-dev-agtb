describe("timeago", function() {

    it("should convert a date into a relative time", function() {
        var date = "2012-06-14 23:58:29";
        var $time = $("<time class=\"relativetime\" datetime=\"" + date + "\">" + date + "</time>");
        var origValue = $time.text();
        SUGAR = {};
        SUGAR.App = {};
        SUGAR.App.logger = {};
        SUGAR.App.utils = {};
        SUGAR.App.utils.date = {};
        SUGAR.App.utils.date.parse = function(date) { return new Date(date); };
        SUGAR.App.utils.date.format = function(date) { return new Date(date); };
        SUGAR.App.logger.debug = function(msg) { console.log(msg); };
        SUGAR.App.utils.date.UTCtoLocalTime = function(date) { return new Date(date); };
        SUGAR.App.utils.date.getRelativeTime = function(date) { return 'LABEL'; };
        $time.timeago();

        expect($time.text()).not.toEqual(origValue);
    });

});