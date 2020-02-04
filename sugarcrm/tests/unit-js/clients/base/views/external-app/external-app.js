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

describe('Base.View.ExternalApp', function() {
    var view;
    var options;
    var app = null;
    var context = null;
    var layout;

    beforeEach(function() {
        var meta = {};

        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());

        window.singleSpa = {
            start: sinon.collection.stub(),
            mountRootParcel: sinon.collection.stub()
        };

        options = {
            context: context,
            meta: {
                srn: 'some-srn',
                env: {
                    testKey: 'test val'
                }
            },
            layout: {
                cid: 'w92'
            }
        };

        layout = SugarTest.createLayout('base', 'Accounts', 'tabbed-layout', meta);
        view = SugarTest.createView('base', 'Accounts', 'external-app', options.meta, options.context, false, layout);
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
    });

    describe('initialize', function() {
        it('should check if singleSpa Start is called', function() {
            view.initialize(options);
            expect(window.singleSpa.start).toHaveBeenCalled();
        });

        it('should set allowApp to be true if it starts undefined', function() {
            view.allowApp = undefined;
            view.initialize(options);

            expect(view.allowApp).toBeTruthy();
        });

        it('should set allowApp to be false if it starts false', function() {
            view.allowApp = false;
            view.initialize(options);

            expect(view.allowApp).toBeFalsy();
        });

        it('should set extraParcelParams is meta.env is set', function() {
            view.initialize(options);

            expect(view.extraParcelParams).toEqual({
                testKey: 'test val'
            });
        });

        it('should call _onSugarAppLoad if not in a tabbed-layout', function() {
            options.layout.type = 'dashboard';
            sinon.collection.stub(view, '_onSugarAppLoad', function() {});
            view.initialize(options);

            expect(view._onSugarAppLoad).toHaveBeenCalled();
        });

        it('should call _onSugarAppLoad if in a tabbed-layout', function() {
            options.layout.type = 'tabbed-layout';
            sinon.collection.stub(view, '_onSugarAppLoad', function() {});
            sinon.collection.stub(view.context, 'on', function() {});
            view.initialize(options);

            expect(view._onSugarAppLoad).not.toHaveBeenCalled();
            expect(view.context.on).toHaveBeenCalledWith('sugarApp:load:w92:some-srn');
        });
    });

    describe('render', function() {
        beforeEach(function() {
            sinon.collection.stub(view, '_mountApp');
            view.render();
        });

        it('should set rendered to true', function() {
            expect(view.rendered).toBeTruthy();
        });

        it('should call _mountApp', function() {
            expect(view._mountApp).toHaveBeenCalled();
        });
    });

    describe('displayError', function() {
        beforeEach(function() {
            sinon.collection.stub(app.lang, 'get', function() {});
            sinon.collection.stub(view.$el, 'empty', function() {});
            sinon.collection.stub(view, 'template', function() {});
            sinon.collection.stub(view.$el, 'append', function() {});

            view.errorCode = 'test1';
            view.displayError();
        });

        it('should call app.lang.get with the errorCode', function() {
            expect(app.lang.get).toHaveBeenCalledWith('LBL_SUGAR_APPS_DASHLET_CATALOG_ERROR', null, {
                errorCode: 'test1'
            });
        });

        it('should empty the $el', function() {
            expect(view.$el.empty).toHaveBeenCalled();
        });

        it('should call the template to add to the $el', function() {
            expect(view.template).toHaveBeenCalledWith(view);
        });

        it('should add the template to the $el', function() {
            expect(view.$el.append).toHaveBeenCalled();
        });
    });

    describe('_mountApp', function() {
        describe('when app is not mounted', function() {
            beforeEach(function() {
                sinon.collection.stub(view.el, 'appendChild');

                view.mounted = false;
                view.parcelApp = true;

                view._mountApp();
            });

            it('should call view.appendChild', function() {
                expect(view.el.appendChild).toHaveBeenCalled();
            });

            it('should call singleSpa.mountRootParcel', function() {
                expect(window.singleSpa.mountRootParcel).toHaveBeenCalled();
            });
        });

        describe('when app is mounted', function() {
            beforeEach(function() {
                view.mounted = true;
                view.parcel = {
                    update: sinon.collection.stub()
                };

                view._mountApp();
            });

            it('should call view.parcel.update', function() {
                expect(view.parcel.update).toHaveBeenCalled();
            });
        });
    });

    describe('_dispose', function() {
        beforeEach(function() {
            view.parcel = {
                unmount: sinon.collection.stub()
            };

            sinon.collection.stub(view, '_super');
            view._dispose();
        });

        it('should call view.parcel.unmount', function() {
            expect(view.parcel.unmount).toHaveBeenCalled();
        });
    });
});
