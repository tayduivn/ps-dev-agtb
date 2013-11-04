/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('DragdropAttachments', ['view'], {
            events: {
                'dragenter .attachable': 'expandNewPost',
                'dragover .attachable': 'dragoverNewPost',
                'dragleave .attachable': 'shrinkNewPost',
                'drop .attachable': 'dropAttachment'
            },

            expandNewPost: function(event) {
                this.$(event.currentTarget).addClass("dragdrop");
                return false;
            },

            dragoverNewPost: function(event) {
                return false;
            },

            shrinkNewPost: function(event) {
                event.stopPropagation();
                event.preventDefault();
                this.$(event.currentTarget).removeClass("dragdrop");
                return false;
            },

            dropAttachment: function(event) {
                var text = $.trim(event.dataTransfer.getData("text")),
                    container = this.$(event.currentTarget);
                this.shrinkNewPost(event);

                if (text.length) {
                    container.append(' ' + text).trigger('change');
                }

                _.each(event.dataTransfer.files, function(file, i) {
                    var fileReader = new FileReader();

                    // Set up the callback for the FileReader.
                    fileReader.onload = (function(file, view) {
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

                            view.dragdrop_attachments = view.dragdrop_attachments || {};
                            view.dragdrop_attachments[unique] = file;
                            container = $("<div class='activitystream-pending-attachment' id='" + unique + "'></div>");

                            // TODO: Review creation of inline HTML
                            $('<a class="close">&times;</a>').on('click',function(e) {
                                container.trigger('close');
                            }).appendTo(container);

                            container.append(file.name + " (" + size + " " + sizes[size_index] + ")");

                            if (file.type.indexOf("image/") !== -1) {
                                container.append("<img style='display:block;' src='" + e.target.result + "' />");
                            } else {
                                container.append("<div>No preview available</div>");
                            }

                            container.appendTo(view.$(event.currentTarget).parent());
                            container.on('close', function() {
                                $(this).remove();
                                delete view.dragdrop_attachments[container.attr('id')];
                                view.trigger('attachments:remove');
                            });

                            view.trigger('attachments:add');
                        };
                    })(file, this);

                    fileReader.readAsDataURL(file);
                }, this);

                event.stopPropagation();
                event.preventDefault();
            },

            getAttachments: function() {
                return this.dragdrop_attachments || {};
            },

            clearAttachments: function() {
                this.$(".activitystream-pending-attachment").trigger('close');
                this.dragdrop_attachments = {};
            },

            onAttach: function(component, plugin) {
                component.on('render', function() {
                    this.$(".attachable").attr('dropzone', 'copy');
                });

                component.on('attachments:process', function() {
                    var self = this,
                        attachments = this.getAttachments(),
                        callback = _.after(_.size(attachments), this.clearAttachments);

                    component.trigger('attachments:start');

                    _.each(attachments, function(file) {
                        var note = app.data.createBean('Notes');
                        note.set('name', file.name);
                        async.waterfall([
                            function(callback) {
                                note.save(null, {
                                    success: function(noteModel) {
                                        callback(null, noteModel);
                                    }
                                });
                            },
                            function(note, callback) {
                                var data = new FormData(),
                                url = app.api.buildFileURL({
                                    module: note.module,
                                    id: note.id,
                                    field: 'filename'
                                });

                                data.append("filename", file);
                                data.append('OAuth-Token', app.api.getOAuthToken());
                                $.ajax({
                                    url: url,
                                    type: "POST",
                                    data: data,
                                    processData: false,
                                    contentType: false
                                }).then(function() {
                                    callback(null);
                                });
                            },
                            function(callback) {
                                var parentId = self.context.parent.get("model").id,
                                    parentType = self.context.parent.get("model").module,
                                    activity = app.data.createBean('Activities'),
                                    payload = {
                                        activity_type: "attach",
                                        parent_id: parentId || null,
                                        parent_type: parentType || null,
                                        data: {
                                            noteId: note.id,
                                            filename: file.name,
                                            mimetype: file.type,
                                            size: file.size
                                        }
                                    };

                                activity.save(payload, {
                                    success: function(activityModel) {
                                        self.collection.add(activityModel);
                                        callback(null, activityModel);
                                    }
                                });
                            }
                        ], function(err, activity) {
                            component.trigger('attachments:end');
                            if (err) {
                                var errorMessage = app.lang.getAppString('LBL_EMAIL_ATTACHMENT_UPLOAD_FAILED');
                                app.alert.show('upload_error', errorMessage);
                            } else {
                                self.context.reloadData({recursive: false});
                                self.clearAttachments.call(self);
                            }
                        });
                    });
                });
            }
        });
    });
})(SUGAR.App);
