(function(app) {
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