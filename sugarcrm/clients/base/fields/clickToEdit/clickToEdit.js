({
    events: {
        'mouseenter': 'showActions',
        'mouseleave': 'hideActions'
    },

    ctefield: {},
    cteicon: {},
    undoicon: {},
    undoValue: '',

    render:function() {
        this.app.view.Field.prototype.render.call(this);
        var self = this;

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

        this.ctefield = this.$el.find('.cte' + this.cteclass);
        this.cteicon = this.ctefield.parent().find('.cteimage');
        this.undoicon = this.ctefield.parent().find('.cteundo');

        this.undoicon.on('click', null, self, this.doUndo);
        this.ctefield.editable(self.doChange,
            {
                type: self.ctetype || "text",
                select: true,
                onedit: self.doEdit,
                onreset: function(){console.log("onreset"); console.log(this);},
                onsubmit: function(){console.log("onsubmit"); console.log(this);},
                onblur: "submit",
                callback: self.callback,
                context: self
            }
        );

        // bind the events specific to this field
        _.each(this.events, function(act, event){
            this.ctefield.on(event, this.act);
        }, this);
        return this;
    },

    doChange: function(value, settings) {
        console.log("ajax");
        settings.context.model.set(settings.context.name, value);
        settings.context.model.save(settings.context.name, value);
        return value;
    },

    doEdit: function(settings, original) {
        console.log("doEdit");
        settings.context.cteicon.hide();
        settings.context.undoValue = original.innerText;
    },

    doUndo: function(e) {
        console.log("doUndo");
        $(this).hide();
        e.data.model.set(e.data.name, e.data.undoValue);
        e.data.model.save(e.data.name, e.data.undoValue);
    },

    callback: function(value, settings) {
        console.log("callback");
        settings.context.undoicon.show();
    },

    /***
     * Overwriting default bindDomChange function to prevent default behavior
     *
     * @param model
     * @param fieldName
     */
    bindDomChange: function(model, fieldName) {},

    showActions: function (e) {
        this.cteicon.show();
    },

    hideActions: function(e) {
        this.cteicon.hide();
    },

})
