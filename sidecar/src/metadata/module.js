(function(app) {

  /**
   * Encapsulates metadata for a single Sugar module.
   * @param name Module name. For example, "Accounts".
   * @param definition Module metadata. An object that contains vardefs, viewdefs, etc.
   */
  app.Module = function(name, definition) {
    this.name = name;
    this.definition = definition;

    // TODO: Process vardefs and initialize defaults
    var defaults = null;
    this.beanModel = app.Bean.extend({
      module: this,
      defaults: defaults
    });

    this.beanCollection = app.BeanCollection.extend({
      model:  this.beanModel,
      module: this
    });
  };

  _.extend(app.Module.prototype, {

    createBean: function(attrs) {
      return new this.beanModel(attrs);
    },

    createBeanCollection: function(models, options) {
      return new this.beanCollection(models, options);
    }


  });

})(SUGAR.App);