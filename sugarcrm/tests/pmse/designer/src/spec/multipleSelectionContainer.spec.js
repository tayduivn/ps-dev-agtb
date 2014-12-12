//***********************other class******************************************************//
describe('jCore.MultipleSelectionContainer', function () {
	var a, b;
	beforeEach(function () {
		a = new jCore.MultipleSelectionContainer();
		document.body.appendChild(a.getHTML());
	});
	afterEach(function () {
		$(a.getHTML()).remove();
		a = null;
	});
	describe('method "paint"', function () {
		it('should paint the MultipleSelectionContainer', function () {
			expect(a.style.cssProperties.backgroundColor).not.toBeDefined();
			expect(function () {a.paint()}).not.toThrow();
			expect(a.style.cssProperties.backgroundColor).toBeDefined();
			expect(typeof a.style.cssProperties.backgroundColor === "string").toBeTruthy();
			expect(a.style.cssProperties.backgroundColor).toEqual("rgba(0,128,255,0.1)");
		});
	});
	describe('method "changeOpacity"', function () {
		it('should change the opacity of the MultipleSelectionContainer', function () {
			b = a.backgroundColor.opacity;
			expect(a.backgroundColor.opacity).toBeDefined();
			expect(function () {a.changeOpacity(2)}).not.toThrow();
			expect(a.backgroundColor.opacity).not.toEqual(b);
			expect(a.backgroundColor.opacity === 2).toBeTruthy();
		});
	});
	describe('method "checkIntersection"', function () {
		it('should return a boolean value checking the intersection', function () {
			b = new jCore.Shape();
			expect(function () {a.checkIntersection(b)}).not.toThrow();
			expect(a.checkIntersection(b)).toBeTruthy();
		});
	});
});