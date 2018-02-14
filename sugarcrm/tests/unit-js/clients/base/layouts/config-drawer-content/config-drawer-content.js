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
describe('Base.Layout.ConfigDrawerContent', function() {
    var app;
    var context;
    var layout;
    var options;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());

        var meta = {
            components: [{
                view: 'config-panel'
            }, {
                view: 'config-panel'
            }]
        };

        options = {
            context: context,
            meta: meta
        };

        layout = SugarTest.createLayout('base', null, 'config-drawer-content', meta, context, false);
        layout._components[1].name = 'config-panel2';
    });

    afterEach(function() {
        sinon.collection.restore();
        layout.dispose();
        layout = null;
    });

    describe('initialize()', function() {
        beforeEach(function() {
            sinon.collection.stub(layout, '_super');
            sinon.collection.stub(layout, '_initHowTo');
        });

        it('should call _initHowTo', function() {
            layout.initialize(options);

            expect(layout._initHowTo).toHaveBeenCalled();
        });
    });

    describe('_render()', function() {
        var collapseStub;

        beforeEach(function() {
            collapseStub = sinon.collection.stub();
            sinon.collection.stub(layout.$el, 'addClass');
            sinon.collection.stub(layout.$el, 'attr');
            sinon.collection.stub(layout, '_super');
            sinon.collection.stub(layout, 'selectPanel');
            sinon.collection.stub(layout, '$', function() {
                return {
                    collapse: collapseStub
                };
            });
        });

        afterEach(function() {
            collapseStub = null;
        });

        it('should add CSS class accordion', function() {
            layout._render();

            expect(layout.$el.addClass).toHaveBeenCalledWith('accordion');
        });

        it('should set id on el', function() {
            layout.collapseDivId = 'test1';
            layout._render();

            expect(layout.$el.attr).toHaveBeenCalledWith('id', 'test1');
        });

        it('should call collapse on the .collapse element', function() {
            layout.collapseDivId = 'test1';
            layout._render();

            expect(layout.$).toHaveBeenCalledWith('.collapse');
            expect(collapseStub).toHaveBeenCalledWith({
                toggle: false,
                parent: '#test1'
            });
        });

        it('should call selectPanel with the first component view', function() {
            layout._render();

            expect(layout.selectPanel).toHaveBeenCalledWith('config-panel');
        });
    });

    describe('selectPanel()', function() {
        var collapseStub;

        beforeEach(function() {
            collapseStub = sinon.collection.stub();
            sinon.collection.stub(layout, '$', function() {
                return {
                    collapse: collapseStub
                };
            });
            sinon.collection.stub(layout, 'onAccordionToggleClicked');
        });

        afterEach(function() {
            collapseStub = null;

        });

        it('should set selectedPanel to the passed in panelName', function() {
            layout.selectPanel('config-panel');

            expect(layout.selectedPanel).toBe('config-panel');
        });

        it('should call collapse on the panel name id', function() {
            layout.selectPanel('config-panel');

            expect(layout.$).toHaveBeenCalledWith('#config-panelCollapse');
            expect(collapseStub).toHaveBeenCalledWith('show');
        });

        it('should call onAccordionToggleClicked', function() {
            layout.selectPanel('config-panel');

            expect(layout.onAccordionToggleClicked).toHaveBeenCalled();
        });
    });

    describe('onAccordionToggleClicked()', function() {
        var event;
        var triggerStub;
        var triggerStub2;

        beforeEach(function() {
            sinon.collection.stub(layout, '_switchHowToData');
            sinon.collection.stub(layout.context, 'trigger');
            event = {
                currentTarget: $.noop
            };
            triggerStub = sinon.collection.stub(layout._components[0], 'trigger');
            triggerStub2 = sinon.collection.stub(layout._components[1], 'trigger');
        });

        afterEach(function() {
            event = null;
            triggerStub = null;
            triggerStub2 = null;
        });

        it('call _switchHowToData from the current target data help-id', function() {
            layout.selectedPanel = undefined;
            sinon.collection.stub($.fn, 'data', function() {
                return 'config-panel';
            });
            layout.onAccordionToggleClicked(event);

            expect(layout._switchHowToData).toHaveBeenCalledWith('config-panel');
        });

        it('call _switchHowToData from the selectedPanel when data help-id is falsy', function() {
            layout.selectedPanel = 'config-panel';
            layout.onAccordionToggleClicked();

            expect(layout._switchHowToData).toHaveBeenCalledWith('config-panel');
        });

        it('should trigger config:howtoData:change on the context', function() {
            layout.selectedPanel = 'config-panel';
            layout.currentHowToData = 'test';
            layout.onAccordionToggleClicked();

            expect(layout.context.trigger).toHaveBeenCalledWith('config:howtoData:change', 'test');
        });

        it('should trigger config:panel:hide on old panel if it exists', function() {
            layout.selectedPanel = 'config-panel2';
            layout.onAccordionToggleClicked();

            expect(triggerStub2).toHaveBeenCalledWith('config:panel:hide');
        });

        it('call set selectedPanel to the new panel id', function() {
            layout.selectedPanel = 'config-panel2';
            sinon.collection.stub($.fn, 'data', function() {
                return 'config-panel';
            });
            layout.onAccordionToggleClicked(event);

            expect(layout.selectedPanel).toBe('config-panel');
        });

        it('call toggle the new panel to show', function() {
            layout.selectedPanel = 'config-panel2';
            sinon.collection.stub($.fn, 'data', function() {
                return 'config-panel';
            });
            layout.onAccordionToggleClicked(event);

            expect(triggerStub).toHaveBeenCalledWith('config:panel:show');
        });
    });

    describe('changeHowToData()', function() {
        beforeEach(function() {
            sinon.collection.stub(layout.context, 'trigger');
        });

        afterEach(function() {

        });

        it('should set currentHowToData title', function() {
            layout.changeHowToData('title1', 'text1');

            expect(layout.currentHowToData.title).toBe('title1');
        });

        it('should set currentHowToData text', function() {
            layout.changeHowToData('title1', 'text1');

            expect(layout.currentHowToData.text).toBe('text1');
        });

        it('should trigger config:howtoData:change on the context', function() {
            layout.changeHowToData('title1', 'text1');

            expect(layout.context.trigger).toHaveBeenCalledWith('config:howtoData:change', {
                title: 'title1',
                text: 'text1'
            });
        });
    });
});
