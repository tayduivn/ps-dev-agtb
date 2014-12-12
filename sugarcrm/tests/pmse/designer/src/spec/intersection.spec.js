//***********************other class******************************************************//
describe('jCore.Intersection', function () {
	var a, b, p, c, s;
	beforeEach(function () {
		p = new jCore.Point(10,10);
		c = new jCore.Connection();
		s = new jCore.Segment()
		a = new jCore.Intersection(p, c.id, s);
		document.body.appendChild(a.getHTML());
	});
	afterEach(function () {
		$(a.getHTML()).remove();
		a = null;
	});
	describe('method "destroy"', function () {
		it('should destroy the Intersection by removing its html', function () {
			b = $(document.body).find('div[class="pmui-intersection"]').length;
			expect($(document.body).find('div[class="pmui-intersection"]').length).toEqual(b);
			a.destroy();
			expect($(document.body).find('div[class="pmui-intersection"]').length).not.toEqual(b);
			expect($(document.body).find('div[class="pmui-intersection"]').length).toEqual(b-1);
		});
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
});