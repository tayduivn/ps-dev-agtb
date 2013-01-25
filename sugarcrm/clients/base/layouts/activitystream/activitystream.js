({
    events: {
        'click .addPost': 'addPost',
        'dragenter .sayit': 'expandNewPost',
        'dragover .sayit': 'dragoverNewPost',
        'dragleave .sayit': 'shrinkNewPost',
        'drop .sayit': 'dropAttachment',
        'dragstart .activitystream-attachment': 'saveAttachment'
    },

    initialize: function(opts) {
        _.bindAll(this);
        this.template = app.template.get("l.activitystream");

        this.renderHtml();

        app.view.Layout.prototype.initialize.call(this, opts);

        // Expose the dataTransfer object for drag and drop file uploads.
        jQuery.event.props.push('dataTransfer');
    },

    renderHtml: function() {
        this.$el.html(this.template(this));
    },

    _placeComponent: function(component) {
        this.$el.find(".activitystream-layout").append(component.el);
    },

    addPost: function() {
        this.trigger("stream:addPost:fire");
    },

    expandNewPost: function(event) {
        this.$(event.currentTarget).attr("placeholder", "Drop a file to attach it to the comment.").addClass("dragdrop");
        return false;
    },

    dragoverNewPost: function(event) {
        return false;
    },

    shrinkNewPost: function(event) {
        event.stopPropagation();
        event.preventDefault();
        this.$(event.currentTarget).attr("placeholder", "Type your post").removeClass("dragdrop");
        return false;
    },

    dropAttachment: function(event) {
        event.stopPropagation();
        event.preventDefault();
        this.shrinkNewPost(event);
        _.each(event.dataTransfer.files, function(file, i) {
            var fileReader = new FileReader();
            var self = this;

            // Set up the callback for the FileReader.
            fileReader.onload = (function(file) {
                return function(e) {
                    var container,
                        sizes = ['B', 'KB', 'MB', 'GB'],
                        size_index = 0,
                        size = file.size,
                        unique = _.uniqueId("activitystream_attachment");

                    while (size > 1024 && size_index < sizes.length - 1) {
                        size_index++;
                        size /= 1024;
                    }

                    size = Math.round(size);

                    app.drag_drop = app.drag_drop || {};
                    app.drag_drop[unique] = file;
                    container = $("<div class='activitystream-pending-attachment' id='" + unique + "'></div>");

                    // TODO: Review creation of inline HTML
                    $('<a class="close">&times;</a>').on('click',function(e) {
                        $(this).parent().remove();
                        delete app.drag_drop[container.attr("id")];
                    }).appendTo(container);

                    container.append(file.name + " (" + size + " " + sizes[size_index] + ")");

                    if (file.type.indexOf("image/") !== -1) {
                        container.append("<img style='display:block;' src='" + e.target.result + "' />");
                    } else {
                        container.append("<div>No preview available</div>");
                    }

                    container.appendTo(self.$(event.currentTarget).parent());
                };
            })(file);

            fileReader.readAsDataURL(file);
        }, this);
    },

    /**
     * Handles dragging an attachment off the page.
     * @param  {Event} event
     */
    saveAttachment: function(event) {
        // The following is only true for Chrome.
        if (event.dataTransfer && event.dataTransfer.constructor == Clipboard &&
            event.dataTransfer.setData('DownloadURL', 'http://www.sugarcrm.com')) {
            var el = $(event.currentTarget),
                mime = el.data("mime"),
                name = el.data("filename"),
                file = el.data("url"),
                origin = document.location.origin,
                path = [];

            path = _.initial(document.location.pathname.split('/'));
            path = path.concat(file.split('/'));

            // Resolve .. and . in paths. Chrome doesn't do it for us.
            for (var i = 0; i < path.length; i++) {
                if (".." == path[i + 1]) {
                    delete path[i + 1];
                    delete path[i];
                    i--;
                }
                if ("." == path[i]) {
                    delete path[i];
                    i--;
                }
            }
            path = _.compact(path);
            event.dataTransfer.setData("DownloadURL", mime + ":" + name + ":" + origin + "/" + path.join('/'));
        }
    }
})
