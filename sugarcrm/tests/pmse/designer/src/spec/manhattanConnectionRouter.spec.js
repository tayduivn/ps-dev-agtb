//***********************other class******************************************************//
describe('jCore.ManhattanConnectionRouter', function () {
	var a, b, customShape, customShape2, port, anotherPort, connection;
	beforeEach(function () {
		a = new jCore.ManhattanConnectionRouter();
		document.body.appendChild(a.getHTML());
	});
	afterEach(function () {
		$(a.getHTML()).remove();
		a = null;
	});
	describe('method "createRoute"', function () {
		it('should create the connection points', function () {
			customShape = new jCore.CustomShape();
			customShape2 = new jCore.CustomShape();
			port = new jCore.Port({
               width: 8,
               height: 8,
               visible: true,
               parent: customShape
           	});
			anotherPort = new jCore.Port({
               width: 8,
               height: 8,
               visible: true,
               parent: customShape2
           	});
			connection = new jCore.Connection({
               srcPort: port,
               destPort: anotherPort,
               segmentColor: new jCore.Color(0, 200, 0),
               segmentStyle: "regular"
           	});
           	expect(function () {a.createRoute(connection)}).not.toThrow();
           	expect(typeof a.createRoute(connection) === "object").toBeTruthy();
           	expect(a.createRoute(connection)[0].type === "Point").toBeTruthy();
           	expect(typeof a.createRoute(connection)[0].x === "number").toBeTruthy();
           	expect(typeof a.createRoute(connection)[0].y === "number").toBeTruthy();
		});
	});
});