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
    var app = null;

    beforeEach(function() {
        app = SugarTest.app;

        window.singleSpa = {
            start: sinon.collection.stub(),
            mountRootParcel: sinon.collection.stub()
        };

        var meta = {};

        var options = {
            meta: {
                srn: 'some-srn',
                env: {
                    testKey: 'test val'
                },
            },
            layout: {
                cid: 'w92'
            }
        };

        context = app.context.getContext();
        layout = SugarTest.createLayout('base', 'Accounts', 'tabbed-layout', meta);
        view = SugarTest.createView('base', 'Accounts', 'external-app', {config: true}, context, false, layout);
        view.initialize(options);
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
    });

    describe('Initialize', function() {
        it('should check if singleSpa Start is called', function() {
            expect(window.singleSpa.start).toHaveBeenCalled();
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
