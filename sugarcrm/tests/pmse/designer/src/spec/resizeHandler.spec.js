//***********************other class******************************************************//
describe('jCore.ResizeHandler', function () {
	var a, b, s, r, resizableStyle, nonResizableStyle;
	beforeEach(function () {
		s = new jCore.Shape();
		r = new jCore.Rectangle();
		resizableStyle = {
                   cssProperties: {
                       'background-color': "rgb(0, 255, 0)",
                       'border': '1px solid black'
                   }
               };
        nonResizableStyle = {
                   cssProperties: {
                       'background-color': "white",
                       'border': '1px solid black'
                   }
               };
        a = new jCore.ResizeHandler({
               width: 8,
               height: 8,
               parent: s,
               orientation: 'nw',  
               representation: s,
               resizableStyle: resizableStyle,
               nonResizableStyle: nonResizableStyle,
               zOrder: 2
           });
        document.body.appendChild(a.getHTML());
	});
	afterEach(function () {
		$(a.getHTML()).remove();
		a = null;
		b = null;
	});
	describe('method "setParent"', function () {
		it('should set a new parent for resizeHandler', function () {
			b = new jCore.Shape();
			expect(a.parent).toEqual(s);
			expect(function () {a.setParent(b)}).not.toThrow();
			expect(a.parent).not.toEqual(s);
			expect(a.parent).toEqual(b);
		});
	});
	describe('method "getParent"', function () {
		it('should return the parent of the resizeHandler', function () {
			b = new jCore.Shape();
			a.setParent(b);
			expect(a.getParent() === b).toBeTruthy();
		});
	});
	describe('method "setCategory"', function () {
		it('should set the category of the resizeHandler as a string', function () {
			expect(a.category).toBeNull();
			a.setCategory("new_category");
			expect(a.category).toEqual("new_category");
		});
		it('should set the category of the resizeHandler as resizable', function () {
			expect(a.category).toBeNull();
			a.setCategory("resizable");
			expect(a.category).toEqual("resizable");
			expect(a.color.red === 0).toBeTruthy();
			expect(a.color.green === 255).toBeTruthy();
			expect(a.color.blue === 0).toBeTruthy();
			expect(a.style.cssClasses.length === 2).toBeTruthy();
			expect(a.style.cssClasses[0] === "ui-resizable-handle").toBeTruthy();
			expect(a.style.cssClasses[1] === "ui-resizable-" + a.orientation).toBeTruthy();
		});
	});
	describe('method "setResizableStyle"', function () {
		it('should set the resizableStyle to the shape', function () {
			resizableStyle = {
                   cssProperties: {
                       'background-color': "rgb(12, 5, 20)",
                       'border': '2px solid black'
                   }
            };
            a.setResizableStyle(resizableStyle);
            expect(a.resizableStyle.cssProperties.border).toEqual("2px solid black");
		});
	});
	describe('method "setNonResizableStyle"', function () {
		it('should set the nonResizableStyle to the shape', function () {
			nonResizableStyle = {
                   cssProperties: {
                       'background-color': "white",
                       'border': '1px solid black'
                   }
            };
            a.setNonResizableStyle(nonResizableStyle);
            expect(a.nonResizableStyle.cssProperties.border).toEqual('1px solid black');
		});
	});
});