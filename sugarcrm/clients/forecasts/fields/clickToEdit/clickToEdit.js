({
    events: {
        'mouseenter': 'showActions',
        'mouseleave': 'hideActions'
    },

    ctePath: "",
    cteField: {},
    cteIcon: {},
    undoIcon:{},
    undoValue: "",

    /**
     * initializes clickToEdit fields
     * @param options
     */
    initialize: function(options) {
        this.app.view.Field.prototype.initialize.call(this, options);
        this.ctePath = this.app.config.serverUrl + "/../../clients/base/fields/clickToEdit";

        var self = this;

        // Temporarily at this location - Add chosen as a field type for editable.
        // TODO: abstract this out to handle more elegantly as determined by metadata, replace with an actual sugarField
        $.editable.addInputType("chosen", {
            /**
             * create the element as a dropdown, so chosen can process
             * @param settings
             * @param original
             */
            element: function(settings, original) {
                var selEl = $('<select class="cteSelect">');
                _.each(app.lang.getAppListStrings(settings.context.def.options), function (value, key) {
                    var option = $("<option>").val(key).append(value);
                    selEl.append(option);
                });
                $(this).append(selEl);
                var hidden = $('<input type="hidden">');
                $(this).append(hidden);
                return(hidden);
            },

            /**
             * sets up and attaches the chosen plugin for this type.
             * @param settings
             * @param original
             */
            plugin: function(settings, original) {
                $("select", this).filter(".cteSelect").chosen().change(settings.context, function(e) {
                    self.doChange($(this).val(), settings);
                });
            },

            /**
             * process value from chosen for submittal
             * @param settings
             * @param original
             */
            submit: function(settings, original) {
                $("input", this).val($("select", this).filter(".cteSelect").val());
            }
        });
    },

    /**
     * renders clickToEdit field
     */
    _render:function() {
        debugger;
        this.app.view.Field.prototype._render.call(this);

        var self = this;

        this.cteField = this.$el.find('.cte' + this.def.cteclass);
        this.cteIcon = this.cteField.parent().find('.cteIcon');
        this.undoIcon = this.cteField.parent().find('.cteUndoIcon');

        this.undoIcon.on('click', null, self, this.doUndo);
        this.cteField.editable(self.doChange,
            {
                type: self.def.ctetype || "text",
                select: true,
                onedit: self.doEdit,
                onreset: function(){console.log("onreset"); console.log(this);},
                onblur: "submit",
                callback: self.editFinished,
                context: self
            }
        );

        // bind the events specific to this field
        _.each(this.events, function(act, event){
            this.cteField.on(event, this.act);
        }, this);
        return this;
    },

    /**
     * Called to do the change, once submitted from editable
     * @param value the new value
     * @param settings the editable settings, includes "self" as settings.context
     */
    doChange: function(value, settings) {
        settings.context.model.set(settings.context.name, value);
        // currently does not pass validation on saves
        // TODO: fix it.
        // settings.context.model.save(settings.context.name, value);
        return value;
    },

    /**
     * Called when the clickToEdit is triggered.  This is where we store the previous value for undo
     * @param settings the editable settings, includes "self" as settings.context
     * @param original the original html
     */
    doEdit: function(settings, original) {
        settings.context.cteIcon.hide();
        settings.context.undoValue = original.innerText;
    },

    /**
     * Handler to do an undo
     * @param e event that triggered the handler
     */
    doUndo: function(e) {
        $(this).hide();
        e.data.model.set(e.data.name, e.data.undoValue);
        // currently does not pass validation on saves
        // TODO: fix it.
        // e.data.model.save(e.data.name, e.data.undoValue);
    },

    /**
     * Gets called after editing is complete and submitted.
     * @param value
     * @param settings
     */
    editFinished: function(value, settings) {
        settings.context.undoIcon.show();
    },

    /**
     * Overwriting default bindDomChange function to prevent default behavior
     *
     * @param model
     * @param fieldName
     */
    bindDomChange: function(model, fieldName) {},

    /**
     * Handler to show the clickToEdit pencil icon
     * @param e event that triggered the handler
     */
    showActions: function (e) {
        this.cteIcon.show();
    },

    /**
     * Handler to hide the clickToEdit pencil icon
     * @param e event that triggered the handler
     */
    hideActions: function(e) {
        this.cteIcon.hide();
    }

})
