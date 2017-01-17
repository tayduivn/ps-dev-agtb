var myStepDefinitionsWrapper = function () {

    /**
     * Search for "value" in list view search filter
     *
     * @example "I search for "Account_Search" in #AccountsList:FilterView view"
     */
    this.When(/^I search for "([^"]*)" in (#\S+) view$/,
        function(value, view, callback) {
            view.setSearchField(value)
                .call(callback);
        }, true);
};

module.exports = myStepDefinitionsWrapper;
