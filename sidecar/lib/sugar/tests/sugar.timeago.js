describe("timeago", function() {

    it("should convert a date into a relative time", function() {
        var date = "2012-06-14 23:58:29";
        var $time = $("<time class=\"relativetime\" datetime=\"" + date + "\">" + date + "</time>");
        var origValue = $time.text();
        SUGAR = {};
        SUGAR.App = {};
        SUGAR.App.utils = {};
        SUGAR.App.utils.date = {};
        SUGAR.App.logger = {};
        SUGAR.App.logger.debug = function(msg) { console.log(msg); };
        SUGAR.App.utils.date.parse = function(date) { return new Date(date); };
        SUGAR.App.utils.date.format = function(date) { return new Date(date); };
        SUGAR.App.utils.date.UTCtoLocalTime = function(date) { return new Date(date + ' UTC'); };
        SUGAR.App.utils.date.getRelativeTimeLabel = function(date) { return { str: 'LABEL', value: undefined } };
        SUGAR.App.template = {};
        SUGAR.App.template.compile = function(key, tpl) { return Handlebars.compile(key, tpl); };
        SUGAR.App.lang = {};
        SUGAR.App.lang.get = function(msg) { return msg; };
        $time.timeago({
            logger: SUGAR.App.logger,
            date: SUGAR.App.utils.date,
            lang: SUGAR.App.lang,
            template: SUGAR.App.template
        });

        expect($time.text()).not.toEqual(origValue);
    });

});