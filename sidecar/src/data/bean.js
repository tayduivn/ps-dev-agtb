(function(app) {

    /**
     * Represents a base class for all bean model classes.
     * - The default Backbone's sync behavior is overridden by dataManager.sync method.
     * - Bean's metadata is accessible via "module" property.
     */
    app.augment("Bean", Backbone.Model.extend({
        sync: app.dataManager.sync,

        /**
        * See Backbone.Model.validate documentation for details.
        * @param attrs
        */
        validate: function(attrs) {
          // The model has "validations" property which keeps a hash of validators for each field
          if (_.isEmpty(this.validations)) return;

          var errors = [], result, validators;
          _.each(_.keys(attrs), function(attribute) {
              validators = this.validations[attribute];

              _.each(validators, function(validator) {
                  result = validator(this, attrs[attribute]);
                  if (result) {
                      result.attribute = attribute;
                      errors.push(result);
                  }
              }, this);
          }, this);

          // "validate" method should not return anything in case there are not validation errors
          if (errors.length > 0) return errors;
        }

    }), false);

})(SUGAR.App);