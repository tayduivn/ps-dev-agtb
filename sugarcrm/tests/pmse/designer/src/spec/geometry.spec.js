//***********************other class******************************************************//
describe('jCore.Geometry', function () {
	var a, point1, point2, point3;
	beforeEach(function () {
		a = jCore.Geometry;
		point1 = new jCore.Point(4,5);
		point2 = new jCore.Point(5,6);
		point3 = new jCore.Point(0,0);		
	});
	afterEach(function () {
	});
	describe('method "cross"', function () {
		it('should Calculates the cross product of 2-dimensional vectors', function () {
			expect(jCore.Geometry.cross(point1,  point2) == -1).toBeTruthy();
		});
	})
	describe('method "area"', function () {
		it('should Calculates the SIGNED area of a parallelogram given three points,', function () {
			expect(jCore.Geometry.area(point3, point1, point2) == -1).toBeTruthy();
		});
	})
	describe('method "onSegment"', function () {
		it('should Determines if the point P is on segment AB', function () {
			var point4 = new jCore.Point(2,3);
			var point5 = new jCore.Point(5.5,3.5);			
			expect(jCore.Geometry.onSegment(point3, point1, point2)).toBeFalsy();
			expect(jCore.Geometry.onSegment(point4, point1, point2)).toBeFalsy();
			expect(jCore.Geometry.onSegment(point5, point1, point2)).toBeFalsy();			
		});
	})
	describe('method "perpendicularSegmentIntersection"', function () {
		it('should Checks if two perpendicular segments intersect, if so it returns the intersection point,', function () {
			var point4 = new jCore.Point(4,5);
			var point5 = new jCore.Point(5,6);
			var point6 = new jCore.Point(8,10);
			var point7 = new jCore.Point(10,12);
			//jCore.Geometry.perpendicularSegmentIntersection(point4,point5,point6,point7)
		});
	})
	describe('method "pointInCircle"', function () {
		it('should determines whether a point is in a circle or not given its center and radius', function () {
			var center = new jCore.Point(0,0);
			var point4 = new jCore.Point(4,5);
			var radius = 8;			
			expect(jCore.Geometry.pointInCircle(point4,center,radius)).toBeTruthy();
			radius = 4;
			expect(jCore.Geometry.pointInCircle(point4,center,radius)).toBeFalsy();
			
		});
	})
	describe('method "pointInRectangle"', function () {
		it('should Determines whether a point is in a given rectangle', function () {
			var pr = new jCore.Point(0,0);
			var br = new jCore.Point(5,5);
			var point = new jCore.Point(1,1);
			expect(jCore.Geometry.pointInRectangle(point,pr,br)).toBeTruthy();
			point = new jCore.Point(5,5);
			expect(jCore.Geometry.pointInRectangle(point,pr,br)).toBeTruthy();
			point = new jCore.Point(6,5);
			expect(jCore.Geometry.pointInRectangle(point,pr,br)).toBeFalsy();
		});
	})
	describe('method "segmentIntersectionPoint"', function () {
		it('Checks if two segments intersect, if so it returns the intersection point', function () {
			var p1 = new jCore.Point(0,0);
			var p2 = new jCore.Point(0,5);
			var p3 = new jCore.Point(1,0);
			var p4 = new jCore.Point(1,5);
			expect(jCore.Geometry.segmentIntersectionPoint(p1,p2,p3,p4).x.toString() === "NaN").toBeTruthy();
			p3 = new jCore.Point(-3,3);
			p4 = new jCore.Point(3,3);
			expect(jCore.Geometry.segmentIntersectionPoint(p1,p2,p3,p4).x.toString() === "0").toBeTruthy();
			expect(jCore.Geometry.segmentIntersectionPoint(p1,p2,p3,p4).y.toString() === "3").toBeTruthy();
		});
	})
});