//***********************other class******************************************************//
describe('PMUI.util.Color', function () {
	var a, b;
	beforeEach(function () {
		a = new PMUI.util.Color(12,23,34,1);
	});
	afterEach(function () {
		a = null;
		b = null;
	});
	describe('method "setRed"', function () {
		it('should set the color red, it must be number between 0 and 255', function () {
			b = a.red;
			a.setRed("23");
			expect(a.red).toEqual(b);
			a.setRed({});
			expect(a.red).toEqual(b);
			a.setRed(300);
			expect(a.red).toEqual(b);
			a.setRed(-23);
			expect(a.red).toEqual(b);
			a.setRed(40);
			expect(a.red).not.toEqual(b);
			expect(a.red).toEqual(40);
		});
	});
	describe('method "getRed"', function () {
		it('should return the color red', function () {
			a.setRed(40);
			expect(a.getRed()).toEqual(40);
		});
	});
	describe('method "setGreen"', function () {
		it('should set the color green, it must be number between 0 and 255', function () {
			b = a.green;
			a.setGreen("23");
			expect(a.green).toEqual(b);
			a.setGreen({});
			expect(a.green).toEqual(b);
			a.setGreen(600);
			expect(a.green).toEqual(b);
			a.setGreen(-23);
			expect(a.green).toEqual(b);
			a.setGreen(50);
			expect(a.green).not.toEqual(b);
			expect(a.green).toEqual(50);
		});
	});
	describe('method "getGreen"', function () {
		it('should return the color Green', function () {
			a.setGreen(40);
			expect(a.getGreen()).toEqual(40);
		});
	});
	describe('method "setBlue"', function () {
		it('should set the color blue, it must be number between 0 and 255', function () {
			b = a.blue;
			a.setBlue("23");
			expect(a.blue).toEqual(b);
			a.setBlue({});
			expect(a.blue).toEqual(b);
			a.setBlue(300);
			expect(a.blue).toEqual(b);
			a.setBlue(-23);
			expect(a.blue).toEqual(b);
			a.setBlue(40);
			expect(a.blue).not.toEqual(b);
			expect(a.blue).toEqual(40);
		});
	});
	describe('method "getBlue"', function () {
		it('should return the color blue', function () {
			a.setBlue(40);
			expect(a.getBlue()).toEqual(40);
		});
	});
	describe('method "setOpacity"', function () {
		it('should set the opacity, it must be number between 0 and 255', function () {
			b = a.opacity;
			a.setOpacity("23");
			expect(a.opacity).toEqual(b);
			a.setOpacity({});
			expect(a.opacity).toEqual(b);
			a.setOpacity(300);
			expect(a.opacity).toEqual(b);
			a.setOpacity(-23);
			expect(a.opacity).toEqual(b);
			a.setOpacity(10);
			expect(a.opacity).not.toEqual(b);
			expect(a.opacity).toEqual(10);
		});
	});
	describe('method "getOpacity"', function () {
		it('should return the opacity', function () {
			a.setOpacity(10);
			expect(a.getOpacity()).toEqual(10);
		});
	});
	describe('method "getCSS"', function () {
		it('should return the css representation of the RGB color', function () {
			expect(a.getCSS()).toEqual("rgba(12,23,34,1)");
		});
	});
});