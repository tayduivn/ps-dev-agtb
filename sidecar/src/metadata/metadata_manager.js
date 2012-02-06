(function(app) {

  /**
   * Metadata manager is responsible for:
   * - caching metadata in memory and browser's local storage if it's enabled.
   * - communicating with the server to keep the metadata in sync.
   * - creating/migrating offline database schema.
   *
   * At the minimum, the metadata manager processes metadata payload and creates instances
   * of Module class that encapsulate metadata for a single module.
   *
   *
   */
  app.metadataManager = {

    load: function(metadata) {
      app.modules = {};

      _.each(_.keys(metadata), function(moduleName) {
        app.modules[moduleName] = new app.Module(moduleName, metadata[moduleName]);
      });

    }

  };

})(SUGAR.App);