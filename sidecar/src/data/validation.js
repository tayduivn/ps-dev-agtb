(function(app) {

    var _validators = {

        max: function(maximumValue, attribute, model, value) {
            if (value > maximumValue)
            return {
                max: maximumValue
            };
        },

        maxLength: function(maxLength, attribute, model, value) {
            value = value || "";
            value = value.toString();
            if (value.length > maxLength)
            return {
                maxLength: maxLength
            };
        }

    };


    app.augment("validation", {
        createValidator: function(type, attribute, definition) {
            var validator = _validators[type];
            validator = _.bind(validator, null, definition, attribute);
            return validator;
        },

        requiredValidator: function(attribute, model, value) {
            var currentValue = model.get(attribute);
            var currentUndefined = _.isUndefined(currentValue);
            // TODO: How about trimming string value? Check out underscore-string lib.
            var valueEmpty = _.isNull(value) || value === "";
            if ((currentUndefined && _.isUndefined(value)) || valueEmpty)
            return {
                required: true
            }
        }


//        addCustomValidator: function(type, validator) {
//            // TODO: Check if validator already exists
//            _validators[type] = validator;
//        }

    }, false);

})(SUGAR.App);