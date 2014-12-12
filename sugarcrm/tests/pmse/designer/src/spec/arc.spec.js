//***********************other class******************************************************//
describe('jCore.Arc', function () {
	var a, b;
	beforeEach(function () {
		a = new jCore.Arc(
						{
							center: new jCore.Point(10, 10),
							radius: 200,
							startAngle: 270,
							endAngle: 0,
							step: 10
						}
		);
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
	describe('method "setStartAngle"', function () {
		it('should set a new start Angle of the Arc', function () {
			a.setStartAngle(250);
			expect(a.startAngle).toEqual(250);
		});
	});
	describe('method "getStartAngle"', function () {
		it('should return the start Angle of the Arc', function () {
			a.setStartAngle(200);
			expect(a.getStartAngle()).toEqual(200);
		});
	});
	describe('method "setEndAngle"', function () {
		it('should set a new end angle of the Arc', function () {
			a.setEndAngle(150);
			expect(a.endAngle).toEqual(150);
		});
	});
	describe('method "getEndAngle"', function () {
		it('should return the end angle of the Arc', function () {
			a.setEndAngle(100);
			expect(a.getEndAngle()).toEqual(100);
		});
	});
	describe('method "setRadius"', function () {
		it('should set the arc radius', function () {
			a.setRadius(500);
			expect(a.radius === 500).toBeTruthy();
		});
	});
	describe('method "getRadius"', function () {
		it('should return the arc radius', function () {
			a.setRadius(250);
			expect(a.getRadius() === 250).toBeTruthy();
		});
	});
	describe('method "setStep"', function () {
		it('should set the step to draw the arc', function () {
			a.setStep(20);
			expect(a.step).toEqual(20);
		});
	});
	describe('method "getStep"', function () {
		it('should return the step of the arc', function () {
			a.setStep(10);
			expect(a.getStep()).toEqual(10);
		});
	});
});