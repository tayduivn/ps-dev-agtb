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
     * Enables sync by default.
     *
     * @param {Object} options
     *
     * @see app.view.Field.initialize
     */
    initialize: function(options) {

        app.view.Field.prototype.initialize.call(this, options);
        this._initialValues = {};
        this._fields = {};

        if (_.isUndefined(this.def.sync)) {
            this.def.sync = true;
        }
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

        if (!$(evt.currentTarget).is(':checked')) {
            this.syncCopy(false);
            this.restore();
            return;
        }

        _.each(this.def.mapping, function(target, source) {
            this.copy(source, target);
        }, this);

        this.syncCopy(true);
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
     * Enables or disables the sync copy only if the field has the `sync`
     * definition to set to TRUE.
     *
     * @param {Boolean} enable
     *   TRUE to keep the mapping fields in sync, FALSE otherwise.
     */
    syncCopy: function(enable) {

        if (!this.def.sync) {
            return;
        }

        if (!enable) {
            this.model.off(null, this.copyChanged);
            return;
        }

        var events = _.map(_.keys(this.def.mapping), function (field) {
            return 'change:' + field;
        });
        this.model.on(events.join(' '), this.copyChanged, this);
    },

    /**
     * Callback for the syncCopy binding.
     *
     * @param {Backbone.Model} model
     *   The model that was changed.
     * @param {*} value
     *   The value of the field that was changed.
     * @param {Object} args
     *   An object with the list of the fields that changed.
     */
    copyChanged: function(model, value, args) {

        _.each(args.changes, function(hasChanged, field) {
            // console.log(field);
            // console.log(mapping[field]);
            model.set(this.def.mapping[field], model.get(field));
        }, this);
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
