(function(app) {

  /**
   * Represents a base class for all bean model classes.
   * - The default Backbone's sync behavior is overridden by dataManager.sync method.
   * - Bean's metadata is accessible via "module" property.
   */
  app.augment("Bean", Backbone.Model.extend({
    sync: app.dataManager.sync,

    validate: function(attrs) {
      // TODO: Implement validation (the metadata is accessible via this.module property)
    }

  }), false);

})(SUGAR.App);