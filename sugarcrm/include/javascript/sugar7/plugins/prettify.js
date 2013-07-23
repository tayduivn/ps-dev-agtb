(function (app) {
    app.events.on('app:init', function () {
        app.plugins.register('prettify', ['layout', 'view'], {
            /**
             * Has content been prettified
             */
            _scriptReady: false,
            _pageReady: false,
            /**
             * Attach code for when the plugin is registered on a view or layout
             *
             * @param component
             * @param plugin
             */
            onAttach: function (component, plugin) {
                this.on('init', function () {
                    var self = this;
                    // was google pretty print script loaded elsewhere?
                    if (window.prettyPrint) {
                        this._scriptReady = true;
                        return;
                    }
                    $.getScript(
                        'styleguide/content/js/google-code-prettify/prettify.js',
                        function () {
                            self._scriptReady = true;
                            if (self._pageReady) {
                                // if content has been loaded, run prettify
                                prettyPrint();
                            }
                        }
                    );
                }, null, component);

                this.on('render', function () {
                    this._pageReady = true;
                    if (this._scriptReady) {
                        // if script has been loaded, run prettify
                        prettyPrint();
                    }
                }, null, component);
            }
        });
    });
})(SUGAR.App);
