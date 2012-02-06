(function(app) {

  /**
   * Represents a base class for all bean collection classes.
   * - The default Backbone's sync behavior is overridden by dataManager.sync method.
   */
  app.BeanCollection = Backbone.Collection.extend({
    sync: app.dataManager.sync
  });

})(SUGAR.App);