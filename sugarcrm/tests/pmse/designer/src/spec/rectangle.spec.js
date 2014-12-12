//***********************other class******************************************************//
describe('jCore.Rectangle', function () {
	var rectangle;
	beforeEach(function () {
		rectangle = new jCore.Rectangle();
		document.body.appendChild(rectangle.getHTML());
	});
	afterEach(function () {
		$(rectangle.getHTML()).remove();
		rectangle = null;
	});
	describe('method "createHTML"', function () {
		it('should create a new HTML element', function () {
			var h = rectangle.createHTML();
			expect(h).toBeDefined();
            expect(h.tagName).toBeDefined();
            expect(h.nodeType).toBeDefined();
            expect(h.nodeType).toEqual(document.ELEMENT_NODE);
		});
	})
	describe('method "paint()"', function () {
		it('should paints the rectangle applying the predefined style and adding a background color', function () {
			rectangle.paint();
			expect(rectangle.style.cssProperties.backgroundColor == "rgba(0,0,0,1)").toBeTruthy();
		});
	})
});