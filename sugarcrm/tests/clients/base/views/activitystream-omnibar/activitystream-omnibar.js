describe("Activity Stream Omnibar View", function() {
    var view;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('activitystream-omnibar', 'view', 'base');
        SugarTest.testMetadata.set();

        view = SugarTest.createView('base', 'Cases', 'activitystream-omnibar');
        view.render();
    });

    afterEach(function() {
        view.dispose();
        SugarTest.testMetadata.dispose();
    });

    describe("toggleSubmitButton()", function() {
        var attachments = {};

        beforeEach(function() {
            view.getAttachments = function() {
                return attachments;
            }
        });

        afterEach(function() {
            view.getAttachments = null;
        });

        it('Should disable Submit button by default', function() {
            expect(view.$('.addPost').hasClass('disabled')).toBe(true);
        });

        it('Should enable Submit button when there is text inside the input area', function() {
            view.$('.sayit').text('foo bar');
            view.toggleSubmitButton();
            expect(view.$('.addPost').hasClass('disabled')).toBe(false);
        });

        it('Should disable Submit button when there are only spaces inside the input area', function() {
            view.$('.sayit').text('       ');
            view.toggleSubmitButton();
            expect(view.$('.addPost').hasClass('disabled')).toBe(true);
        });

        it('Should enable Submit button when an attachment is added', function() {
            view.toggleSubmitButton();

            attachments = {one:1};
            view.trigger('attachments:add');

            expect(view.$('.addPost').hasClass('disabled')).toBe(false);
            attachments = {};
        });

        it('Should disable Submit button when an existing attachment is removed', function() {
            attachments = {one:1};
            view.toggleSubmitButton();

            attachments = {};
            view.trigger('attachments:remove');

            expect(view.$('.addPost').hasClass('disabled')).toBe(true);
        });
    });
});
