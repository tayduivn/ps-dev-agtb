describe('BaseEmailAttachmentsField', function() {
    var app;
    var clock;
    var context;
    var field;
    var model;
    var sandbox;
    var timestamp;

    beforeEach(function() {
        sandbox = sinon.sandbox.create();

        SugarTest.testMetadata.init();
        SugarTest.declareData('base', 'Emails', true, false);
        SugarTest.loadPlugin('NestedCollection');
        SugarTest.loadHandlebarsTemplate('email-attachments', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('email-attachments', 'field', 'base', 'edit');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

        // Use a fake timer because there appears to be a bug with PhantomJS
        // that causes the first call to `new Date()` to return the Epoch and
        // every subsequent call to return the correct date.
        // `SUGAR.Api#buildFileURL` uses `new Date()` to add a cache buster to
        // the URL when `cleanCache: true` is passed as an option. The value of
        // that query string parameter is very unreliable when comparing
        // strings, due to the aforementioned bug. The fake timer works around
        // the bug when a specific date is created for the start of the timer.
        timestamp = (new Date(2016, 7, 22, 0, 26, 17)).getTime();
        clock = sinon.useFakeTimers(timestamp);
    });

    afterEach(function() {
        clock.restore();
        sandbox.restore();

        field.dispose();
        app.cache.cutAll();
        app.view.reset();

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('creating the field', function() {
        describe('creating a new email', function() {
            it('should initialize an empty attachments collection', function() {
                field = SugarTest.createField({
                    name: 'attachments',
                    type: 'email-attachments',
                    viewName: 'edit',
                    module: 'Emails',
                    model: model,
                    context: context
                });

                expect(model.get('attachments').length).toBe(0);
            });
        });

        describe('opening an existing email', function() {
            var data;

            beforeEach(function() {
                data = {
                    next_offset: -1,
                    records: [{
                        id: _.uniqueId(),
                        upload_id: '',
                        name: 'Disclosure Agreement.pdf',
                        filename: 'Disclosure Agreement.pdf',
                        file_mime_type: 'application/pdf',
                        file_size: 158589,
                        file_source: '',
                        file_ext: 'pdf'
                    }, {
                        id: _.uniqueId(),
                        upload_id: _.uniqueId(),
                        name: 'logo.jpg',
                        filename: 'logo.jpg',
                        file_mime_type: 'image/jpg',
                        file_size: 158589,
                        file_source: 'DocumentRevisions',
                        file_ext: 'jpg'
                    }]
                };

                model.set('id', _.uniqueId());

                sandbox.spy(model, 'once').withArgs('sync');
            });

            using('view modes', ['detail', 'edit'], function(mode) {
                it('should fetch all attachments when the field is created', function() {
                    var attachments;

                    // The data is loaded before the field is created.
                    model.set('attachments', data);
                    attachments = model.get('attachments');

                    sandbox.stub(attachments, 'fetch', function(options) {
                        expect(options.all).toBe(true);
                    });

                    field = SugarTest.createField({
                        name: 'attachments',
                        type: 'email-attachments',
                        viewName: mode,
                        module: 'Emails',
                        model: model,
                        context: context
                    });

                    // The next_offset is already -1, so there are no more
                    // attachments to fetch. But the function call was made.
                    expect(attachments.length).toBe(2);

                    // Listening to the `sync` event once was not needed.
                    expect(model.once).not.toHaveBeenCalled();
                });

                it('should fetch all attachments when the data is loaded', function() {
                    var attachments;

                    field = SugarTest.createField({
                        name: 'attachments',
                        type: 'email-attachments',
                        viewName: mode,
                        module: 'Emails',
                        model: model,
                        context: context
                    });

                    // The data is loaded after the field is created.
                    model.set('attachments', data);
                    attachments = model.get('attachments');

                    sandbox.stub(attachments, 'fetch', function(options) {
                        expect(options.all).toBe(true);
                    });

                    model.trigger('sync', model, data, {});

                    // The next_offset is already -1, so there are no more
                    // attachments to fetch. But the function call was made.
                    expect(attachments.length).toBe(2);

                    // Listening to the `sync` event once was needed.
                    expect(model.once).toHaveBeenCalled();
                });
            });
        });
    });

    describe('editing a draft', function() {
        var attachments;

        beforeEach(function() {
            var data = {
                next_offset: -1,
                records: [{
                    id: _.uniqueId(),
                    name: 'Disclosure Agreement.pdf',
                    filename: 'Disclosure Agreement.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf'
                }, {
                    id: _.uniqueId(),
                    upload_id: _.uniqueId(),
                    name: 'logo.jpg',
                    filename: 'logo.jpg',
                    file_mime_type: 'image/jpg',
                    file_size: 158589,
                    file_source: 'DocumentRevisions',
                    file_ext: 'jpg'
                }]
            };

            model.set('id', _.uniqueId());
            model.set('attachments', data);
            attachments = model.get('attachments');

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

        it('should format the value', function() {
            var $file = $('<input/>', {value: 'quote.pdf'});
            var urlEndpoint = '/file/filename?force_download=1&' + timestamp + '=1&platform=base';
            var expected;

            // Add some attachments.
            attachments.add([{
                filename_guid: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_ext: 'pdf'
            }, {
                id: _.uniqueId(),
                upload_id: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_source: 'DocumentRevisions',
                file_ext: 'pdf'
            }, {
                id: _.uniqueId(),
                upload_id: _.uniqueId(),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_source: 'DocumentRevisions',
                file_ext: 'pdf'
            }]);

            // Remove a previously linked attachment.
            attachments.remove(attachments.at(1));

            // Remove one of the new attachments, before it is linked.
            attachments.remove(attachments.at(2));

            // Don't allow the success callback to be called for the request.
            sandbox.stub(app.api, 'file');

            sandbox.stub(field, '_getFileInput').returns($file);
            field.$('input[type=file]').change();

            expected = [{
                cid: attachments.at(0).cid,
                id: attachments.at(0).get('id'),
                name: 'Disclosure Agreement.pdf',
                filename: 'Disclosure Agreement.pdf',
                file_mime_type: 'application/pdf',
                file_size: '159K',
                file_ext: 'pdf',
                file_url: app.api.serverUrl + '/Notes/' + attachments.at(0).get('id') + urlEndpoint
            }, {
                cid: attachments.at(1).cid,
                filename_guid: attachments.at(1).get('filename_guid'),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: '159K',
                file_ext: 'pdf',
                file_url: null
            }, {
                cid: attachments.at(2).cid,
                id: attachments.at(2).get('id'),
                upload_id: attachments.at(2).get('upload_id'),
                name: 'quote.pdf',
                filename: 'quote.pdf',
                file_mime_type: 'application/pdf',
                file_size: '159K',
                file_source: 'DocumentRevisions',
                file_ext: 'pdf',
                file_url: app.api.serverUrl + '/Notes/' + attachments.at(2).get('id') + urlEndpoint
            }, {
                cid: field._placeholders.at(0).cid,
                name: field._placeholders.at(0).get('name'),
                file_size: '0K',
                file_url: null
            }];

            expect(field.getFormattedValue()).toEqual(expected);
        });

        it('should not allow the dropdown to open', function() {
            var event = $.Event('select2-opening');
            sandbox.spy(event, 'preventDefault');

            field.$(field.fieldTag).trigger(event);
            expect(event.preventDefault).toHaveBeenCalled();
        });

        describe('uploading an attachment', function() {
            it('should open the file dialog', function() {
                sandbox.stub(field, '_openFilePicker');
                field.view.trigger('email_attachments:file:pick');
                expect(field._openFilePicker).toHaveBeenCalled();
            });

            it('should add an uploaded file', function() {
                var fileName = 'quote.pdf';
                var $file = $('<input/>', {value: fileName});
                var id = _.uniqueId();
                var attachment;
                var json;

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
                            file_ext: 'pdf'
                        }
                    };

                    expect(method).toBe('create');
                    expect(data.id).toBe('temp');
                    expect(data.module).toBe('Notes');
                    expect(data.field).toBe('filename');
                    expect(options.temp).toBe(true);
                    expect(options.iframe).toBe(true);
                    expect(options.deleteIfFails).toBe(true);
                    expect(options.htmlJsonFormat).toBe(true);

                    // The uploaded attachment doesn't yet exist.
                    expect(attachments.length).toBe(2);

                    // A placeholder currently exists in the uploaded
                    // attachment's place.
                    expect(field._placeholders.length).toBe(1);
                    expect(field._placeholders.at(0).get('name')).toBe(fileName);

                    callbacks.success(response);
                    callbacks.complete();
                });

                field.$('input[type=file]').change();

                // The file input field should be cleared.
                expect($file.val()).toEqual('');

                // The placeholder should no longer exist.
                expect(field._placeholders.length).toBe(0);

                // The uploaded attachment should now exist.
                expect(attachments.length).toBe(3);

                attachment = attachments.at(2);
                expect(attachment.get('id')).toBeUndefined();
                expect(attachment.get('filename_guid')).toBe(id);
                expect(attachment.get('name')).toBe(fileName);
                expect(attachment.get('filename')).toBe(fileName);
                expect(attachment.get('file_mime_type')).toBe('application/pdf');
                expect(attachment.get('file_size')).toBe(158589);
                expect(attachment.get('file_ext')).toBe('pdf');
                expect(attachment.get('file_source')).toBeUndefined();

                json = model.toJSON();
                expect(json.attachments.create.length).toBe(1);
                expect(json.attachments.create).toEqual([{
                    filename_guid: id,
                    name: fileName,
                    filename: fileName,
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf'
                }]);
                expect(json.attachments.add).toBeUndefined();
                expect(json.attachments.delete).toBeUndefined();
            });

            it('should alert the user when the uploaded file is too large', function() {
                var fileName = 'quote.pdf';
                var $file = $('<input/>', {value: fileName});
                var error = {error: 'request_too_large'};
                var json;

                sandbox.spy(app.alert, 'show');
                sandbox.spy(app.api, 'defaultErrorHandler');
                sandbox.spy(app.lang, 'get');
                sandbox.stub(field, '_getFileInput').returns($file);
                sandbox.stub(app.api, 'file', function(method, data, $files, callbacks, options) {
                    expect(method).toBe('create');
                    expect(data.id).toBe('temp');
                    expect(data.module).toBe('Notes');
                    expect(data.field).toBe('filename');
                    expect(options.temp).toBe(true);
                    expect(options.iframe).toBe(true);
                    expect(options.deleteIfFails).toBe(true);
                    expect(options.htmlJsonFormat).toBe(true);

                    // The uploaded attachment doesn't yet exist.
                    expect(attachments.length).toBe(2);

                    // A placeholder currently exists in the uploaded
                    // attachment's place.
                    expect(field._placeholders.length).toBe(1);
                    expect(field._placeholders.at(0).get('name')).toBe(fileName);

                    callbacks.error(error);
                    callbacks.complete();
                });

                field.$('input[type=file]').change();

                expect(error.handled).toBe(true);
                expect(app.alert.show).toHaveBeenCalled();
                expect(app.lang.get).toHaveBeenCalledWith('ERROR_MAX_FILESIZE_EXCEEDED');
                expect(app.api.defaultErrorHandler).toHaveBeenCalledWith(error);

                // The file input field should be cleared.
                expect($file.val()).toEqual('');

                // The placeholder should no longer exist.
                expect(field._placeholders.length).toBe(0);

                // The uploaded attachment is not added due to the error.
                expect(attachments.length).toBe(2);

                json = model.toJSON();
                expect(json.attachments).toBeUndefined();
            });
        });

        describe('attaching a document', function() {
            it("should add a document's file", function() {
                var selection = {
                    id: _.uniqueId(),
                    name: 'Contract',
                    value: 'Contract'
                };
                var doc;
                var attachment;
                var json;

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
                    // The document attachment doesn't yet exist.
                    expect(attachments.length).toBe(2);

                    // A placeholder currently exists in the document
                    // attachment's place.
                    expect(field._placeholders.length).toBe(1);
                    expect(field._placeholders.at(0).get('name')).toBe('Contract');

                    doc.set({
                        document_revision_id: _.uniqueId(),
                        name: 'Contract.pdf',
                        filename: 'Contract.pdf',
                        latest_revision_file_mime_type: 'application/pdf',
                        latest_revision_file_size: 158589,
                        latest_revision_file_ext: 'pdf'
                    });

                    options.success(doc);
                    options.complete();
                });

                field.view.trigger('email_attachments:document:pick');

                // The placeholder should no longer exist.
                expect(field._placeholders.length).toBe(0);

                // The document attachment should now exist.
                expect(attachments.length).toBe(3);

                attachment = attachments.at(2);
                expect(attachment.get('id')).toBeUndefined();
                expect(attachment.get('upload_id')).toBe(doc.get('document_revision_id'));
                expect(attachment.get('name')).toBe('Contract.pdf');
                expect(attachment.get('filename')).toBe('Contract.pdf');
                expect(attachment.get('file_mime_type')).toBe('application/pdf');
                expect(attachment.get('file_size')).toBe(158589);
                expect(attachment.get('file_ext')).toBe('pdf');
                expect(attachment.get('file_source')).toBe('DocumentRevisions');

                json = model.toJSON();
                expect(json.attachments.create.length).toBe(1);
                expect(json.attachments.create).toEqual([{
                    upload_id: doc.get('document_revision_id'),
                    name: 'Contract.pdf',
                    filename: 'Contract.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf',
                    file_source: 'DocumentRevisions'
                }]);
                expect(json.attachments.add).toBeUndefined();
                expect(json.attachments.delete).toBeUndefined();

                app.drawer = null;
            });
        });

        describe('removing an attachment', function() {
            it('should remove a new attachment', function() {
                var fileName = 'quote.pdf';
                var $file = $('<input/>', {value: fileName});
                var json;

                sandbox.stub(field, '_getFileInput').returns($file);
                sandbox.stub(app.api, 'file', function(method, data, $files, callbacks, options) {
                    var response = {
                        filename: {
                            guid: fileName
                        },
                        record: {
                            id: _.uniqueId(),
                            deleted: false,
                            file_mime_type: 'application/pdf',
                            file_size: 158589,
                            filename: fileName,
                            file_ext: 'pdf'
                        }
                    };

                    callbacks.success(response);
                    callbacks.complete();
                });

                field.$('input[type=file]').change();

                // The uploaded attachment should now exist.
                expect(attachments.length).toBe(3);

                json = model.toJSON();
                expect(json.attachments.create.length).toBe(1);
                expect(json.attachments.create).toEqual([{
                    filename_guid: attachments.at(2).get('filename_guid'),
                    name: fileName,
                    filename: fileName,
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_ext: 'pdf'
                }]);
                expect(json.attachments.add).toBeUndefined();
                expect(json.attachments.delete).toBeUndefined();

                field.$(field.fieldTag).trigger($.Event('select2-removed', {val: attachments.at(2).cid}));

                // The uploaded attachment should no longer exist.
                expect(attachments.length).toBe(2);

                json = model.toJSON();
                expect(json.attachments).toBeUndefined();
            });

            it('should remove an existing attachment', function() {
                var json;
                var id = attachments.at(0).get('id');

                expect(attachments.length).toBe(2);

                json = model.toJSON();
                expect(json.attachments).toBeUndefined();

                field.$(field.fieldTag).trigger($.Event('select2-removed', {val: attachments.at(0).cid}));

                expect(attachments.length).toBe(1);

                json = model.toJSON();
                expect(json.attachments.create).toBeUndefined();
                expect(json.attachments.add).toBeUndefined();
                expect(json.attachments.delete).toEqual([id]);
            });

            it('should remove a placeholder attachment', function() {
                var $file = $('<input/>', {value: 'quote.pdf'});
                var placeholder;

                // Don't allow the success callback to be called for the request.
                sandbox.stub(app.api, 'file');

                sandbox.stub(field, '_getFileInput').returns($file);
                field.$('input[type=file]').change();
                placeholder = field._placeholders.at(0);

                expect(field._placeholders.length).toBe(1);
                expect(placeholder.get('name')).toBe('quote.pdf');

                field.$(field.fieldTag).trigger($.Event('select2-removed', {val: placeholder.cid}));

                expect(field._placeholders.length).toBe(0);
                expect(field._requests[placeholder.cid]).toBeUndefined();
            });
        });
    });

    describe('viewing an archived email', function() {
        var attachments;

        beforeEach(function() {
            var data = {
                next_offset: -1,
                records: [{
                    id: _.uniqueId(),
                    upload_id: '',
                    name: 'Disclosure Agreement.pdf',
                    filename: 'Disclosure Agreement.pdf',
                    file_mime_type: 'application/pdf',
                    file_size: 158589,
                    file_source: '',
                    file_ext: 'pdf'
                }, {
                    id: _.uniqueId(),
                    upload_id: _.uniqueId(),
                    name: 'logo.jpg',
                    filename: 'logo.jpg',
                    file_mime_type: 'image/jpg',
                    file_size: 158589,
                    file_source: 'DocumentRevisions',
                    file_ext: 'jpg'
                }]
            };

            model.set('id', _.uniqueId());
            model.set('attachments', data);
            attachments = model.get('attachments');

            field = SugarTest.createField({
                name: 'attachments',
                type: 'email-attachments',
                viewName: 'detail',
                module: 'Emails',
                model: model,
                context: context
            });
        });

        it('should format the value', function() {
            var urlEndpoint = '/file/filename?force_download=1&' + timestamp + '=1&platform=base';
            var expected = [{
                cid: attachments.at(0).cid,
                id: attachments.at(0).get('id'),
                upload_id: '',
                name: 'Disclosure Agreement.pdf',
                filename: 'Disclosure Agreement.pdf',
                file_mime_type: 'application/pdf',
                file_size: '159K',
                file_source: '',
                file_ext: 'pdf',
                file_url: app.api.serverUrl + '/Notes/' + attachments.at(0).get('id') + urlEndpoint
            }, {
                cid: attachments.at(1).cid,
                id: attachments.at(1).get('id'),
                upload_id: attachments.at(1).get('upload_id'),
                name: 'logo.jpg',
                filename: 'logo.jpg',
                file_mime_type: 'image/jpg',
                file_size: '159K',
                file_source: 'DocumentRevisions',
                file_ext: 'jpg',
                file_url: app.api.serverUrl + '/Notes/' + attachments.at(1).get('id') + urlEndpoint
            }];

            expect(field.getFormattedValue()).toEqual(expected);
        });

        it('should download an attachment', function() {
            var url = app.api.serverUrl +
                '/Notes/' +
                attachments.at(0).get('id') +
                '/file/filename?force_download=1&' +
                timestamp +
                '=1&platform=base';

            sandbox.stub(app.api, 'fileDownload');

            field.render();
            field.$('[data-action=download]').first().click();

            expect(app.api.fileDownload).toHaveBeenCalledWith(url);
        });
    });

    describe('checking if the field is empty', function() {
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

        it('should return true', function() {
            expect(field.isEmpty()).toBe(true);
        });

        it('should return false', function() {
            model.get('attachments').add({
                filename_guid: _.uniqueId(),
                name: 'Disclosure Agreement.pdf',
                filename: 'Disclosure Agreement.pdf',
                file_mime_type: 'application/pdf',
                file_size: 158589,
                file_ext: 'pdf'
            });

            expect(field.isEmpty()).toBe(false);
        });

        it('should return false when there is a placeholder', function() {
            var $file = $('<input/>', {value: 'quote.pdf'});
            var flag;

            // Don't allow the success callback to be called for the request.
            sandbox.stub(field, '_getFileInput').returns($file);
            sandbox.stub(app.api, 'file', function() {
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
                expect(field.isEmpty()).toBe(false);
            });
        });
    });
});
