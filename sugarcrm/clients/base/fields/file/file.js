({
    events: {
        "click a.file": "startDownload"
    },
    fileUrl: "",
    _render: function() {
        // This array will contain objects accessible in the view
        this.attachments = [];

        this.model = this.model || this.view.model;
        var value = this.model.get(this.name);

        // Not the same behavior either the value is a string or an array of files
        if (_.isArray(value)) {
            // If it's an array, we get the uri for each files in the response
            _.each(value, function(file) {
                var fileObj = {
                    name: file.name,
                    url: file.uri
                };
                this.attachments.push(fileObj);
            }, this);
        } else if (value) {
            // If it's a string, build the uri with the api library
            var fileObj = {
                name: value,
                url: app.api.buildFileURL({
                        module: this.module,
                        id: this.model.id,
                        field: this.name
                    },
                    {
                        htmlJsonFormat: false,
                        passOAuthToken: false
                    })};
            this.attachments.push(fileObj);
        }
        app.view.Field.prototype._render.call(this);
        this.resizeInput();
        return this;
    },
    startDownload: function(e) {
        var self = this;
        // Starting a download.
        // First, we do an ajax call to the `ping` API. This is supposed to check if the token hasn't expired before we
        // append it to the uri of the file. Thus the token will be valid anytime we append it to the url and start the
        // download.
        App.api.call('read', App.api.buildURL('ping'), {}, {
                success: function(data) {
                   // Second, start the download with the "iframe" hack
                   var uri = self.$(e.currentTarget).data("url") + "?oauth_token=" + app.api.getOAuthToken();
                   self.$el.prepend('<iframe class="hide" src="' + uri + '"></iframe>');
                },
                error: function(data) {
                    // refresh token if it has expired
                    app.error.handleHttpError(data, {});
                }}
        );
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