//***********************other class******************************************************//
describe('jCore.Router', function () {
	var a;
	describe('method "createRoute"', function () {
		it('should create a route(abstract method)', function () {
			a = new jCore.Router();
			expect(a.createRoute()).toBeTruthy();
		});
	});
});