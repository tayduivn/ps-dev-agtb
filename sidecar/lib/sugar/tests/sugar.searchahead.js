describe("searchahead", function() {
    var $input, div, form;

    beforeEach(function() {
        div = $("<div id='searchForm' />");
        form = $('<form />');
        $input = $('<input />');
        div.append(form.append($input));
        $input.searchahead({
            request: function() {},
            compiler: function() {}
        });
    });
    afterEach(function() {
        $input = null;
    });

    it("should listen to input", function() {
        expect($input.data('events').blur).toBeDefined();
        expect($input.data('events').keypress).toBeDefined();
        expect($input.data('events').keyup).toBeDefined();
    });
    it('should provide sensible defaults', function() {
        expect($input.searchahead().data('searchahead').throttle).toBeDefined();
        expect($input.searchahead().data('searchahead').throttleMillis).toBe(250);
        expect($input.searchahead().data('searchahead').minChars).toBe(3);
        expect($input.searchahead().data('searchahead').$button).toBeNull();
    });
    it("should create a menu", function () {
        expect($input.searchahead().data('searchahead').$menu).toBeDefined();
    });
    it("should menu with dropdown-menu container", function () {
        expect(/dropdown.*/.test($input.searchahead().data('searchahead').$menu[0].outerHTML)).toBeTruthy();
        expect(/xxx/.test($input.searchahead().data('searchahead').$menu[0].outerHTML)).toBeFalsy();
    });
    it("should have mouseover on the $menu", function() {
        var $menu = $input.searchahead().data('searchahead').$menu;
        expect($menu.data('events').mouseover).toBeDefined();
    });
    it("should call lookup when search term entered", function() {
        var searchahead = $input.data('searchahead'), 
            spy = sinon.spy(searchahead, 'onSearch');
        $input.val('a');
        $input.trigger({
            type: 'keyup',
            keyCode: 65 
        });
        expect(spy).toHaveBeenCalled();
        expect(spy.getCall(0).args[0].keyCode).toEqual(65);
        searchahead.$menu.remove();
      });

    it("should not call lookup when down, arrow, escape chars entered", function() {
        var searchahead = $input.data('searchahead'), 
            spy = sinon.spy(searchahead, 'onSearch');
        $input.val('a');
        $input.trigger({
            type: 'keyup',
            keyCode: 40 
        });
        expect(spy).not.toHaveBeenCalled();
        $input.trigger({
            type: 'keyup',
            keyCode: 38
        });
        expect(spy).not.toHaveBeenCalled();
        $input.trigger({
            type: 'keyup',
            keyCode: 27
        });
        expect(spy).not.toHaveBeenCalled();
        searchahead.$menu.remove(); 
    });

    it("should call provided functions", function() {
        // Create fresh new searchahead so it doesn't reuse from beforeEach
        div = $("<div id='searchForm' />");
        form = $('<form />');
        $input = $('<input />');
        div.append(form.append($input));

            $input.searchahead({
                request: function() {
                    var self = this;
                    setTimeout(function() {
                        self.provide('{"yes": "it works"}');
                    }, 20);
                },
                compiler: function(o) {
                    return '<li>'+JSON.parse(o.data).yes+'</li>';
                },
                minChars: -1,
                throttleMillis: 20,
                menu: '<ul class="typeahead dropdown-menu"></ul>'
            });

        $input.val('a');
        $input.trigger({
            type: 'keyup',
            keyCode: 65 
        });
    
        expect($input.data('searchahead').minChars).toBe(-1);
        waits(100);
        runs(function () {
            // some browsers will add style="" ,etc., so use regex so not brittle ;=)
            expect($input.data('searchahead').$menu[0].outerHTML).toMatch(/<ul class="typeahead dropdown-menu.*li class="active.*it works/);
        });
    });
    
});

