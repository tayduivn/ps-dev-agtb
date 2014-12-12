//***********************other class******************************************************//
describe('jCore.Segment', function () {
	var a, b, c, p, i;
	beforeEach(function() {
    	a = new jCore.Segment(); 
    });
	describe('method "createHTML"', function () {
		it('should create a new HTML element', function () {
			var h = a.createHTML();
            expect(h).toBeDefined();
            expect(h.tagName).toBeDefined();
            expect(h.nodeType).toBeDefined();
            expect(h.nodeType).toEqual(document.ELEMENT_NODE);
		});
	});
	describe('method "setParent"', function () {
		it('should set the parent for the Segment', function () {
			expect(a.parent).toBeNull();
			a.setParent({});
			expect(a.parent).not.toBeNull();
		});
	});
	describe('method "getParent"', function () {
		it('should get the parent for the segment', function () {
			expect(a.getParent()).toBeNull();
			a.setParent({});
			expect(a.getParent()).not.toBeNull();
		});
	});
	describe('method "setStartPoint"', function () {
		it('should set the start point of the segment', function () {
			p = new jCore.Point();
			p.x = 34;
			p.y = 45;
			b = new jCore.Segment(p);
			b.setStartPoint(p);
			expect(b.startPoint === p).toBeTruthy();
			expect(b.startPoint.x === 34).toBeTruthy();
			expect(b.startPoint.y === 45).toBeTruthy();
		});
	});
	describe('method "getStartPoint"', function () {
		it('should get the start point of the segment', function () {
			p = new jCore.Point();
			p.x = 34;
			p.y = 45;
			b = new jCore.Segment(p);
			b.setStartPoint(p);
			expect(b.getStartPoint() === p).toBeTruthy();
			expect(b.getStartPoint().x === 34).toBeTruthy();
			expect(b.getStartPoint().y === 45).toBeTruthy();
		});
	});
	describe('method "setEndPoint"', function () {
		it('should set the end point of the segment', function () {
			p = new jCore.Point();
			p.x = 3;
			p.y = 4;
			b = new jCore.Segment(p);
			b.setEndPoint(p);
			expect(b.endPoint === p).toBeTruthy();
			expect(b.endPoint.x === 3).toBeTruthy();
			expect(b.endPoint.y === 4).toBeTruthy();
		});
	});
	describe('method "getEndPoint"', function () {
		it('should get the end point of the segment', function () {
			p = new jCore.Point();
			p.x = 3;
			p.y = 4;
			b = new jCore.Segment(p);
			b.setEndPoint(p);
			expect(b.getEndPoint() === p).toBeTruthy();
			expect(b.getEndPoint().x === 3).toBeTruthy();
			expect(b.getEndPoint().y === 4).toBeTruthy();
		});
	});
	describe('method "setStyle"', function () {
		it('should set the segmentStyle of this segment', function () {
			a.setStyle("newStyle");
			expect(a.segmentStyle === "newStyle").toBeTruthy();
		});
	});
	describe('method "setColor"', function () {
		it ('should set the color of this segment', function () {
			c = new jCore.Color();
			a.setColor(c);
			expect(a.segmentColor === c).toBeTruthy();
			expect(a.segmentColor.red === 0).toBeTruthy();
			expect(a.segmentColor.green === 0).toBeTruthy();
			expect(a.segmentColor.blue === 0).toBeTruthy();
			expect(a.segmentColor.opacity === 1).toBeTruthy();
		});
	});
	describe('method "clearIntersections"', function () {
		it('should clear all the intersections of the segment', function () {
			expect(a.intersections).toBeDefined();
			expect(a.intersections).not.toBeNull();
			a.clearIntersections();
			expect(a.intersections.getSize() === 0).toBeTruthy();
		});
	});
});