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

    initialize: function(options) {
        this.app.view.Field.prototype.initialize.call(this, options);

        var self = this;

        this.ctePath = this.app.config.serverUrl + "/../../clients/base/fields/clickToEdit";

        // temporarily add chosen clickToEdit type, here
        // TODO:  make this happen more elegantly, and add more type
        $.editable.addInputType("chosen", {
            element: function(settings, original) {
                var selEl = $('<select class="cteSelect">');
                _.each(app.lang.getAppListStrings(settings.context.fieldDef.options), function (value, key) {
                    var option = $("<option>").val(key).append(value);
                    selEl.append(option);
                });
                $(this).append(selEl);
                var hidden = $('<input type="hidden">');
                $(this).append(hidden);
                $(this).find('.cteSelect');
                return(hidden);
            },

            plugin: function(settings, original) {
                $("select", this).filter(".cteSelect").chosen().change(settings.context, function(e) {
                    self.doChange($(this).val(), settings);
                });
            },

            submit: function(settings, original) {
                $("input", this).val($("select", this).filter(".cteSelect").val());
            }
        });
    },

    render:function() {
        this.app.view.Field.prototype.render.call(this);

        var self = this;

        this.cteField = this.$el.find('.cte' + this.cteclass);
        this.cteIcon = this.cteField.parent().find('.cteimage');
        this.undoIcon = this.cteField.parent().find('.cteundo');

        this.undoIcon.on('click', null, self, this.doUndo);
        this.cteField.editable(self.doChange,
            {
                type: self.ctetype || "text",
                select: true,
                onedit: self.doEdit,
                onreset: function(){console.log("onreset"); console.log(this);},
                onsubmit: self.editFinished,
                onblur: "submit",
                context: self
            }
        );

        // bind the events specific to this field
        _.each(this.events, function(act, event){
            this.cteField.on(event, this.act);
        }, this);
        return this;
    },

    /***
     * Overwriting default bindDomChange function to prevent default behavior
     *
     * @param model
     * @param fieldName
     */
    bindDomChange: function(model, fieldName) {},

    /**
     * The function editable expects to be the AJAX call for the edit.  We simply return the value since all Ajax/REST
     * calls happen in backbone
     * @param value
     * @param settings
     */
    doChange: function(value, settings) {
        return value;
    },

    /**
     * This is called when the clickToEdit is initiated.  Saves the undo value for the field.
     * @param settings
     * @param original
     */
    doEdit: function(settings, original) {
        settings.context.cteIcon.hide();
        if (settings.context.undoValue != original.innerText) {
            // This just saves the undovalue in memory as part of the current context.
            //TODO:  make the undo value persistent
            settings.context.undoValue = original.innerText;
        }
    },

    /**
     * Event handler for the event that triggers an undo
     * @param e the event
     */
    doUndo: function(e) {
        $(this).hide();
        e.data.model.set(e.data.name, e.data.undoValue);
        e.data.model.save(e.data.name, e.data.undoValue);
    },

    /**
     * This is called when the clickToEdit value is submitted
     * @param value
     * @param settings
     */
    editFinished: function(value, settings) {
        if (value != settings.context.undoValue) {
            settings.context.model.set(settings.context.name, value);
            settings.context.model.save(settings.context.name, value);
            settings.context.undoIcon.show();
        }
        return value;
    },

    /**
     * Event handler to show the clickToEdit pencil icon
     * @param e an event
     */
    showActions: function (e) {
        this.cteIcon.show();
    },

    /**
     * Event handler to hide the clickToEdit pencil icon
     * @param e an event
     */
    hideActions: function(e) {
        this.cteIcon.hide();
    }
})
