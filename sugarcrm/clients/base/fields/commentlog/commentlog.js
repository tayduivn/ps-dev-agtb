/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Fields.Base.CommentLogField
 * @alias SUGAR.App.view.fields.BaseCommentLogField
 * @extends View.Fields.Base.BaseField
 */
({
    fieldTag: 'textarea',

    /**
     * Called when initializing the field
     * @param options
     */
    initialize: function(options) {
        this._super('initialize', [options]);
    },

    /**
     * Called when rendering the field
     * @private
     */
    _render: function() {
        this.showCommentLog();
        this._super('_render'); // everything showing in the UI should be done before this line.
    },

    /**
     * Called when formatting the value for display
     * @param value
     */
    format: function(value) {
        return value;
    },

    /**
     * Builds model for handlebar to show pass commentlog messages in record view.
     * This should only be called when there is need to render past messages, only
     * when this.getFormattedValue() returns the data format for message.
     */
    showCommentLog: function() {
        var collection = this.model.get('commentlog');

        if (!collection) {
            return;
        }
        var comments = collection.models;

        if (comments) {
            this.msgs = [];
            // add readable time and user link to users
            _.each(comments, function(commentModel) {
                var msg = {
                    entry: commentModel.get('entry'),
                    created_by_name: commentModel.get('created_by_name'),
                };

                // to date display format
                var enteredDate = app.date(commentModel.get('date_entered'));
                if (enteredDate.isValid()) {
                    msg.entered_date = enteredDate.formatUser();
                }

                var link = commentModel.get('created_by_link');
                if (link && link.id) {
                    if (app.acl.hasAccess('view', 'Users', {acls: link._acl})) {
                        msg.href = '#' + app.router.buildRoute('Users', link.id, 'detail');
                    }
                } else if (commentModel.has('created_by')) {
                    msg.href = '#' + app.router.buildRoute('Users', commentModel.get('created_by'), 'detail');
                }

                if (commentModel === this._newEntryModel) {
                    msg.isNew = true;
                }
                this.msgs.push(msg);
            }, this);
        }

        this.newValue = this._newEntryModel ? this._newEntryModel.get('entry') : '';
    },

    /**
     * Called when unformatting the value for storage
     * @param value
     */
    unformat: function(value) {
        return value;
    },

    /**
     * @inheritdoc
     */
    bindDomChange: function() {
        if (!(this.model instanceof Backbone.Model)) {
            return;
        }

        var el = this.$el.find(this.fieldTag);

        var self = this;

        el.on('change', function() {
            var value = self.unformat(el.val());

            if (!self._newEntryModel) {
                var collectionField = self.model.get('commentlog');

                if (!collectionField) {
                    self.model.set(self.name, []);
                    collectionField = self.model.get('commentlog');
                }

                self._newEntryModel = app.data.createRelatedBean(self.model, null, 'commentlog_link', {
                    entry: value,
                    _link: 'commentlog_link',
                });

                collectionField.add(self._newEntryModel);
            }

            self._newEntryModel.set('entry', value);
        });
    },

    /**
     * @inheritdoc
     */
    bindDataChange: function() {
        if (this.model) {
            var collectionField = this.model.get(this.name);
            if (collectionField) {
                this.listenTo(collectionField, 'reset', function() {
                    this.newValue = this._newEntryModel = null;
                });
            }
            this.model.on('change:' + this.name, function(model, value) {
                if (this.action !== 'edit') {
                    this.newValue = this._newEntryModel = null;
                }
                this.render();
            }, this);
        }
    },
})
