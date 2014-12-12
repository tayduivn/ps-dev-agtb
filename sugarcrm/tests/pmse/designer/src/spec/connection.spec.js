//***********************other class******************************************************//
describe('jCore.Connection', function () {
	var a, b;
	beforeEach(function () {
		a = new jCore.Connection({
               srcPort:  new jCore.Port(),
               destPort:  new jCore.Port(),
               segmentColor:  new jCoreColor(0, 200, 0),
               segmentColor:  "regular"
        });
        document.body.appendChild(a.getHTML());
	});
	afterEach(function () {
		$(a.getHTML()).remove();
		a = null;
	});
	describe('method "createHTML"', function () {
		it('should create a new HTML element', function () {
			var html = a.createHTML();
            expect(html).toBeDefined();
            expect(html.tagName).toBeDefined();
            expect(html.nodeType).toBeDefined();
            expect(html.nodeType).toEqual(document.ELEMENT_NODE);
		});
	});
	describe('method "getPoints"', function () {
		it('should return the points of the Connection', function () {
			expect(a.getPoints()).not.toBeNull();
			expect(a.getPoints().length).toEqual(0);
		});
	});
	describe('method "getOldPoints"', function () {
		it('should return the old points of the Connection', function () {
			expect(a.getOldPoints()).not.toBeNull();
			expect(a.getOldPoints().length).toEqual(0);
		});
	});
	describe('method "getZOrder"', function () {
		it('should return the Z order of the Connection', function () {
			expect(function () {a.setZOrder(23)}).not.toThrow();
			expect(a.getZOrder()).toBeDefined();
			expect(a.getZOrder()).toEqual(23);
		});
	});
	describe('method "setDestDecorator"', function () {
		it('should set the destination decorator of the Connection', function () {
			expect(function () {a.setDestDecorator(new PMUI.Field.TextAreaField())}).toThrow();
			b = new jCore.ConnectionDecorator();
			expect(function () {a.setDestDecorator(b)}).not.toThrow();
			expect(a.getDestDecorator()).toEqual(b);
		});
	});
	describe('method "setSrcDecorator"', function () {
		it('should set the source decorator of the Connection', function () {
			expect(function() {a.setSrcDecorator(new PMUI.Field.TextAreaField())}).toThrow();
			b = new jCore.ConnectionDecorator();
			expect(function () {a.setSrcDecorator(b)}).not.toThrow();
			expect(a.getSrcDecorator()).toEqual(b);
		});
	});
	describe('method "getLineSegments"', function () {
		it('should get Line Segments of the Connection', function () {
			expect(a.getLineSegments()).not.toBeNull();
			expect(typeof a.getLineSegments() === "object").toBeTruthy();
			expect(a.getLineSegments().asArray().length).toEqual(0);
		});
	});
	describe('method "getDestDecorator"', function () {
		it('should return the destination decorator of the Connection', function () {
			b = new jCore.ConnectionDecorator();
			a.setDestDecorator(b);
			expect(a.getDestDecorator()).toEqual(b);
		});
	});
	describe('method "getSrcDecorator"', function () {
		it('should return the source decorator of the Connection', function () {
			b = new jCore.ConnectionDecorator();
			a.setSrcDecorator(b);
			expect(a.getSrcDecorator()).toEqual(b);
		});
	});
	describe('method "setDestPort"', function () {
		it('should set the destination port of the Connection', function () {
			b = new jCore.Port();
			expect(function () {a.setDestPort(b)}).not.toThrow();
			expect(a.getDestPort()).toEqual(b);
		});
	});
	describe('method "getDestPort"', function () {
		it('should return the destination port of the Connection', function () {
			b = new jCore.Port();
			a.setDestPort(b);
			expect(a.getDestPort()).toEqual(b);
		});
	});
	describe('method "setSrcPort"', function () {
		it('should set the source port of the Connection', function () {
			b = new jCore.Port();
			expect(function () {a.setSrcPort(b)}).not.toThrow();
			expect(a.getSrcPort()).toEqual(b);
		});
	});
	describe('method "getSrcPort"', function () {
		it('should return the destination port of the Connection', function () {
			b = new jCore.Port();
			a.setSrcPort(b);
			expect(a.getSrcPort()).toEqual(b);
		});
	});
	describe('method "setSegmentStyle"', function () {
		it('should set the segment style of the Connection', function () {
			expect(function () {a.setSegmentStyle("newStyle", true)}).not.toThrow();
			expect(a.getSegmentStyle()).toEqual("newStyle");
		});
	});
	describe('method "getSegmentStyle"', function () {
		it('should return the segment style of the Connection', function () {
			a.setSegmentStyle("newStyle", true);
			expect(a.getSegmentStyle()).toEqual("newStyle");
		});
	});
	describe('method "setSegmentColor"', function () {
		it('should set the segment color of the Connection', function () {
			b = new PMUI.util.Color(100, 200, 50);
			expect(function () {a.setSegmentColor(b, true)}).not.toThrow();
			expect(a.getSegmentColor()).toEqual(b);
		});
	});
	describe('method "getSegmentColor"', function () {
		it('should return the segment color of the Connection', function () {
			b = new PMUI.util.Color(100, 200, 50);
			a.setSegmentColor(b, true);
			expect(a.getSegmentColor()).toEqual(b);
		});
	});
	describe('method "move"', function () {
		it('should move the Connection', function () {
			var x, y, x1, y1;
			x = parseInt(a.html.style.left);
			y = parseInt(a.html.style.top);
			x1 = 10;
			y1 = 15;
			expect(function () {a.move(x1, y1)}).not.toThrow();
			expect(parseInt(a.html.style.left)).toEqual(x+x1);
			expect(parseInt(a.html.style.top)).toEqual(y+y1);
		});
	});
	describe('method "clearAllIntersections"', function () {
		it('should clear all the intersections of the Connection', function () {
			expect(function () {a.clearAllIntersections()}).not.toThrow();
			expect(a.intersectionWith.asArray().length).toEqual(0);
		});
	});
});