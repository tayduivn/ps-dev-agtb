({
    'events': {
        'click input[type=checkbox]': 'toggle'
    },

    _initialValues: null,
    _fields: null,

    /**
     * Initializes the copy field component.
     *
     * Initializes the initialValues and fields properties.
     *
     * @param {Object} options
     *
     * @see app.view.Field.initialize
     */
    initialize: function(options) {

        app.view.Field.prototype.initialize.call(this, options);
        this._initialValues = {};
        this._fields = {};
    },

    /**
     * Function called for each click on checkbox (normally acts as toggle
     * function).
     *
     * If the checkbox is checked, copy all the source fields to target ones
     * based on the mapping definition of this field. Otherwise, restore all the
     * values of the modified fields by this copy widget.
     *
     * @param {Event} evt
     *   The event (expecting click event) that triggered the checkbox status
     *   change.
     */
    toggle: function(evt) {

        var mapping = this.def.mapping;

        if (!$(evt.currentTarget).is(':checked')) {
            this.restore();
            return;
        }

        _.each(mapping, function(target, source) {
            this.copy(source, target);
        }, this);
    },

    /**
     * Copies the source field value to the target field.
     *
     * Store the initial value of the target field to be able to restore it
     * after. Copy the source field value to the target field. Disable the
     * target field.
     *
     * @param {View.Field} from
     *   The source field to get the value from.
     * @param {View.Field} to
     *   The target field to set the value to.
     */
    copy: function(from, to) {

        if (_.isUndefined(this._initialValues[to])) {
            this._initialValues[to] = this.model.get(to);
        }

        this.model.set(to, this.model.get(from));
        var field = this.getField(to);
        if (!_.isUndefined(field)) {
            field.setDisabled(true);
        }
    },

    /**
     * Restores all the initial value of the fields that were modified by this
     * copy command.
     */
    restore: function() {

        _.each(this._initialValues, function(value, field) {
            this.model.set(field, value);
            var field = this.getField(field);
            if (!_.isUndefined(field)) {
                field.setDisabled(false);
            }
        }, this);

        this._initialValues = {};
    },

    /**
     * Get the field with the supplied name.
     *
     * Cache the fields locally to be faster on next request of the same field.
     *
     * @param {String} name
     *   The name of the field to search for.
     *
     * @return {View.Field}
     *   The field with the name given.
     */
    getField: function(name) {

        if (_.isUndefined(this._fields[name])) {
            this._fields[name] = _.find(this.view.fields, function(field) {
                return field.name == name;
            });
        }

        return this._fields[name];
    },

    /**
     * Keep empty because you cannot set a value of a type `copy`.
     */
    bindDataChange: function() {
    }
})
