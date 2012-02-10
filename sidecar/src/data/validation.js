(function(app) {

    var _validators = {

        required: function(flag, attribute, model, value) {
            var currentValue = model.get(attribute);
            if ((_.isEmpty(value) && _.isEmpty(currentValue)) || _.isEmpty(value))
            return {
                required: true
            }
        },

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

            // TODO: Is there more elegant way to deal with this?
            if (type !== "required") {
                validator = _.bind(validator, null, definition, attribute);
            }
            else {
                validator = _.bind(validator, null, attribute);
            }

            return validator;
        }

//        addCustomValidator: function(type, validator) {
//            // TODO: Check if validator already exists
//            _validators[type] = validator;
//        }

    }, false);

})(SUGAR.App);