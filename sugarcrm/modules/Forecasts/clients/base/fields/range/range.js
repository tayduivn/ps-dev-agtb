({
    /**
     * holder for the field tag, so we can get at it easily
     */
    fieldTag: '.rangeSlider',

    /**
     * Object that maps the sliderType from metadata, to the appropriate noUiSlider settings.
     * The number of handles this slider has can be set in metadata by specifying sliderType to be as follows:
     * - 'single' - a single slider, the value of the field will just be the integer value of the slide
     * - 'upper' - a single slider, visually connected to the upper range, the value of the field will be {min: <value of slide>, max: this.rangeMax}
     * - 'lower' - a single slider, visually connected to the lower range, the value of the field will be {min: this.rangeMin, max: <value of slide>}
     * - 'double' - a double slider, the value of the field will be [<value of lower slide>, <value of upper slide>]
     * - 'connected' - a double slider, visually connected together, the value of the field will be {min: <value of lower slide>, max: <value of upper slide>}
     */
    _sliderTypeSettings: {
        single: {handles: 1, connect: false},
        upper: {handles: 1, connect: 'upper'},
        lower: {handles: 1, connect: 'lower'},
        double: {handles: 2, connect: false},
        connected: {handles: 2, connect: true}
    },

    /**
     * Renders this field.  This is where the noUiSlider gets added.
     * @param value
     * @private
     */
    _render: function(value) {
        app.view.Field.prototype._render.call(this);

        this._setupSlider(this.$el.find(this.fieldTag));
    },

    /**
     * Unformats a value for storing in a model.
     *
     * Cleans up the value to store it in a model based on the sliderType
     * @param {Mixed} value The value to unformat.
     * @return {Mixed} Unformatted value based on sliderType:
     * - single - integer value
     * - double - array of two integer values
     * - upper, lower, connected - a range as {min, max}.
     */
    unformat: function(value) {
        var sliderType = this.def.sliderType || 'single';

        switch(sliderType) {
            case 'single':
                return _.first(value);
            case 'upper':
                return {
                    min: _.first(value),
                    max: this.def.maxRange || 100
                };
            case 'lower':
                return {
                    min: this.def.minRange || 0,
                    max: _.last(value)
                };
            case 'double':
                return [
                    _.isNaN(_.first(value))?this.def.minRange || 0: _.first(value),
                    _.isNaN(_.last(value))?this.def.maxRange || 100:_.last(value)
                ];
            case 'connected':
            default:
                return {
                    min: _.isNaN(_.first(value))?this.def.minRange || 0: _.first(value),
                    max: _.isNaN(_.last(value))?this.def.maxRange || 100:_.last(value)
                };
        }
    },

    /**
     * Formats a value for display.
     *
     * Converts the field stored in the model for a slider type, into a value usable by noUiSlider widget
     * @param {Mixed} value The value to format.
     * @return {Mixed} Formatted value.
     */
    format: function(value) {
        var sliderType = this.def.sliderType || 'single';

        switch(sliderType) {
            case 'single':
                return [ value || this.def.rangeMin || 0 ];
            case 'upper':
                return [ value.min || this.def.rangeMin || 0 ];
            case 'lower':
                return [ value.max || this.def.rangeMax || 100 ];
            case 'double':
                return value;
            case 'connected':
            default:
                if(value) {
                    return [ value.min || this.def.rangeMin || 0, value.max || this.def.rangeMax || 100];
                }
        }
        return [this.def.rangeMin || 0, this.def.rangeMax || 100];
    },

    /**
     * Sets up the noUiSlider jquery widget on the given jQuery element.
     * @param jqel a jquery element, i. e. $.find(this.fieldTag)
     * @private
     */
    _setupSlider: function(jqel) {
        jqel.noUiSlider('init', {
            knobs: this._calculateHandles(),
            connect: this._setupHandleConnections(this.def.sliderType || 'single'),
            scale: this._setupSliderEndpoints(),
            start: this._setupSliderStartPositions(),
            change: this._sliderChange,
            end: this._sliderChangeComplete,
            field: this
        });
    },

    /**
     * Used to calculate the number of sliders for this field by the sliderType set in metadata.
     * @return {integer} The number of handles for the slider
     * @private
     */
    _calculateHandles: function() {
        var sliderType = this.def.sliderType || 'single';

        return this._sliderTypeSettings[sliderType].handles;
    },

    /**
     * Used to set up the graphical connections for the handles based on the slider type
     * @param sliderType the value from metadata.  Valid options are 'single', 'upper', 'lower', 'double', 'connected'
     * @return {string || bool} The value to pass as the 'connect' setting for the noUiSlider jquery widget
     * @private
     */
    _setupHandleConnections: function(sliderType) {
        var sliderType = this.def.sliderType || 'single';

        return this._sliderTypeSettings[sliderType].connect;
    },

    /**
     * Calculates the min and max range for the sliders.
     * @return {array} the min and max setting for the range field that will be pass as the noUiSlider scale
     * @private
     */
    _setupSliderEndpoints: function() {
        var minRange = this.def.minRange || 0,
            maxRange = this.def.maxRange || 100;

        return [minRange, maxRange];
    },

    /**
     * Used to get the starting positions for the sliders to pass to the noUiSlider widget.
     * @return {integer || array} the start values contained in the model for the field.  Falls back on minRange from
     * metadata if the model is empty/undefined, or 0 if no model value and minRange is undefined in metadata.
     * @private
     */
    _setupSliderStartPositions: function() {
        var value;

        if (this.model) {
            value = this.model.get(this.name);
        }

        if (_.isUndefined(value) || (_.isArray(value) && _.isEmpty(value))) {
            return [ this.def.minRange || 0, this.def.maxRange || 100 ];
        }

        return this.format(value);
    },

    /**
     * Gets the value for the jquery slider widget and cleans it up to be used by the view
     * @param jqel the jquery element that has the noUiSlider attached to it.
     * @return {array} for sliderType set to 'single', an array with a single value, for all others, an array of two values.
     */
    getSliderValues: function(jqel) {
        var value = jqel.noUiSlider('value');

        return this.unformat(value);
    },

    /**
     * The function that gets called whenever the sliders are in the process of getting moved/changed.
     * The context of `this` is the noUiSlider from which the function was activated. The field is passed through the
     * settings and can be accessed using `this.data('settings').field` for any necessary access to the controller, model, etc...
     * @param type The type of change that moved the slider.  This will be 'click' when the slider is clicked to a point
     * on the range, 'move' when the slider is updated via the move method (i. e. by the linked slider delegates), or
     * 'slide' when the handle is dragged to a value.
     * @private
     */
    _sliderChange: function(type) {

    },

    /**
     * The function that gets called whenever the slider change is complete.  The context of `this` is the noUiSlider from
     * which the function was activated. The field is passed through the settings and can be accessed using
     * `this.data('settings').field` for any necessary access to the controller, model, etc...
     * @param type The type of change that moved the slider.  This will be 'click' when the slider is clicked to a point
     * on the range, 'move' when the slider is updated via the move method (i. e. by the linked slider delegates), or
     * 'slide' when the handle is dragged to a value.
     * @private
     */
    _sliderChangeComplete: function(type) {
        var field = this.data('settings').field;
        field.model.set(field.name, field.getSliderValues(this));
    }

})