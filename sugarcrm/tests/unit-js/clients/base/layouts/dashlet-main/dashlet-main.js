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

describe('Base.Layout.DashletMain', function() {
    var app;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Home', 'dashlet-main');
    });

    afterEach(function() {
        sinon.collection.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        layout.dispose();
        layout.context = null;
        layout = null;
    });

    describe('setMetadata', function() {
        var initComponentsStub;
        var loadDataStub;
        var renderStub;
        var triggerStub;

        beforeEach(function() {
            initComponentsStub = sinon.collection.stub(layout, 'initComponents');
            loadDataStub = sinon.collection.stub(layout, 'loadData');
            renderStub = sinon.collection.stub(layout, 'render');
            triggerStub = sinon.collection.stub(layout.context, 'trigger');
        });

        it('should load data and render once it gets metadata', function() {
            layout.model.set('metadata', {i: 'am fake metadata'}, {silent: true});

            layout.setMetadata();

            expect(loadDataStub).toHaveBeenCalled();
            expect(renderStub).toHaveBeenCalled();
        });

        it('should not do anything if there is no metadata', function() {
            layout.model.unset('metadata', {silent: true});

            layout.setMetadata();

            expect(loadDataStub).not.toHaveBeenCalled();
            expect(renderStub).not.toHaveBeenCalled();
        });

        it('should initialize one dashlet-row per component', function() {
            var comps1 = {rows: ['row 1', 'row 2'], width: 5};
            var comps2 = {rows: ['row 3', 'row 4'], width: 8};
            layout.model.set('metadata', {components: [comps1, comps2]}, {silent: true});

            layout.setMetadata();

            var expected1 = [
                {
                    layout: {
                        type: 'dashlet-row',
                        width: 5,
                        components: ['row 1', 'row 2'],
                        index: '0',
                    },
                }
            ];
            var expected2 = [
                {
                    layout: {
                        type: 'dashlet-row',
                        width: 8,
                        components: ['row 3', 'row 4'],
                        index: '1',
                    },
                }
            ];
            expect(initComponentsStub).toHaveBeenCalledTwice();
            expect(initComponentsStub).toHaveBeenCalledWith(expected1);
            expect(initComponentsStub).toHaveBeenCalledWith(expected2);
            expect(triggerStub).not.toHaveBeenCalled();
        });

        it('should set up tabs, including the active tab', function() {
            var tab0 = {name: 'tab0', components: [{rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}]};
            var tab1 = {name: 'tab1', components: [{rows: ['row 1, tab 1', 'row 2, tab 1'], width: 14}]};
            var metadata = {tabs: [tab0, tab1]};
            layout.model.set('metadata', metadata, {silent: true});

            layout.setMetadata({tabIndex: 1});

            expect(triggerStub).toHaveBeenCalledWith(
                'tabbed-dashboard:update',
                {
                    activeTab: 1,
                    tabs: [
                        {name: 'tab0', components: [{rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}]},
                        {name: 'tab1', components: [{rows: ['row 1, tab 1', 'row 2, tab 1'], width: 14}]}
                    ],
                }
            );
            var expected = [
                {
                    layout: {
                        type: 'dashlet-row',
                        width: 14,
                        components: ['row 1, tab 1', 'row 2, tab 1'],
                        index: '0',
                    }
                }
            ];
            expect(initComponentsStub).toHaveBeenCalledWith(expected);
        });

        it('should allow non-dashlet tab for tabbed dashboard', function() {
            var tab0 = {name: 'tab0', components: [{rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}]};
            var tab1 = {name: 'tab1', components: [{view: 'multi-line-list'}]};
            var metadata = {tabs: [tab0, tab1]};
            layout.model.set('metadata', metadata, {silent: true});

            layout.setMetadata({tabIndex: 1});

            var expected = [{view: 'multi-line-list'}];
            expect(initComponentsStub).toHaveBeenCalledWith(expected);
        });
    });
});
