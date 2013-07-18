({
    events: {
        'click .addPost': 'addPost',
        'change div[data-placeholder]': 'checkPlaceholder',
        'keydown div[data-placeholder]': 'checkPlaceholder',
        'keypress div[data-placeholder]': 'checkPlaceholder',
        'input div[data-placeholder]': 'checkPlaceholder'
    },

    className: "omnibar",

    plugins: ['dragdrop_attachments', 'taggable'],

    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        // Assets for the activity stream post avatar
        this.user_id = app.user.get('id');
        this.full_name = app.user.get('full_name');
        this.picture_url = app.user.get('picture') ? app.api.buildFileURL({
            module: 'Users',
            id: this.user_id,
            field: 'picture'
        }) : '';
    },

    /**
     * Initialize taggable plugin so that it knows which record the tags are
     * associated with.
     */
    bindDataChange: function() {
        if (this.context.parent) {
            this.context.parent.on('change', function(context) {
                var moduleName = context.get('module'),
                    modelId = context.get('model').get('id');

                this.setTaggableRecord(moduleName, modelId);
            }, this);
        }
        app.view.View.prototype.bindDataChange.call(this);
    },

    /**
     * Remove events added in bindDataChange().
     */
    unbindData: function() {
        if (this.context.parent) {
            this.context.parent.off(null, null, this);
        }
        app.view.View.prototype.unbindData.call(this);
    },

    /**
     * Creates a new post.
     */
    addPost: function() {
        var self = this,
            parentId = this.context.parent.get("model").id,
            parentType = this.context.parent.get("model").module,
            attachments = this.$('.activitystream-pending-attachment'),
            $submitButton = this.$('button.addPost'),
            payload = {
                activity_type: "post",
                parent_id: parentId || null,
                parent_type: parentType !== "Activities" ? parentType : null,
                data: {}
            },
            bean;

        if (!$submitButton.hasClass('disabled')) {
            payload.data = this.getPost();

            if (payload.data.value && (payload.data.value.length > 0)) {
                $submitButton.addClass('disabled');
                bean = app.data.createBean('Activities');
                bean.save(payload, {
                    success: function(model) {
                        self.$('div.sayit')
                            .empty()
                            .trigger('change')
                            .focus();

                        model.set('picture', app.user.get('picture'));
                        self.collection.add(model);

                        self.layout.prependPost(model);
                    },
                    complete: function() {
                        $submitButton.removeClass('disabled');
                    },
                    showAlerts: true
                });
            }

            this.trigger("attachments:process");
        }
    },

    /**
     * Retrieve the post entered inside content editable and translate any tags into text format
     * so that it can be saved in the database as JSON string.
     *
     * @returns {String}
     */
    getPost: function() {
        return this.unformatTags(this.$('div.sayit'));
    },

    checkPlaceholder: function(e) {
        // We can't use any of the jQuery methods or use the dataset property to
        // set this attribute because they don't seem to work in IE 10. Dataset
        // isn't supported in IE 10 at all.
        var el = e.currentTarget;
        if (el.textContent) {
            el.setAttribute('data-hide-placeholder', 'true');
        } else {
            el.removeAttribute('data-hide-placeholder');
        }
    }
})
