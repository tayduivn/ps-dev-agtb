//***********************other class******************************************************//
describe('jCore.RegularShape', function () {
	var a, b;
	beforeEach(function () {
		a = new jCore.RegularShape(
								{
									height: 200,
									id: 'rsha1',
									width: 200,
									container: 'container',
									resizeBehavior: 'yes',

								}
			);
		document.body.appendChild(a.getHTML());
	});
	afterEach(function () {
		$(a.getHTML()).remove();
		a = null;
	});
	describe('method "setColor"', function () {
		it('should set the shape color', function () {
			b = new jCore.Color(100, 50, 45, 1);
			a.setColor(b);
			expect(typeof a.color === "object").toBeTruthy();
			expect(a.color.red).toEqual(100);
			expect(a.color.green).toEqual(50);
			expect(a.color.blue).toEqual(45);
			expect(a.color.opacity).toEqual(1);
		});
	});
	describe('method "getColor"', function () {
		it('should return the shape color', function () {
			b = new jCore.Color(150, 100, 50, 1);
			a.setColor(b);
			expect(typeof a.getColor() === 'object').toBeTruthy();
			expect(a.getColor().red === 150).toBeTruthy();
			expect(a.getColor().green === 100).toBeTruthy();
			expect(a.getColor().blue === 50).toBeTruthy();
			expect(a.getColor().opacity === 1).toBeTruthy();
		});
	});
});