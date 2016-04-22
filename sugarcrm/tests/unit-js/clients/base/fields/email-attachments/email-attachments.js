describe('Base.EmailAttachments', function() {
    var app;
    var context;
    var field;
    var model;
    var sandbox;

    beforeEach(function() {
        sandbox = sinon.sandbox.create();

        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('email-attachments', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('email-attachments', 'field', 'base', 'edit');
        SugarTest.testMetadata.set();
        app.data.declareModel('Notes', {});

        context = app.context.getContext({module: 'Emails'});
        context.prepare();
        model = context.get('model');
    });

    afterEach(function() {
        sandbox.restore();

        field.dispose();
        app.cache.cutAll();
        app.view.reset();

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('an existing email', function() {
        it('should initialize with existing attachments', function() {
            var notes = app.data.createBeanCollection('Notes');
            var attachments = [{
                id: _.uniqueId(),
                name: 'Disclosure Agreement.pdf',
                filename: 'Disclosure Agreement.pdf',
                file_mime_type: 'application/pdf'
            }, {
                id: _.uniqueId(),
                name: 'logo.jpg',
                filname: 'logo.jpg',
                file_mime_type: 'image/jpg'
            }];

            model.set('id', _.uniqueId());
            sandbox.stub(app.data, 'createBeanCollection').withArgs('Notes').returns(notes);
            sandbox.stub(notes, 'fetch', function(options) {
                expect(options.filter).toEqual({
                    filter: [{
                        email_id: {
                            '$equals': model.get('id')
                        }
                    }]
                });

                notes.add(attachments);
                options.success(notes);
            });

            field = SugarTest.createField({
                name: 'attachments',
                type: 'email-attachments',
                viewName: 'detail',
                module: 'Emails',
                model: model,
                context: context
            });

            expect(field._attachments.length).toBe(attachments.length);
        });
    });

    describe('getting the formatted value', function() {
        it('should return an array of objects without any attachments that are to be unlinked', function() {
            var value;
            var file1 = new Backbone.Model({
                _action: 'create',
                _url: null,
                _file: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_source: 'Uploaded'
            });
            var file2 = new Backbone.Model({
                _url: 'url/to/download/file',
                _file: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_source: 'Uploaded'
            });
            var file3 = new Backbone.Model({
                _action: 'placeholder',
                _url: null,
                _file: _.uniqueId(),
                name: 'quote.pdf'
            });
            var file4 = new Backbone.Model({
                _action: 'delete',
                _url: 'url/to/download/file',
                _file: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_source: 'Uploaded'
            });

            field = SugarTest.createField({
                name: 'attachments',
                type: 'email-attachments',
                viewName: 'detail',
                module: 'Emails',
                model: model,
                context: context
            });

            field._attachments.add([file1, file2, file3, file4]);

            value = field.getFormattedValue();
            expect(value).toEqual([file1.toJSON(), file2.toJSON(), file3.toJSON()]);
        });
    });

    describe('detail mode', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'attachments',
                type: 'email-attachments',
                viewName: 'detail',
                module: 'Emails',
                model: model,
                context: context
            });
            field.render();
        });

        it('should download an attachment', function() {
            var $file;
            var file = new Backbone.Model({
                _url: 'url/to/download/file',
                _file: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_source: 'Uploaded'
            });
            field._attachments.add(file);
            sandbox.stub(app.api, 'fileDownload');

            $file = field.$('[data-action=download]');
            $file.click();

            expect(app.api.fileDownload).toHaveBeenCalledWith(file.get('_url'));
        });
    });

    describe('edit mode', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'attachments',
                type: 'email-attachments',
                viewName: 'edit',
                module: 'Emails',
                model: model,
                context: context
            });
            field.render();
        });

        it('should not allow the dropdown to open', function() {
            var event = $.Event('select2-opening');
            sandbox.spy(event, 'preventDefault');

            field.$(field.fieldTag).trigger(event);
            expect(event.preventDefault).toHaveBeenCalled();
        });

        describe('add an attachment', function() {
            it('should open the file dialog', function() {
                sandbox.stub(field, '_openFilePicker');
                field.view.trigger('email_attachments:file:pick');
                expect(field._openFilePicker).toHaveBeenCalled();
            });

            it('should add an uploaded file', function() {
                var fileName = 'quote.pdf';
                var $file = $('<input/>', {value: fileName});
                var attachment;
                var id = _.uniqueId();
                var flag;

                sandbox.stub(field, '_getFileInput').returns($file);
                sandbox.stub(app.api, 'file', function(method, data, $files, callbacks, options) {
                    var response = {
                        filename: {
                            guid: fileName
                        },
                        record: {
                            id: id,
                            deleted: false,
                            file_mime_type: 'application/pdf',
                            filename: fileName,
                            _module: 'Notes'
                        }
                    };
                    var placeholder;

                    expect(method).toBe('create');
                    expect(data.id).toBe('temp');
                    expect(data.module).toBe('Notes');
                    expect(data.field).toBe('filename');
                    expect(options.temp).toBe(true);
                    expect(options.iframe).toBe(true);
                    expect(options.deleteIfFails).toBe(true);
                    expect(options.htmlJsonFormat).toBe(true);

                    expect(field._attachments.length).toBe(1);
                    placeholder = field._attachments.at(0);
                    expect(placeholder.get('_action')).toBe('placeholder');
                    expect(placeholder.get('name')).toBe(fileName);

                    callbacks.success(response);
                    callbacks.complete();

                    flag = true;
                });

                runs(function() {
                    flag = false;
                    field.$('input[type=file]').change();
                });

                waitsFor(function() {
                    return flag;
                }, 'The file to be uploaded', 100);

                runs(function() {
                    expect($file.val()).toEqual('');
                    expect(field._attachments.length).toBe(1);
                    attachment = field._attachments.at(0);
                    expect(attachment.get('_action')).toBe('create');
                    expect(attachment.get('_file')).toBe(id);
                    expect(attachment.get('name')).toBe(fileName);
                    expect(attachment.get('filename')).toBe(fileName);
                    expect(attachment.get('file_mime_type')).toBe('application/pdf');
                    expect(attachment.get('file_source')).toBe('Uploaded');
                });
            });

            it('should alert the user when the uploaded file is too large', function() {
                var fileName = 'quote.pdf';
                var $file = $('<input/>', {value: fileName});
                var error = new SUGAR.Api.HttpError({
                    xhr: {
                        status: 413
                    }
                });
                var flag;

                sandbox.spy(app.alert, 'show');
                sandbox.spy(app.error, 'handleHttpError');
                sandbox.spy(app.lang, 'get');
                sandbox.stub(field, '_getFileInput').returns($file);
                sandbox.stub(app.api, 'file', function(method, data, $files, callbacks, options) {
                    var placeholder;

                    expect(method).toBe('create');
                    expect(data.id).toBe('temp');
                    expect(data.module).toBe('Notes');
                    expect(data.field).toBe('filename');
                    expect(options.temp).toBe(true);
                    expect(options.iframe).toBe(true);
                    expect(options.deleteIfFails).toBe(true);
                    expect(options.htmlJsonFormat).toBe(true);

                    expect(field._attachments.length).toBe(1);
                    placeholder = field._attachments.at(0);
                    expect(placeholder.get('_action')).toBe('placeholder');
                    expect(placeholder.get('name')).toBe(fileName);

                    callbacks.error(error);
                    callbacks.complete();

                    flag = true;
                });

                runs(function() {
                    flag = false;
                    field.$('input[type=file]').change();
                });

                waitsFor(function() {
                    return flag;
                }, 'The file to be uploaded', 100);

                runs(function() {
                    expect($file.val()).toEqual('');
                    expect(field._attachments.length).toBe(0);
                    expect(error.handled).toBe(true);
                    expect(app.alert.show).toHaveBeenCalled();
                    expect(app.lang.get).toHaveBeenCalledWith('ERROR_MAX_FILESIZE_EXCEEDED');
                    expect(app.error.handleHttpError).toHaveBeenCalledWith(error);
                });
            });

            it('should add a document', function() {
                var selection = {
                    id: _.uniqueId(),
                    name: 'Contract',
                    value: 'Contract'
                };
                var doc;
                var attachment;

                app.drawer = {
                    open: sandbox.stub().callsArgWith(1, selection)
                };

                app.data.declareModel('Documents', {});
                doc = app.data.createBean('Documents', {
                    id: selection.id,
                    name: selection.name
                });
                sandbox.stub(app.data, 'createBean').withArgs('Documents').returns(doc);
                sandbox.stub(doc, 'fetch', function(options) {
                    var placeholder;

                    expect(field._attachments.length).toBe(1);
                    placeholder = field._attachments.at(0);
                    expect(placeholder.get('_action')).toBe('placeholder');
                    expect(placeholder.get('name')).toBe('Contract');

                    doc.set({
                        document_revision_id: _.uniqueId(),
                        filename: 'Contract.pdf',
                        file_mime_type: 'application/pdf'
                    });

                    options.success(doc);
                    options.complete();
                });

                field.view.trigger('email_attachments:document:pick');

                expect(field._attachments.length).toBe(1);
                attachment = field._attachments.at(0);
                expect(attachment.get('_action')).toBe('create');
                expect(attachment.get('_url')).toBeNull();
                expect(attachment.get('_file')).toBe(doc.get('document_revision_id'));
                expect(attachment.get('name')).toBe('Contract.pdf');
                expect(attachment.get('filename')).toBe('Contract.pdf');
                expect(attachment.get('file_mime_type')).toBe('application/pdf');
                expect(attachment.get('file_source')).toBe('Document');

                app.drawer = null;
            });

            it('should add attachments from a template', function() {
                var template;
                var notes;
                var attachment;
                var templateAttachments = [{
                    id: _.uniqueId(),
                    name: 'Disclosure Agreement.pdf',
                    filename: 'Disclosure Agreement.pdf',
                    file_mime_type: 'application/pdf'
                }, {
                    id: _.uniqueId(),
                    name: 'NDA.pdf',
                    filname: 'NDA.pdf',
                    file_mime_type: 'application/pdf'
                }, {
                    id: _.uniqueId(),
                    name: 'logo.jpg',
                    filname: 'logo.jpg',
                    file_mime_type: 'image/jpg'
                }];

                // New uploaded attachment should still be linked after adding
                // template attachments.
                var file1 = new Backbone.Model({
                    _action: 'create',
                    _url: null,
                    _file: _.uniqueId(),
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_source: 'Uploaded'
                });

                // Placeholder attachment should still remain after adding
                // template attachments. This placeholder could be for a
                // document that has not yet finished fetching.
                var file2 = new Backbone.Model({
                    _action: 'placeholder',
                    _url: null,
                    _file: _.uniqueId(),
                    name: 'quote.pdf'
                });

                // Existing uploaded attachment to be removed should still be
                // unlinked after adding template attachments.
                var file3 = new Backbone.Model({
                    _action: 'delete',
                    _url: 'url/to/download/file',
                    _file: _.uniqueId(),
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_source: 'Uploaded'
                });

                // Existing template attachment to be removed should still be
                // unlinked after adding template attachments.
                var file4 = new Backbone.Model({
                    _action: 'delete',
                    _url: 'url/to/download/file',
                    _file: _.uniqueId(),
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_source: 'Template'
                });

                // New template attachment should be removed before adding
                // attachments from another template. This case occurs when
                // the user changes templates multiple times during a single
                // editing session.
                var file5 = new Backbone.Model({
                    _action: 'create',
                    _url: null,
                    _file: _.uniqueId(),
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_source: 'Template'
                });

                // Existing template attachments should be unlinked.
                var file6 = new Backbone.Model({
                    _url: 'url/to/download/file',
                    _file: _.uniqueId(),
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_source: 'Template'
                });

                // Existing template attachments should be unlinked.
                var file7 = new Backbone.Model({
                    _url: 'url/to/download/file',
                    _file: _.uniqueId(),
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_source: 'Template'
                });

                field._attachments.add([file1, file2, file3, file4, file5, file6, file7]);

                app.data.declareModel('EmailTemplates', {});
                template = app.data.createBean('EmailTemplates', {
                    id: _.uniqueId(),
                    name: 'We have quite the offer for you!'
                });
                notes = app.data.createBeanCollection('Notes');
                sandbox.stub(app.data, 'createBeanCollection').withArgs('Notes').returns(notes);
                sandbox.stub(notes, 'fetch', function(options) {
                    var placeholder;

                    expect(options.filter).toEqual({
                        filter: [{
                            email_id: {
                                '$equals': template.get('id')
                            }
                        }]
                    });

                    expect(field._attachments.length).toBe(8);
                    placeholder = field._attachments.at(field._attachments.length - 1);
                    expect(placeholder.get('_action')).toBe('placeholder');
                    expect(placeholder.get('name')).toBe(template.get('name'));

                    notes.add(templateAttachments);

                    options.success(notes);
                    options.complete();
                });

                field.view.trigger('email_attachments:template:add', template);

                expect(field._attachments.length).toBe(9);

                attachment = field._attachments.where({_file: file1.get('_file')});
                attachment = _.first(attachment);
                expect(attachment.get('_action')).toBe('create');

                attachment = field._attachments.where({_file: file2.get('_file')});
                attachment = _.first(attachment);
                expect(attachment.get('_action')).toBe('placeholder');

                attachment = field._attachments.where({_file: file3.get('_file')});
                attachment = _.first(attachment);
                expect(attachment.get('_action')).toBe('delete');

                attachment = field._attachments.where({_file: file4.get('_file')});
                attachment = _.first(attachment);
                expect(attachment.get('_action')).toBe('delete');

                attachment = field._attachments.where({_file: file5.get('_file')});
                expect(attachment).toEqual([]);

                attachment = field._attachments.where({_file: file6.get('_file')});
                attachment = _.first(attachment);
                expect(attachment.get('_action')).toBe('delete');

                attachment = field._attachments.where({_file: file7.get('_file')});
                attachment = _.first(attachment);
                expect(attachment.get('_action')).toBe('delete');

                _.each(templateAttachments, function(templateAttachment) {
                    var attachment = field._attachments.where({_file: templateAttachment.id});
                    attachment = _.first(attachment);
                    expect(attachment.get('_action')).toBe('create');
                    expect(attachment.get('_url')).toBeNull();
                    expect(attachment.get('name')).toBe(templateAttachment.filename);
                    expect(attachment.get('filename')).toBe(templateAttachment.filename);
                    expect(attachment.get('file_mime_type')).toBe(templateAttachment.file_mime_type);
                    expect(attachment.get('file_source')).toBe('Template');
                });
            });
        });

        describe('remove an attachment', function() {
            var id;
            var event;

            beforeEach(function() {
                id = _.uniqueId();
                event = $.Event('select2-removed', {val: id});
            });

            it('should remove a new attachment', function() {
                var file = new Backbone.Model({
                    _action: 'create',
                    _url: null,
                    _file: id,
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_source: 'Uploaded'
                });

                field._attachments.add(file);
                expect(field._attachments.length).toBe(1);

                field.$(field.fieldTag).trigger(event);
                expect(field._attachments.length).toBe(0);
            });

            it('should remove an existing attachment', function() {
                var file = new Backbone.Model({
                    _url: 'url/to/download/file',
                    _file: id,
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_source: 'Uploaded'
                });

                field._attachments.add(file);
                expect(field._attachments.length).toBe(1);

                field.$(field.fieldTag).trigger(event);
                expect(field._attachments.length).toBe(1);
                expect(field._attachments.at(0).get('_action')).toBe('delete');
            });

            it('should remove a placeholder attachment', function() {
                var file = new Backbone.Model({
                    _action: 'placeholder',
                    _url: null,
                    _file: id,
                    name: 'quote.pdf'
                });

                field._attachments.add(file);
                expect(field._attachments.length).toBe(1);

                field.$(field.fieldTag).trigger(event);
                expect(field._attachments.length).toBe(0);
            });

            it('should remove only the specified attachment', function() {
                var create = new Backbone.Model({
                    _action: 'create',
                    _url: null,
                    _file: id,
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_source: 'Uploaded'
                });
                var existing = new Backbone.Model({
                    _url: 'url/to/download/file',
                    _file: _.uniqueId(),
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_source: 'Uploaded'
                });
                var attachment;

                field._attachments.add([create, existing]);
                expect(field._attachments.length).toBe(2);

                field.$(field.fieldTag).trigger(event);
                expect(field._attachments.length).toBe(1);

                attachment = field._attachments.at(0);
                expect(attachment.get('_file')).toBe(existing.get('_file'));
            });

            it('should unlink only the specified attachment', function() {
                var create = new Backbone.Model({
                    _action: 'create',
                    _url: null,
                    _file: _.uniqueId(),
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_source: 'Uploaded'
                });
                var existing = new Backbone.Model({
                    _url: 'url/to/download/file',
                    _file: id,
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_source: 'Uploaded'
                });
                var attachment;

                field._attachments.add([create, existing]);
                expect(field._attachments.length).toBe(2);

                field.$(field.fieldTag).trigger(event);
                expect(field._attachments.length).toBe(2);

                attachment = field._attachments.at(0);
                expect(attachment.get('_file')).toBe(create.get('_file'));

                attachment = field._attachments.at(1);
                expect(attachment.get('_file')).toBe(id);
                expect(attachment.get('_action')).toBe('delete');
            });
        });
    });
});
