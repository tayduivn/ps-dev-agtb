describe("Activity Stream View", function() {
    var view, viewName = 'activitystream',
        createRelatedCollectionStub,
        processAvatarsStub,
        getPreviewDataStub;

    beforeEach(function() {
        createRelatedCollectionStub = sinon.stub(SugarTest.app.data, 'createRelatedCollection', function() {
            return new Backbone.Collection();
        });

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base', 'videoEmbed');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();

        var context = SugarTest.app.context.getContext();
        context.set({
            module: 'Cases'
        });
        context.prepare();
        context.get('model').set({
            id: "edf88cef-1be4-9bcc-4cbc-51caf35c5bb1",
            activity_type: "post",
            data: {
                embed: {
                    type: "video",
                    html: "<iframe width='200px' height='100px'></iframe>"
                }
            }
        });

        view = SugarTest.createView('base', 'Cases', viewName, null, context);

        processAvatarsStub = sinon.stub(view, 'processAvatars');
        getPreviewDataStub = sinon.stub(view, 'getPreviewData');
    });

    afterEach(function() {
        createRelatedCollectionStub.restore();
        processAvatarsStub.restore();
        getPreviewDataStub.restore();
        view.dispose();
        SugarTest.testMetadata.dispose();
    });

    describe("processEmbed()", function() {
        it('Should load videoEmbed template when the type is video', function() {
            var appTemplateGetStub = sinon.stub(SugarTest.app.template, 'get');

            view.model.set('data', {
                embed: {
                    type: 'video'
                }
            });
            view.processEmbed();

            expect(appTemplateGetStub.calledWith('activitystream.videoEmbed')).toBe(true);
            appTemplateGetStub.restore();
        });

        it('Should load videoMovieEmbed template when the type is video.movie', function() {
            var appTemplateGetStub = sinon.stub(SugarTest.app.template, 'get');

            view.model.set('data', {
                embed: {
                    type: 'video.movie'
                }
            });
            view.processEmbed();

            expect(appTemplateGetStub.calledWith('activitystream.videoMovieEmbed')).toBe(true);
            appTemplateGetStub.restore();
        });
    });

    describe("resizeVideo()", function() {
        it('Should resize to fit the activity stream container width', function() {
            var widthStub = sinon.stub($.fn, 'width', function() {
                return 300;
            });

            view.render();

            expect(view.$('.embed iframe').prop('width')).toBe('300');
            widthStub.restore();
        });

        it('Should resize with 480px width when the activity stream container width is more than 480px', function() {
            var widthStub = sinon.stub($.fn, 'width', function() {
                return 481;
            });

            view.render();

            expect(view.$('.embed iframe').prop('width')).toBe('480');
            widthStub.restore();
        });

        it('Should resize height so that it keeps its proportion', function() {
            var widthStub = sinon.stub($.fn, 'width', function() {
                return 400;
            });

            view.render();

            expect(view.$('.embed iframe').prop('height')).toBe('200');
            widthStub.restore();
        });
    });

    describe("getPreviewData", function(){
        var previewData,
            aclStub,
            getParentModelStub;

        beforeEach(function() {
            getPreviewDataStub.restore();
        });

        it('Should load preview enabled with default message', function() {
            var previewId = "3be4-2be4-49bcc-dcbc-cccaf35c5bb1";

            view.model.set({
                parent_id: "5asdfgg-2be4-49bcc-dcbc-cccaf35c5bb1",
                parent_type: "Contacts"
            });

            getParentModelStub = sinon.stub(view, '_getParentModel').returns({id: previewId});

            previewData = view.getPreviewData();

            expect(previewData.enabled).toBeTruthy();
            expect(previewData.label).toBe('LBL_PREVIEW');

            getParentModelStub.restore();
        });

        it('Should return preview disabled with correct message when no user has no read access to related record', function() {
            aclStub = sinon.stub(SugarTest.app.acl,'hasAccess', function(){
                return false;
            });

            view.model.set({
                parent_id: "5asdfgg-2be4-49bcc-dcbc-cccaf35c5bb1",
                parent_type: "Contacts"
            });

            previewData = view.getPreviewData();

            expect(previewData.enabled).toBeFalsy();
            expect(previewData.label).toBe('LBL_PREVIEW_DISABLED_NO_ACCESS');

            aclStub.restore();
        });

        it('Should return preview disabled with correct message when related record is same as context model', function() {
            aclStub = sinon.stub(SugarTest.app.acl,'hasAccess', function(){
                return true;
            });

            var moduleId = "3be4-2be4-49bcc-dcbc-cccaf35c5bb1";

            view.model.set({
                parent_id: moduleId,
                parent_type: "Contacts"
            });

            getParentModelStub = sinon.stub(view, '_getParentModel').returns({id: moduleId});

            previewData = view.getPreviewData();

            expect(previewData.enabled).toBeFalsy();
            expect(previewData.label).toBe('LBL_PREVIEW_DISABLED_SAME_RECORD');

            aclStub.restore();
            getParentModelStub.restore();
        });

        it('Should return preview disabled with correct message when no related record', function() {
            previewData = view.getPreviewData();

            expect(previewData.enabled).toBeFalsy();
            expect(previewData.label).toBe('LBL_PREVIEW_DISABLED_NO_RECORD');
        });

        it('Should return preview disabled with correct message when attachment', function() {
            view.model.set({
                activity_type: "attach"
            });

            view.model.unset("data");

            previewData = view.getPreviewData();

            expect(previewData.enabled).toBeFalsy();
            expect(previewData.label).toBe('LBL_PREVIEW_DISABLED_ATTACHMENT');
        });
    });

    describe("formatAllTagsAndLinks()", function() {
        it('Should format text-based tags in activity post into HTML format', function() {
            view.model.set('data', {
                value: 'foo @[Accounts:1234-1234:foo bar] bar'
            });

            view.formatAllTagsAndLinks();

            expect(view.model.get('data').value).toBe('foo <span class="label label-Accounts sugar_tag"><a href="#Accounts/1234-1234">foo bar</a></span> bar')
        });

        it('Should format text-based tags in comments into HTML format', function() {
            view.commentsCollection.add({
                data: {
                    value: 'foo @[Accounts:1234-1234:foo bar] bar'
                }
            });

            view.formatAllTagsAndLinks();

            expect(view.commentsCollection.at(0).get('data').value).toBe('foo <span class="label label-Accounts sugar_tag"><a href="#Accounts/1234-1234">foo bar</a></span> bar')
        });

        it('Should convert URLs into links in posts', function() {
            view.model.set('data', {
                value: 'test.com'
            });

            view.formatAllTagsAndLinks();

            expect(view.model.get('data').value).toBe('<a href="http://test.com" target="_blank">test.com</a>');
        });

        it('Should convert URLs into links in comments', function() {
            view.commentsCollection.add({
                data: {
                    value: 'test.com'
                }
            });

            view.formatAllTagsAndLinks();

            expect(view.commentsCollection.at(0).get('data').value).toBe('<a href="http://test.com" target="_blank">test.com</a>');
        });

        it('Should convert URLs into links even if it is called twice', function() {
            view.model.set('data', {
                value: 'test.com'
            });

            view.formatAllTagsAndLinks();
            view.formatAllTagsAndLinks();

            expect(view.model.get('data').value).toBe('<a href="http://test.com" target="_blank">test.com</a>');
        });
    });

    describe("formatLinks()", function() {
        it('Should convert URLs with http and https protocols into links', function() {
            var input = 'foo http://www.sugarcrm.com bar https://www.test.com',
                expected = 'foo <a href="http://www.sugarcrm.com" target="_blank">http://www.sugarcrm.com</a> bar <a href="https://www.test.com" target="_blank">https://www.test.com</a>',
                result = view.formatLinks(input);

            expect(result).toBe(expected);
        });

        it('Should convert URLs into links when no protocols or "www" is specified', function() {
            var input = 'foo www.sugarcrm.com bar test.com',
                expected = 'foo <a href="http://www.sugarcrm.com" target="_blank">www.sugarcrm.com</a> bar <a href="http://test.com" target="_blank">test.com</a>',
                result = view.formatLinks(input);

            expect(result).toBe(expected);
        });

        it('Should convert URL with parameters into links', function() {
            var input = 'http://www.sugarcrm.com/1234/321?q=difa%20fdaf',
                expected = '<a href="http://www.sugarcrm.com/1234/321?q=difa%20fdaf" target="_blank">http://www.sugarcrm.com/1234/321?q=difa%20fdaf</a>',
                result = view.formatLinks(input);

            expect(result).toBe(expected);
        });

        it('Should convert URL with port number into links', function() {
            var input = 'http://www.sugarcrm.com:8888/ent/sugarcrm/index.php',
                expected = '<a href="http://www.sugarcrm.com:8888/ent/sugarcrm/index.php" target="_blank">http://www.sugarcrm.com:8888/ent/sugarcrm/index.php</a>',
                result = view.formatLinks(input);

            expect(result).toBe(expected);
        });

        it('Should not convert tags into links', function() {
            var input = '@[Contacts:1234-1234:foo bar]',
                expected = '@[Contacts:1234-1234:foo bar]',
                result = view.formatLinks(input);

            expect(result).toBe(expected);
        });
    });
});
