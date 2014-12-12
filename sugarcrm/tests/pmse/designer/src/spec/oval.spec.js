//***********************other class******************************************************//
describe('jCore.Oval', function () {
	var a;
	describe('method "createHTML"', function () {
		it('should create a new HTML element', function () {
			a = new jCore.Oval();
			var html = a.createHTML();
            expect(html).toBeDefined();
            expect(html.tagName).toBeDefined();
            expect(html.nodeType).toBeDefined();
            expect(html.nodeType).toEqual(document.ELEMENT_NODE);
		});
	});
});