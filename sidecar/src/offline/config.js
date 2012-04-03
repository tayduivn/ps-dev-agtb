/**
 * @ignore
 */
(function(app) {
    /**
     * Database adapter.
     * @member config
     * @ignore
     */
    app.config.db = "webSqlAdapter";

    /**
     * Database size, in megabytes.
     * @member config
     * @ignore
     */
    app.config.dbSize = 5;

    /**
     * Database name.
     * @member config
     * @ignore
     */
    app.config.dbName = "sidecar";

})(SUGAR.App);