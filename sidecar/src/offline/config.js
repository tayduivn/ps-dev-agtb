(function(app) {
//    /**
//     * Flag indicating if offline storage is enabled.
//     * @type {Boolean}
//     * @member config
//     */
//    app.config.offlineModeEnabled = true;

    /**
     * Database adapter.
     * @member config
     */
    app.config.db = "webSqlAdapter";

    /**
     * Database size, in megabytes.
     * @member config
     */
    app.config.dbSize = 5;

    /**
     * Database name.
     * @member config
     */
    app.config.dbName = "sidecar";

})(SUGAR.App);