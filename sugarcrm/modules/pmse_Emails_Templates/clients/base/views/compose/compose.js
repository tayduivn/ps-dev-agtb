/**
 * View for the email composition layout that contains the HTML editor.
 */
({
    extendsFrom: 'RecordView',
//    className: 'compose',
//    _lastSelectedSignature: null,
//    ATTACH_TYPE_SUGAR_DOCUMENT: 'document',
//    ATTACH_TYPE_TEMPLATE: 'template',
    MIN_EDITOR_HEIGHT: 300,
    EDITOR_RESIZE_PADDING: 5,
    buttons: null,

    initialize: function(options) {
        _.bindAll(this);
        var self = this;
        this._super("initialize", [options]);
//        options.meta = _.extend({}, app.metadata.getView(null, 'compose'), options.meta);
//        app.view.View.prototype.initialize.call(this, options);
        this.events = _.extend({}, this.events, {
//            'click .cc-option': 'showSenderOptionField',
//            'click .bcc-option': 'showSenderOptionField',
//            'click [name=draft_button]': 'saveAsDraft',
            'click [name=save_button]': 'save',
            'click [name=save_buttonExit]': 'saveExit',
            'click [name=cancel_button]': 'cancel'
        });
//        this.context.on('actionbar:template_button:clicked', this.launchTemplateDrawer, this);
//        this.context.on('actionbar:attach_sugardoc_button:clicked', this.launchDocumentDrawer, this);
//        this.context.on("actionbar:signature_button:clicked", this.launchSignatureDrawer, this);
//        this.context.on('attachments:updated', this.toggleAttachmentVisibility, this);
        this.context.on('tinymce:oninit', this.handleTinyMceInit, this);
//        this.on('more-less:toggled', this.handleMoreLessToggled, this);
//        app.drawer.on('drawer:resize', this.resizeEditor, this);
        this.action = 'edit';
        this.createMode = true;
        this._lastSelectedSignature = app.user.getPreference("signature_default");



    },

    _render: function () {
        var self= this;
        var url,
            $editor;

        this._super("_render");
        if (this.createMode) {
            if (this.getField('name')) {
                this.setTitle(app.lang.get('LBL_PMSE_DASHLET_TITLE_EMAILTEMPLATE', this.module) + ' | ' + this.getField('name').value);
            }
        }
//        url = app.api.buildURL('pmse_Emails_Templates', 'modules/find', null, {'module': this.model.get('base_module')});
//        app.api.call('read', url, null, {
//                success:function (modules){
//                    console.log(modules);
//                    self.modulesList= modules;
//
//                }
//            }
//        );
        //console.log(this);
        //module = this.model.get('base_module');
        //console.log(module);
    },

    /**
     * Cancel and close the drawer
     */
    cancel: function() {
//            this.model.revertAttributes();
            this.toggleEdit(false);
            this.inlineEditMode = false;
            App.router.navigate('Home' , {trigger: true, replace: true });
    },

    /**
     * Send the email immediately or warn if user did not provide subject or body
     */
    save: function() {
//        this.model.doValidate(this.getFields(this.module), _.bind(this.validationCompleteApprove, this));
        this.model.doValidate(this.getFields(this.module), _.bind(function(isValid) {
            if (isValid) {
                this.validationCompleteApprove(this.model,false);
            }
        }, this));
    },
    validationCompleteApprove: function (model,exit) {
        var url, attributes, bodyHtml, subject;//, from_address;
        //console.log(this);
        url = App.api.buildURL('pmse_Emails_Templates', null, {id: this.context.attributes.modelId});
        bodyHtml = model.get('body_html');//bodyHtml = this.model.get('body_html');
        subject = model.get('subject');//subject = this.model.get('subject');
//        from_address = model.get('from_address');//from_address = this.model.get('from_address');
//        console.log(from_address);
        attributes = {
            body_html: bodyHtml,
            subject: subject,
            description:model.get('description'),//description:this.model.get('description'),
            name: model.get('name')//name: this.model.get('name'),
//            from_name: model.get('from_name'),//from_name: this.model.get('from_name'),
//            from_address: model.get('from_address')//from_address: this.model.get('from_address')
        };
        App.alert.show('upload', {level: 'process', title: 'LBL_SAVING', autoclose: false});
        App.api.call('update', url, attributes, {
            success: function (data) {
                //alert('update')
                App.alert.dismiss('upload');
                if(exit)
                {
                    model.revertAttributes();
                    App.router.redirect('Home');
                }
            },
            error: function (err) {
                App.alert.dismiss('upload');
            }
        });
    },
    saveExit: function() {
//        this.model.doValidate(this.getFields(this.module), _.bind(this.validationCompleteApprove, this));
        this.model.doValidate(this.getFields(this.module), _.bind(function(isValid) {
            if (isValid) {
                this.validationCompleteApprove(this.model,true);
            }
        }, this));
    },
    _dispose: function() {
        if (App.drawer) {
            App.drawer.off(null, null, this);
        }
        this._super("_dispose");
    },
    handleTinyMceInit: function() {
        this.resizeEditor();
    },
    /**
     * Resize the html editor based on height of the drawer it is in
     *
     * @param drawerHeight current height of the drawer or height the drawer will be after animations
     */
    resizeEditor: function(drawerHeight) {
        var $editor, headerHeight, recordHeight, showHideHeight, diffHeight, editorHeight, newEditorHeight;

        $editor = this.$('.mceLayout .mceIframeContainer iframe');
        //if editor not already rendered, cannot resize
        if ($editor.length === 0) {
            return;
        }

        drawerHeight = drawerHeight || app.drawer.getHeight();
        headerHeight = this.$('.headerpane').outerHeight(true);
        recordHeight = this.$('.record').outerHeight(true);
        showHideHeight = this.$('.show-hide-toggle').outerHeight(true);
        editorHeight = $editor.height();

        //calculate the space left to fill - subtracting padding to prevent scrollbar
        diffHeight = drawerHeight - headerHeight - recordHeight - showHideHeight - this.EDITOR_RESIZE_PADDING;

        //add the space left to fill to the current height of the editor to get a new height
        newEditorHeight = editorHeight + diffHeight;

        //maintain min height
        if (newEditorHeight < this.MIN_EDITOR_HEIGHT) {
            newEditorHeight = this.MIN_EDITOR_HEIGHT;
        }

        //set the new height for the editor
        $editor.height(newEditorHeight);
    }

})
