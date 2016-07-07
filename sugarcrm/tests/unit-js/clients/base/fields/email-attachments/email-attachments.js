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
                file_mime_type: 'application/pdf',
                file_size: 158589
            }, {
                id: _.uniqueId(),
                name: 'logo.jpg',
                filename: 'logo.jpg',
                file_mime_type: 'image/jpg',
                file_size: 158589,
                file_source: 'DocumentRevisions'
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
            var file1Guid = _.uniqueId();
            var file1 = new Backbone.Model({
                _action: 'create',
                _url: null,
                id: file1Guid,
                filename_guid: file1Guid,
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589
            });
            var file2 = new Backbone.Model({
                _url: 'url/to/download/file',
                id: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589
            });
            var file3 = new Backbone.Model({
                _action: 'placeholder',
                _url: null,
                id: _.uniqueId(),
                name: 'quote.pdf'
            });
            var file4 = new Backbone.Model({
                _action: 'delete',
                _url: 'url/to/download/file',
                id: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589
            });
            var file1Json = file1.toJSON();
            var file2Json = file2.toJSON();
            var file3Json = file3.toJSON();

            // The file sizes that SUGAR.App.utils#getReadableFileSize will
            // return.
            file1Json.file_size = '159K';
            file2Json.file_size = '159K';
            file3Json.file_size = '0K';

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
            expect(value).toEqual([file1Json, file2Json, file3Json]);
        });
    });

    describe('setting up the model value', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'attachments',
                type: 'email-attachments',
                viewName: 'detail',
                module: 'Emails',
                model: model,
                context: context
            });
        });

        it('should set the create property on the model value', function() {
            var file1Id = _.uniqueId();
            var file1 = new Backbone.Model({
                _action: 'create',
                _url: null,
                id: file1Id,
                filename_guid: file1Id,
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf'
            });
            var file2 = new Backbone.Model({
                _action: 'create',
                _url: null,
                id: _.uniqueId(),
                upload_id: _.uniqueId(),
                name: 'logo.jpg',
                filename: 'logo.jpg',
                file_mime_type: 'image/jpg',
                file_source: 'EmailTemplates'
            });
            var attachments;

            field._attachments.add([file1, file2]);

            attachments = field.model.get(field.name).create;
            expect(attachments.length).toEqual(2);
            expect(attachments[0].filename_guid).toEqual(file1Id);
            expect(attachments[0].id).toBeUndefined();
            expect(attachments[1].upload_id).toEqual(file2.get('upload_id'));
            expect(attachments[1].id).toBeUndefined();
        });

        it('should set the delete property on the model value', function() {
            var file = new Backbone.Model({
                _action: 'delete',
                _url: null,
                id: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf'
            });

            field._attachments.add(file);
            expect(field.model.get(field.name)['delete']).toEqual([file.get('id')]);
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
                id: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589
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
                            file_size: 158589,
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
                    expect(attachment.get('id')).toBe(id);
                    expect(attachment.get('filename_guid')).toBe(id);
                    expect(attachment.get('name')).toBe(fileName);
                    expect(attachment.get('filename')).toBe(fileName);
                    expect(attachment.get('file_mime_type')).toBe('application/pdf');
                    expect(attachment.get('file_size')).toBe(158589);
                    expect(attachment.get('file_source')).toBeUndefined();
                });
            });

            it('should alert the user when the uploaded file is too large', function() {
                var fileName = 'quote.pdf';
                var $file = $('<input/>', {value: fileName});
                var error = {
                    error: 'request_too_large'
                };
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
                        latest_revision_file_mime_type: 'application/pdf',
                        latest_revision_file_size: 158589
                    });

                    options.success(doc);
                    options.complete();
                });

                field.view.trigger('email_attachments:document:pick');

                expect(field._attachments.length).toBe(1);
                attachment = field._attachments.at(0);
                expect(attachment.get('_action')).toBe('create');
                expect(attachment.get('_url')).toBeNull();
                expect(attachment.get('id')).toBe(doc.get('document_revision_id'));
                expect(attachment.get('upload_id')).toBe(doc.get('document_revision_id'));
                expect(attachment.get('name')).toBe('Contract.pdf');
                expect(attachment.get('filename')).toBe('Contract.pdf');
                expect(attachment.get('file_mime_type')).toBe('application/pdf');
                expect(attachment.get('file_size')).toBe(158589);
                expect(attachment.get('file_source')).toBe('DocumentRevisions');

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
                    file_mime_type: 'application/pdf',
                    file_size: 158589
                }, {
                    id: _.uniqueId(),
                    name: 'NDA.pdf',
                    filename: 'NDA.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589
                }, {
                    id: _.uniqueId(),
                    name: 'logo.jpg',
                    filename: 'logo.jpg',
                    file_mime_type: 'image/jpg',
                    file_size: 158589
                }];

                // New uploaded attachment should still be linked after adding
                // template attachments.
                var file1Guid = _.uniqueId();
                var file1 = new Backbone.Model({
                    _action: 'create',
                    _url: null,
                    id: file1Guid,
                    filename_guid: file1Guid,
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589
                });

                // Placeholder attachment should still remain after adding
                // template attachments. This placeholder could be for a
                // document that has not yet finished fetching.
                var file2 = new Backbone.Model({
                    _action: 'placeholder',
                    _url: null,
                    id: _.uniqueId(),
                    name: 'quote.pdf'
                });

                // Existing uploaded attachment to be removed should still be
                // unlinked after adding template attachments.
                var file3 = new Backbone.Model({
                    _action: 'delete',
                    _url: 'url/to/download/file',
                    id: _.uniqueId(),
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589
                });

                // Existing template attachment to be removed should still be
                // unlinked after adding template attachments.
                var file4 = new Backbone.Model({
                    _action: 'delete',
                    _url: 'url/to/download/file',
                    id: _.uniqueId(),
                    upload_id: _.uniqueId(),
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_source: 'EmailTemplates'
                });

                // New template attachment should be removed before adding
                // attachments from another template. This case occurs when
                // the user changes templates multiple times during a single
                // editing session.
                var file5 = new Backbone.Model({
                    _action: 'create',
                    _url: null,
                    id: _.uniqueId(),
                    upload_id: _.uniqueId(),
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_source: 'EmailTemplates'
                });

                // Existing template attachments should be unlinked.
                var file6 = new Backbone.Model({
                    _url: 'url/to/download/file',
                    id: _.uniqueId(),
                    upload_id: _.uniqueId(),
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_source: 'EmailTemplates'
                });

                // Existing template attachments should be unlinked.
                var file7 = new Backbone.Model({
                    _url: 'url/to/download/file',
                    id: _.uniqueId(),
                    upload_id: _.uniqueId(),
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_source: 'EmailTemplates'
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
                    // No placeholder attachment.
                    expect(field._attachments.length).toBe(7);
                    expect(options.filter).toEqual({
                        filter: [{
                            email_id: {
                                '$equals': template.get('id')
                            }
                        }]
                    });

                    notes.add(templateAttachments);

                    options.success(notes);
                    options.complete();
                });

                field.view.trigger('email_attachments:template:add', template);

                expect(field._attachments.length).toBe(9);

                attachment = field._attachments.where({id: file1.get('id')});
                attachment = _.first(attachment);
                expect(attachment.get('_action')).toBe('create');

                attachment = field._attachments.where({id: file2.get('id')});
                attachment = _.first(attachment);
                expect(attachment.get('_action')).toBe('placeholder');

                attachment = field._attachments.where({id: file3.get('id')});
                attachment = _.first(attachment);
                expect(attachment.get('_action')).toBe('delete');

                attachment = field._attachments.where({id: file4.get('id')});
                attachment = _.first(attachment);
                expect(attachment.get('_action')).toBe('delete');

                attachment = field._attachments.where({id: file5.get('id')});
                expect(attachment).toEqual([]);

                attachment = field._attachments.where({id: file6.get('id')});
                attachment = _.first(attachment);
                expect(attachment.get('_action')).toBe('delete');

                attachment = field._attachments.where({id: file7.get('id')});
                attachment = _.first(attachment);
                expect(attachment.get('_action')).toBe('delete');

                _.each(templateAttachments, function(templateAttachment) {
                    var attachment = field._attachments.where({id: templateAttachment.id});
                    attachment = _.first(attachment);
                    expect(attachment.get('_action')).toBe('create');
                    expect(attachment.get('_url')).toBeNull();
                    expect(attachment.get('upload_id')).toBe(templateAttachment.id);
                    expect(attachment.get('name')).toBe(templateAttachment.filename);
                    expect(attachment.get('filename')).toBe(templateAttachment.filename);
                    expect(attachment.get('file_mime_type')).toBe(templateAttachment.file_mime_type);
                    expect(attachment.get('file_size')).toBe(templateAttachment.file_size);
                    expect(attachment.get('file_source')).toBe('EmailTemplates');
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
                    id: id,
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589
                });

                field._attachments.add(file);
                expect(field._attachments.length).toBe(1);

                field.$(field.fieldTag).trigger(event);
                expect(field._attachments.length).toBe(0);
            });

            it('should remove an existing attachment', function() {
                var file = new Backbone.Model({
                    _url: 'url/to/download/file',
                    id: id,
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589
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
                    id: id,
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
                    id: id,
                    filename_guid: id,
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589
                });
                var existing = new Backbone.Model({
                    _url: 'url/to/download/file',
                    id: _.uniqueId(),
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589
                });
                var attachment;

                field._attachments.add([create, existing]);
                expect(field._attachments.length).toBe(2);

                field.$(field.fieldTag).trigger(event);
                expect(field._attachments.length).toBe(1);

                attachment = field._attachments.at(0);
                expect(attachment.get('id')).toBe(existing.get('id'));
            });

            it('should unlink only the specified attachment', function() {
                var createId = _.uniqueId();
                var create = new Backbone.Model({
                    _action: 'create',
                    _url: null,
                    id: createId,
                    filename_guid: createId,
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589
                });
                var existing = new Backbone.Model({
                    _url: 'url/to/download/file',
                    id: id,
                    name: 'quote.pdf',
                    filename: 'quote.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589
                });
                var attachment;

                field._attachments.add([create, existing]);
                expect(field._attachments.length).toBe(2);

                field.$(field.fieldTag).trigger(event);
                expect(field._attachments.length).toBe(2);

                attachment = field._attachments.at(0);
                expect(attachment.get('id')).toBe(createId);

                attachment = field._attachments.at(1);
                expect(attachment.get('id')).toBe(id);
                expect(attachment.get('_action')).toBe('delete');
            });
        });
    });
});
