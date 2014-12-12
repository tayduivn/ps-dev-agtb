//***********************other class******************************************************//
describe('jCore.Polygon', function () {
	var a, p, q, r, s, t, x, y, z;
	beforeEach(function () {
		p = new jCore.Point(100, 100);
		q = new jCore.Point(30, 30);
		r = new jCore.Point(50, 20);
		s = new jCore.Point(500, 200);
		t = new jCore.Point(700, 600);
		a = new jCore.Polygon(
									{
										points: [p, q, r, s, t]
									}
			);
		document.body.appendChild(a.getHTML());
	});
	afterEach(function () {
		$(a.getHTML()).remove();
		a = null;
	});
	describe('method "getPoints"', function () {
		it('should return the points of the polygon', function () {
			expect(a.getPoints().length).toEqual(5);
			expect(a.getPoints()[0]).toEqual(p);
			expect(a.getPoints()[1]).toEqual(q);
			expect(a.getPoints()[2]).toEqual(r);
			expect(a.getPoints()[3]).toEqual(s);
			expect(a.getPoints()[4]).toEqual(t);
		});
	});
	describe('method "setPoints"', function () {
		it('should set the points for the polygon', function () {
			x = new jCore.Point(23,34);
			y = new jCore.Point(56,67);
			z = new jCore.Point(78,89);
			a.setPoints([x, y, z]);
			expect(a.getPoints().length).toEqual(3);
			expect(a.getPoints()[0]).toEqual(x);
			expect(a.getPoints()[1]).toEqual(y);
			expect(a.getPoints()[2]).toEqual(z);
		});
	});
});