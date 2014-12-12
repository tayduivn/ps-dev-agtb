//***********************other class******************************************************//
describe('jCore.Snapper', function () {
	var a, b, c;
	beforeEach(function() {
		a = new jCore.Snapper();
	});
	describe('method "createHTML"', function () {
		it('should create a new HTML element', function () {
			var h = a.createHTML();
            expect(h).toBeDefined();
            expect(h.tagName).toBeDefined();
            expect(h.nodeType).toBeDefined();
            expect(h.nodeType).toEqual(document.ELEMENT_NODE);
		});
	});
	describe('method "show"', function () {
		it('should show the snapper', function () {
			expect(a.visible).toBeFalsy();
			a.show();
			expect(a.visible).toBeTruthy();
		});
	});
	describe('method "hide"', function () {
		it('should hide the snapper', function () {
			expect(a.visible).toBeFalsy();
			a.show();
			expect(a.visible).toBeTruthy();
			a.hide();
			expect(a.visible).toBeFalsy();
		});
	});
	describe('method "binarySearch"', function () {
		it('should make a search in the data', function () {
			a.data = [1,4,7,8];
			expect(a.binarySearch(4) === 4).toBeTruthy();
			expect(a.binarySearch(7) === 7).toBeTruthy();
			expect(a.binarySearch(5) === 5).toBeFalsy();
		});
	});
	describe('method "setOrientation"', function () {
		it('should set the orientation of the snapper', function () {
			expect(a.orientation === "horizontal").toBeTruthy();
			a.setOrientation("vertical");
			expect(a.orientation === "vertical").toBeTruthy();

		});
	});
	describe('method "getOrientation"', function () {
		it('should get the orientation of the snapper', function () {
			expect(a.getOrientation() === "horizontal").toBeTruthy();
			a.setOrientation("vertical");
			expect(a.getOrientation() === "vertical").toBeTruthy();	
		});
	});
});