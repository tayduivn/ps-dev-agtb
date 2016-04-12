describe('Sugar.Tooltip', function() {

    var app, tooltip, $fixture;

    beforeEach(function() {
        app = SugarTest.app;
        tooltip = app.tooltip;

        $fixture = $('<div id="Sugar.Tooltips">').appendTo('body');
    });

    afterEach(function() {
        sinon.collection.restore();
        $fixture.remove();
        // remove any tooltips created in the DOM (added in to the body)
        $('.tooltip').remove();
    });

    it('should allow clear to be called without any existing tooltip', function() {
        expect($('.tooltip').length).toBe(0);
        app.tooltip.clear();
        expect($('.tooltip').length).toBe(0);
    });

    describe('touch devices', function() {

        beforeEach(function() {
            sinon.collection.stub(Modernizr, 'touch', true);
            tooltip.init();
        });

        it('should have tooltips disabled', function() {
            var $el = $('<div rel="tooltip" title="tooltip unit test">').appendTo($fixture);
            $el.trigger('mouseenter');
            expect($el.data('bs.tooltip')).not.toBeDefined();
        });
    });

    describe('non-touch devices tooltips', function() {

        var $el;

        beforeEach(function() {
            sinon.collection.stub(Modernizr, 'touch', false);
            tooltip.init();

            $el = $('<div rel="tooltip" title="tooltip unit test">').appendTo($fixture);
        });

        it('should have tooltips enabled', function() {
            $el.trigger('mouseenter');
            expect($el.data('bs.tooltip')).toBeDefined();
            expect($('.tooltip').length).toBe(1);
        });

        it('should remove any tooltips when clicking in the element', function() {
            $el.trigger('mouseenter');
            expect($('.tooltip').length).toBe(1);
            $el.trigger('click');
            expect($('.tooltip').length).toBe(0);
        });

        it('should remove any tooltips when clicking on any element', function() {
            var $el2 = $('<div>Unrelated Div</div>').appendTo($fixture);
            $el.trigger('mouseenter');
            expect($('.tooltip').length).toBe(1);
            $el2.trigger('click');
            expect($('.tooltip').length).toBe(0);
        });

        it('should remove any tooltips when calling clear', function() {
            $el.trigger('mouseenter');
            expect($('.tooltip').length).toBe(1);
            app.tooltip.clear();
            expect($('.tooltip').length).toBe(0);
        });
    });

    describe('non-touch devices ellipsis', function() {

        var $el;

        beforeEach(function() {
            sinon.collection.stub(Modernizr, 'touch', false);
            tooltip.init();

            $el = $('<div class="ellipsis_inline" title="tooltip unit test"></div>').appendTo($fixture);
        });

        it('should initialize but not show tooltip if the element has enough space', function() {
            $el.trigger('mouseenter');
            expect($el.data('bs.tooltip')).toBeDefined();
            expect($('.tooltip').length).toBe(0);
        });

        it('should initialize and show tooltip if the element does not have enough space', function() {
            $el.text('Example');
            $el.css('width', '1px');
            $el.trigger('mouseenter');
            expect($('.tooltip').length).toBe(1);
        });

    });
});
