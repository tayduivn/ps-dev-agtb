({
    /**
     * The UID for what we are going to pull up
     */
    uid : '',

    /**
     * Events to watch for
     */
    events : {
        'click a[rel=inspector]': 'click'
    },

    /**
     * Just Prevent The Default Event From Happening
     *
     * @param evt
     */
    click : function(evt) {
        evt.preventDefault();
    },

    /**
     * Set the this.uid before we render the field
     *
     * @return {*}
     * @private
     */
    _render:function () {
        this.uid = this.model.get(this.def.uid_field);

        app.view.Field.prototype._render.call(this);

        return this;
    }
})