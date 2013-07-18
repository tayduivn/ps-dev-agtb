describe("Activity Stream View", function() {
    var view, viewName = 'activitystream',
        createRelatedCollectionStub,
        processAvatarsStub;

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
    });

    afterEach(function() {
        createRelatedCollectionStub.restore();
        processAvatarsStub.restore();
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

    describe("formatAllTags()", function() {
        it('Should format text-based tags in activity post into HTML format', function() {
            view.model.set('data', {
                value: 'foo @[Accounts:1234-1234:foo bar] bar'
            });

            view.formatAllTags();

            expect(view.model.get('data').value).toBe('foo <span class="label label-Accounts sugar_tag"><a href="#Accounts/1234-1234">foo bar</a></span> bar')
        });

        it('Should format text-based tags in comments into HTML format', function() {
            view.commentsCollection.add({
                data: {
                    value: 'foo @[Accounts:1234-1234:foo bar] bar'
                }
            });

            view.formatAllTags();

            expect(view.commentsCollection.at(0).get('data').value).toBe('foo <span class="label label-Accounts sugar_tag"><a href="#Accounts/1234-1234">foo bar</a></span> bar')
        });
    });
});
