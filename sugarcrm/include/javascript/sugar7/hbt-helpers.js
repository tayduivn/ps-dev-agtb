(function(app) {
    app.events.on("app:init", function() {

        /**
         * Handlebar helper to get the letters used for the icons shown in various headers for each module, based on the
         * translated singular module name.  This does not always match the name of the module in the model,
         * i. e. Product == Revenue Line Item
         * If the module has an icon string defined, use it, otherwise fall back to the module's translated name.
         * If there are spaces in the name, (e. g. Revenue Line Items or Product Catalog), it takes the initials
         * from the first two words, instead of the first two letters (e. g. RL and PC, instead of Re and Pr)
         * @param {String} module to which the icon belongs
         */
        Handlebars.registerHelper('moduleIconLabel', function(module) {
            var name = app.lang.getAppListStrings('moduleIconList')[module] ||
                    app.lang.getAppListStrings('moduleListSingular')[module] ||
                    module,
                space = name.indexOf(" ");

            return (space != -1) ? name.substring(0 , 1) + name.substring(space + 1, space + 2) : name.substring(0, 2);
        });

        /**
         * Handlebar helper to get the Tooltip used for the icons shown in various headers for each module, based on the
         * translated singular module name.  This does not always match the name of the module in the model,
         * i. e. Product == Revenue Line Item
         * @param {String} module to which the icon belongs
         */
        Handlebars.registerHelper('moduleIconToolTip', function(module) {
            return app.lang.getAppListStrings('moduleListSingular')[module] || module;
        });
    });
})(SUGAR.App);
