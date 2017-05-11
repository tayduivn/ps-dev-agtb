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
describe('Base.View.Bwc', function() {
    var view;
    var app;
    var iframeStub;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;

        var module = 'Documents';
        //view's initialize checks context's url so we add a "sidecar url" here
        var url = 'http://localhost:8888/master/ent/sugarcrm/';
        var context = app.context.getContext();
        context.set({ url: url, module: module});
        context.prepare();
        view = SugarTest.createView('base', module, 'bwc', null, context);

        sandbox = sinon.sandbox.create();

        iframeStub = sandbox.stub(view, '$');
        iframeStub.withArgs('iframe').returns({
            get: function() {
                return {
                    contentWindow: {
                    }
                };
            }
        });
    });

    afterEach(function() {
        view.dispose();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        sandbox.restore();
    });

    describe('Warning unsaved changes', function() {
        var navigateStub;

        beforeEach(function() {
            navigateStub = sandbox.stub(app.router, 'navigate');
            sandbox.stub(app.alert, 'show');
            sandbox.stub(Backbone.history, 'getFragment');
        });

        it('serialize form elements', function() {
            var form = $('<form>' +
                '<input name="name" value="test">' +
                '<input name="phone_number" value="121-1213-456">' +
                '<input type="checkbox" name="check1" value="c">' +
                '<input type="checkbox" name="check1" value="d" checked>' +
                '<input type="radio" name="radio1" value="1">' +
                '<input type="radio" name="radio1" value="0" checked>' +
                '<select name="select1">' +
                '<option value="blah1">Boo1</option>' +
                '<option value="blah2" selected>Boo2</option>' +
                '</select>' +
                '<textarea name="text1">raw data set</textarea>' +
                '</form>').get(0);
            var actual = view.serializeObject(form);
            expect(actual.name).toBe('test');
            expect(actual.phone_number).toBe('121-1213-456');
            expect(actual.radio1).toBe('0');
            expect(actual.select1).toBe('blah2');
            expect(actual.check1).toBe('d');
            expect(actual.text1).toBe('raw data set');

            //Assign new value changing by jQuery
            $(form).find('[name=name]').val('new test value');
            $(form).find('[name=select1]').val('blah1');

            //Assign new value changing by JS
            form.phone_number.value = '999-888-1200';
            var actual2 = view.serializeObject(form);
            expect(actual2.name).toBe('new test value');
            expect(actual2.phone_number).toBe('999-888-1200');
            expect(actual2.radio1).toBe(actual.radio1);
            expect(actual2.select1).toBe('blah1');
            expect(actual2.check1).toBe(actual.check1);
            expect(actual2.text1).toBe(actual.text1);
        });

        it('should ignore unsavedchange logic when current view does not contain form data', function() {
            var emptyForm = $('<div>' +
                '<a href="javascript:void(0);"></a>' +
                '<h1>Title foo</h1>' +
                '</div>').get(0);

            iframeStub.withArgs('iframe').returns({
                get: function() {
                    return {
                        contentWindow: {
                            EditView: emptyForm
                        }
                    };
                }
            });

            var bwcWindow = view.$('iframe').get(0).contentWindow,
                attributes = view.serializeObject(bwcWindow.EditView);
            view.resetBwcModel(attributes);
            expect(_.isEmpty(view.bwcModel.attributes)).toBe(true);
            expect(view.hasUnsavedChanges()).toBe(false);
        });

        it('warn unsaved changes on bwc iframe', function() {
            var form = $('<form>' +
                '<input name="name" value="test">' +
                '<input name="phone_number" value="121-1213-456">' +
                '</form>').get(0);
            view.resetBwcModel({module: 'Document'});

            iframeStub.withArgs('iframe').returns({
                get: function() {
                    return {
                        contentWindow: {
                            EditView: form
                        }
                    };
                }
            });

            expect(view.hasUnsavedChanges()).toBe(true);
            var bwcWindow = view.$('iframe').get(0).contentWindow,
                attributes = view.serializeObject(bwcWindow.EditView);
            //reset to the current changed form
            view.resetBwcModel(attributes);
            expect(view.hasUnsavedChanges()).toBe(false);
            //change the value once again
            form.phone_number.value = '408-888-8888';
            expect(view.hasUnsavedChanges()).toBe(true);
        });

        // TODO: Remove this when we get rid of bwc functionality
        it('should redirect to sidecar Home if user tries to directly access bwc Home/Dashboard', function() {
            var oldHomeUrl = 'http://localhost:8888/master/ent/sugarcrm/#bwc/index.php?module=Home&action=index';
            var context = app.context.getContext();
            context.set({ url: oldHomeUrl, module: 'Documents'});
            context.prepare();
            view.initialize({context: context});
            expect(navigateStub).toHaveBeenCalled();
        });

        it('convertToSidecarUrl should put BWC module URLs through bwc/ route', function() {
            var href = 'index.php?module=Documents&offset=1&stamp=1&return_module=Documents&action=DetailView&record=1';
            sandbox.stub(app.metadata, 'getModule', function() {
                return {isBwcEnabled: true};
            });
            var result = view.convertToSidecarUrl(href);
            expect(result).toEqual("bwc/index.php?module=Documents&offset=1&stamp=1&return_module=Documents&action=DetailView&record=1")
        });

        it('convertToSidecarLink should leave javascript URLs alone', function() {
            var ele = document.createElement("A");
            var href = 'javascript:void alert("Hi!");';
            ele.setAttribute("href", href);
            view.convertToSidecarLink(ele);
            expect(ele.getAttribute("href")).toEqual(href);
        });

        it('convertToSidecarLink should leave Administration module URLs alone', function() {
            var href = 'index.php?module=Administration&action=Languages&view=default';
            sandbox.stub(app.metadata, 'getModule', function() {
                return {isBwcEnabled: true};
            });
            var ele = document.createElement("A");
            ele.setAttribute("href", href);
            view.convertToSidecarLink(ele);
            expect(ele.getAttribute("href")).toEqual(href);
        });

        it('should NOT check for Home module if no url', function() {
            var context = app.context.getContext();
            context.set({ url: undefined, module: 'Documents'});
            context.prepare();
            view.initialize({context: context});
            expect(navigateStub).not.toHaveBeenCalled();
        });
        it('should get the current module off the page if not in location.search', function() {
            var contentWindowMock = {
                location: {
                    search: null
                },
                $: function () {
                    return {
                        val: function () {
                            return 'testModuleName';
                        }
                    }
                }
            };
            // Mock it to pretend to be in an iframe
            window.parent.SUGAR = {App: SugarTest.app};

            var module = view._setModule(contentWindowMock);

            expect(SugarTest.app.controller.context.get('module')).toEqual('testModuleName');

            delete window.parent.SUGAR;
        });
    });

    describe('creating emails from a bwc module', function() {
        beforeEach(function() {
            view.model.set({
                id: _.uniqueId(),
                name: 'Foo Bar'
            });

            sandbox.stub(app.utils, 'openEmailCreateDrawer');
        });

        describe('sending an email', function() {
            it('should open the compose email drawer', function() {
                var composePackage = {
                    attachments: {
                        123: {
                            id: 123,
                            filename: 'foobar.jpg'
                        },
                        456: {
                            id: 456,
                            filename: 'bizbaz.pdf'
                        }
                    },
                    body: 'blah blah blah',
                    email_id: '',
                    parent_id: view.model.get('id'),
                    parent_name: view.model.get('name'),
                    parent_type: view.model.module,
                    subject: 'check this out!',
                    to_email_addrs: ''
                };

                view.openComposeEmailDrawer(composePackage);

                expect(app.utils.openEmailCreateDrawer).toHaveBeenCalledOnce();
                expect(app.utils.openEmailCreateDrawer.args[0][0]).toBe('compose-email');
                expect(app.utils.openEmailCreateDrawer.args[0][1].name).toBe(composePackage.subject);
                expect(app.utils.openEmailCreateDrawer.args[0][1].description_html).toBe(composePackage.body);
                expect(app.utils.openEmailCreateDrawer.args[0][1].related).toBe(view.model);
                expect(app.utils.openEmailCreateDrawer.args[0][1].attachments.length).toBe(2);
                expect(app.utils.openEmailCreateDrawer.args[0][1].attachments[0].toJSON()).toEqual({
                    upload_id: 123,
                    name: 'foobar.jpg',
                    filename: 'foobar.jpg'
                });
                expect(app.utils.openEmailCreateDrawer.args[0][1].attachments[1].toJSON()).toEqual({
                    upload_id: 456,
                    name: 'bizbaz.pdf',
                    filename: 'bizbaz.pdf'
                });
            });

            using(
                'to_email_addrs',
                [
                    [
                        'Billy Bob <bb@foo.com>',
                        'bb@foo.com'
                    ],
                    [
                        '<bb@foo.com>',
                        'bb@foo.com'
                    ],
                    [
                        'Billy Bob <bb@foo.com>, Cathy Cobb <cc@foo.com>, Susie Q <sq@foo.com>',
                        'bb@foo.com|cc@foo.com|sq@foo.com'
                    ],
                    [
                        '<bb@foo.com>, <cc@foo.com>',
                        'bb@foo.com|cc@foo.com'
                    ],
                    [
                        'bb@foo.com, cc@foo.com',
                        'bb@foo.com|cc@foo.com'
                    ]
                ],
                function(toEmailAddrs, expected) {
                    it('should pass email addresses to the email compose drawer', function() {
                        var actual;
                        var composePackage = {
                            to_email_addrs: toEmailAddrs
                        };

                        view.openComposeEmailDrawer(composePackage);

                        actual = _.map(app.utils.openEmailCreateDrawer.args[0][1].to, function(recipient) {
                            return recipient.get('email_address');
                        }).join('|');
                        expect(actual).toBe(expected);
                    });
                }
            );
        });

        describe('creating an archived email', function() {
            it('should open the archive email drawer', function() {
                view.openArchiveEmailDrawer();

                expect(app.utils.openEmailCreateDrawer).toHaveBeenCalledOnce();
                expect(app.utils.openEmailCreateDrawer.args[0][0]).toBe('create');
                expect(app.utils.openEmailCreateDrawer.args[0][1].related).toBe(view.model);
            });
        });
    });
});
