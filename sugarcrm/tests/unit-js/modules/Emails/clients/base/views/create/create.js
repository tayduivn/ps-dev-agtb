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
describe('Emails.Views.Create', function() {
    var app;
    var view;
    var context;
    var model;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'create');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({
            module: 'Emails',
            create: true
        });
        context.prepare(true);
        model = context.get('model');

        view = SugarTest.createView('base', 'Emails', 'create', null, context, true);

        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        sandbox.restore();

        view.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete app.drawer;

        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('setting the page title', function() {
        it('should set the title for creating an archived email', function() {
            sandbox.stub(app.lang, 'get').returnsArg(0);
            sandbox.stub(view, 'setTitle');

            view.render();

            expect(view.setTitle).toHaveBeenCalledWith('LNK_NEW_ARCHIVE_EMAIL');
        });
    });

    describe('managing attachments', function() {
        describe('hiding or showing the attachments field', function() {
            var field;
            var spyAddClass;
            var spyRemoveClass;

            beforeEach(function() {
                var $el;

                spyAddClass = sandbox.spy();
                spyRemoveClass = sandbox.spy();

                $el = {
                    closest: function() {
                        return {
                            addClass: spyAddClass,
                            removeClass: spyRemoveClass
                        };
                    }
                };

                field = {
                    getFieldElement: function() {
                        return $el;
                    },
                    isEmpty: $.noop
                };

                sandbox.stub(view, 'getField').withArgs('attachments_collection').returns(field);
                sandbox.stub(view, '_resizeEditor');
            });

            describe('rendering the view', function() {
                it('should show the attachments field', function() {
                    // There are attachments.
                    sandbox.stub(field, 'isEmpty').returns(false);

                    view.render();

                    expect(spyAddClass).toHaveBeenCalledWith('single');
                    expect(spyRemoveClass).toHaveBeenCalledWith('hidden');
                    expect(view._resizeEditor).toHaveBeenCalledOnce();
                });

                it('should hide the attachments field', function() {
                    // There are no attachments.
                    sandbox.stub(field, 'isEmpty').returns(true);

                    view.render();

                    expect(spyAddClass).toHaveBeenCalledWith('hidden');
                    expect(spyRemoveClass).toHaveBeenCalledWith('single');
                    expect(view._resizeEditor).toHaveBeenCalledOnce();
                });
            });

            describe('responding to changes to the attachments', function() {
                it('should show the attachments field', function() {
                    // There are attachments.
                    sandbox.stub(field, 'isEmpty').returns(false);

                    model.trigger('change:attachments_collection');

                    expect(spyAddClass).toHaveBeenCalledWith('single');
                    expect(spyRemoveClass).toHaveBeenCalledWith('hidden');
                    expect(view._resizeEditor).toHaveBeenCalledOnce();
                });

                it('should hide the attachments field', function() {
                    // THere are no attachments.
                    sandbox.stub(field, 'isEmpty').returns(true);

                    model.trigger('change:attachments_collection');

                    expect(spyAddClass).toHaveBeenCalledWith('hidden');
                    expect(spyRemoveClass).toHaveBeenCalledWith('single');
                    expect(view._resizeEditor).toHaveBeenCalledOnce();
                });
            });
        });

        describe('alerting the user when the attachments are too large', function() {
            var saveButton;

            beforeEach(function() {
                saveButton = {
                    setDisabled: sandbox.spy()
                };
                sandbox.stub(view, 'getField').withArgs('save_button').returns(saveButton);
            });

            describe('attachments are over the limit', function() {
                it('should disable the save button', function() {
                    model.trigger('attachments_collection:over_max_total_bytes');

                    expect(saveButton.setDisabled).toHaveBeenCalledWith(true);
                });

                it('should alert the user', function() {
                    sandbox.stub(app.alert, 'show');

                    model.trigger('attachments_collection:over_max_total_bytes');

                    expect(app.alert.show).toHaveBeenCalledWith('email-attachment-status');
                });
            });

            describe('attachments are under the limit', function() {
                it('should enable the save button', function() {
                    model.trigger('attachments_collection:under_max_total_bytes');

                    expect(saveButton.setDisabled).toHaveBeenCalledWith(false);
                });

                it('should hide any open alerts', function() {
                    sandbox.stub(app.alert, 'dismiss');

                    model.trigger('attachments_collection:under_max_total_bytes');

                    expect(app.alert.dismiss).toHaveBeenCalledWith('email-attachment-status');
                });
            });
        });
    });

    describe('saving an email', function() {
        it('should build a message stating that the email was archived', function() {
            var actual;

            sandbox.stub(app.lang, 'get').returnsArg(0);

            actual = view.buildSuccessMessage();

            expect(actual).toBe('LBL_EMAIL_ARCHIVED');
            expect(app.lang.get.firstCall.args[1]).toBe(view.module);
        });
    });

    describe('ResizeEditor', function() {
        var $drawer;
        var $editor;

        beforeEach(function() {
            var mockHtml = '<div><div class="drawer">' +
                    '<div class="headerpane"></div>' +
                    '<div class="record"><div class="mce-stack-layout"><div class="mce-stack-layout-item">' +
                    '<iframe frameborder="0"></iframe></div></div></div><div class="show-hide-toggle"></div>' +
                    '</div></div>';
            var drawerHeight = view.MIN_EDITOR_HEIGHT + 300;
            var otherHeight = 50;
            var editorHeight = drawerHeight - (otherHeight * 2) - view.EDITOR_RESIZE_PADDING -
                view.ATTACHMENT_FIELD_HEIGHT;

            view.$el = $(mockHtml);
            $drawer = view.$('.drawer');
            $drawer.height(drawerHeight);
            $editor = view.$('.mce-stack-layout .mce-stack-layout-item iframe');
            $editor.height(editorHeight);

            view.$('.headerpane').height(otherHeight);
            view.$('.record').height(editorHeight);
            view.$('.show-hide-toggle').height(otherHeight);

            app.drawer = {
                getHeight: $.noop
            };
            sandbox.stub(app.drawer, 'getHeight', function() {
                return $drawer.height();
            });
        });

        it('should increase the height of the editor when drawer height increases', function() {
            var editorHeightBefore = $editor.height();
            var drawerHeightBefore = $drawer.height();

            //increase drawer height by 100 pixels
            $drawer.height(drawerHeightBefore + 100);

            view._resizeEditor();
            //editor should be increased to fill the space
            expect($editor.height()).toEqual(editorHeightBefore + 100);
        });

        it('should decrease the height of the editor when drawer height decreases', function() {
            var editorHeightBefore = $editor.height();
            var drawerHeightBefore = $drawer.height();

            //decrease drawer height by 100 pixels
            $drawer.height(drawerHeightBefore - 100);

            view._resizeEditor();
            //editor should be decreased to account for decreased drawer height
            expect($editor.height()).toEqual(editorHeightBefore - 100);
        });

        it('should ensure that editor maintains minimum height when drawer shrinks beyond that', function() {
            //decrease drawer height to 50 pixels below min editor height
            $drawer.height(view.MIN_EDITOR_HEIGHT - 50);

            view._resizeEditor();
            //editor should maintain min height
            expect($editor.height()).toEqual(view.MIN_EDITOR_HEIGHT);
        });

        it('should resize editor to fill empty drawer space but with a padding to prevent scrolling', function() {
            var editorHeightBefore = $editor.height();
            var editorHeightPlusPadding = editorHeightBefore + view.EDITOR_RESIZE_PADDING;

            //add the resize padding on
            $editor.height(editorHeightPlusPadding);
            view.$('.record').height(editorHeightPlusPadding);

            //padding should be added back
            view._resizeEditor();
            expect($editor.height()).toEqual(editorHeightBefore);
        });

        describe('events that resize the editor', function() {
            beforeEach(function() {
                sandbox.stub(view, '_resizeEditor');
            });

            it('should resize the editor when tinymce is initialized', function() {
                context.trigger('tinymce:oninit');

                expect(view._resizeEditor).toHaveBeenCalledOnce();
            });

            it('should resize the editor when toggling to show/hide hidden panel', function() {
                view.trigger('more-less:toggled');

                expect(view._resizeEditor).toHaveBeenCalledOnce();
            });

            it('should resize the editor when the visibility of any recipients fields is toggled', function() {
                view.trigger('email-recipients:toggled');

                expect(view._resizeEditor).toHaveBeenCalledOnce();
            });
        });
    });
});
