({
    events: {
        "mouseenter img" : "showButton",
        "mouseenter a" : "showButton",
        "mouseleave a" : "hideButton",
        "mouseleave img" : "hideButton",
        "click .delete" : "delete",
        "focus input[type=file]": "onUploadFocus",
        "blur input[type=file]": "onUploadBlur",
        "change input[type=file]": "onUploadChange"
    },
    fileUrl : "",
    _render: function() {
        app.view.Field.prototype._render.call(this);
        this.model.fileField = this.name;
        this.fileURL = (this.value) ? this.buildUrl() + "&" + this.value : "";
        app.view.Field.prototype._render.call(this);
        this.resizeInput();
        return this;
    },
    bindDataChange: function() {
        if (this.view.name != "edit" && this.view.fallbackFieldTemplate != "edit") {
            //Keep empty because you cannot set a value of a type `file` input
            app.view.Field.prototype.bindDataChange.call(this);
        }
    },
    resizeInput: function () {
          // Get label width so we can make button fluid, 12px default left/right padding
          var lbl_width = (this.$('input[type=file]').parent().find('span strong').width() || 14) + 24;

           this.$('input[type=file]').parent().find('span').css('width',lbl_width)
          .closest('.upload-field-custom').css('width',lbl_width);
    },
    buildUrl: function(options) {
        return app.api.buildFileURL({
                    module: this.module,
                    id: this.model.id,
                    field: this.name
                }, options);
    },
    showButton: function() {
        this.$(".delete").removeClass("hide");
    },
    hideButton: function() {
        this.$(".delete").addClass("hide");
    },
    delete: function() {
        var self = this;
        App.api.call('delete', self.buildUrl({htmlJsonFormat: false}), {}, {
                success: function(data) {
                    self.model.set(self.name, null);
                    self._render();
                },
                error: function(data) {
                    // refresh token if it has expired
                    app.error.handleHttpError(data, {});
                }}
        );
    },
    onUploadChange:function (e) {
        var input = e.currentTarget,
            $input = this.$(input);
        if (input.value) {
            var this_container = $input.parent('.file-upload').parent('.upload-field-custom'),
                value_explode = input.value.split('\\'),
                value = value_explode[value_explode.length - 1];

            if (this_container.next('.file-upload-status').length > 0) {
                this_container.next('.file-upload-status').remove();
            }
            //this_container.append('<span class="file-upload-status">'+value+'</span>');
            $('<span class="file-upload-status">' + value + '</span>').insertAfter(this_container);
        }

    },
    onUploadFocus:function () {
        this.$(e.currentTarget).parent().addClass('focus');
    },
    onUploadBlur:function () {
        this.$(e.currentTarget).parent().addClass('focus');
    }
})