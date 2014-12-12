//***********************other class******************************************************//
describe('jCore.Handler', function () {
	var a, b;
	beforeEach(function () {
		a = new jCore.Handler();
		document.body.appendChild(a.getHTML());
	});
	afterEach(function () {
		$(a.getHTML()).remove();
		a = null;
	});
	describe('method "setParent"', function () {
		it('should set the parent for the handler', function () {
			b = new jCore.Shape();
			expect(function () {a.setParent(b)}).not.toThrow();
			expect(a.parent).toEqual(b);
		});
	});
	describe('method "getParent"', function () {
		it('should return the parent of the current Handler', function () {
			expect(a.getParent()).toBeNull();
			b = new jCore.Shape();
			a.setParent(b);
			expect(a.getParent()).toEqual(b);
		});
	});
	describe('method "setRepresentation"', function () {
		it('should set the representation of the handler', function () {
			expect(a.representation).toBeNull();
			expect(function () {a.setRepresentation({})}).not.toThrow();
			expect(typeof a.representation === "object").toBeTruthy();
		});
	});
	describe('method "getRepresentation"', function () {
		it('should return the representation of the handler', function () {
			expect(a.getRepresentation()).toBeNull();
			a.setRepresentation({});
			expect(typeof a.getRepresentation() === "object").toBeTruthy();
		});
	});
	describe('method "setOrientation"', function () {
		it('should set the orientation of the handler', function () {
			expect(a.orientation).toBeNull();
			expect(function () {a.setOrientation("top")}).not.toThrow();
			expect(a.orientation === "top").toBeTruthy();
		});
	});
	describe('method "getOrientation"', function () {
		it('should return the orientation of the handler', function () {
			expect(a.getOrientation()).toBeNull();
			a.setOrientation("top");
			expect(a.getOrientation() === "top").toBeTruthy();
		});
	});
	describe('method "setColor"', function () {
		it('should set the color of the handler', function () {
			expect(a.color).toBeNull();
			b = new jCore.Color(10,20,20,1);
			expect(function () {a.setColor(b)}).not.toThrow();
			expect(a.color).toEqual(b);
		});
	});
	describe('method "getColor"', function () {
		it('should return the color of the handler', function () {
			expect(a.getColor()).toBeNull();
			b = new jCore.Color(10,20,20,1);
			a.setColor(b);
			expect(a.getColor()).toEqual(b);
		});
	});
});