// turn of warnings like "Confusing use of '!'., W018"
/* jshint -W018 */

var myStepDefinitionsWrapper = function () {

    /**
     * Click header panel buttons
     *
     * @example "I click Save button on #AccountsDrawer header"
     */
    this.When(/^I click (Create|Edit|Cancel|Save) button on (#[A-Z](?:\w|\S)*) header$/,
        function (btnName, layout, callback) {
            layout.$$('HeaderView').clickButton(btnName.toLowerCase()).call(callback);
        }, true);

    /**
     * Open header panel actions menu
     *
     * @example "I open actions menu in #Account_ARecord"
     */
    this.When(/^I open actions menu in (#[A-Z]\w+)$/,
        function (layout, callback) {
            layout.$$('HeaderView').clickButton('actions').call(callback);
        }, true);

    /**
     * Choose Actions menu options
     *
     * @example "I choose Delete from actions menu in #Account_ARecord"
     */
    this.When(/^I choose (Copy|Delete) from actions menu in (#[A-Z]\w+)\s*$/,
        function (action, layout, callback) {
            layout.$$('HeaderView').clickButton(action).call(callback);
        }, true);
};

module.exports = myStepDefinitionsWrapper;
