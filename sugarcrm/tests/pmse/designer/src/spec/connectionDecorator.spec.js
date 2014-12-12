//***********************other class******************************************************//
describe('jCore.ConnectionDecorator', function () {
	var a, b, customShape, customShape2, port, anotherPort, connection;
	beforeEach(function () {
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
        a = new jCore.ConnectionDecorator({
               decoratorPrefix:  'con',
               decoratorType:  'source',
               style:  {
                   cssClasses:  [],
                   cssProperties:  {}
               },
               parent:  connection,
               separator: "-",
               sprite: "sprite1"
        });
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
	describe('method "applyZoom"', function () {
		it('should refresh the dimension and position of the decorator', function () {
			expect(function () {a.applyZoom()}).not.toThrow();
			expect(a.getDimension().width === a.width).toBeTruthy();
			expect(a.getDimension().height === a.height).toBeTruthy();
		});
	});
	describe('method "getDecoratorType"', function () {
		it('should return the decorator type', function () {
			expect(typeof a.getDecoratorType() === "string").toBeTruthy();
			expect(a.getDecoratorType() === "source").toBeTruthy();
		});
	});
	describe('method "setDecoratorType"', function () {
		it('should set the decorator type', function () {
			expect(a.getDecoratorType() === "source").toBeTruthy();
			expect(function () {a.setDecoratorType("target")}).not.toThrow();
			expect(a.getDecoratorType() === "target").toBeTruthy();
		});
	});
	describe('method "getDecoratorPrefix"', function () {
		it('should return the decorator Prefix', function () {
			expect(typeof a.getDecoratorPrefix() === "string").toBeTruthy();
			expect(a.getDecoratorPrefix() === "con").toBeTruthy();
		});
	});
	describe('method "setDecoratorPrefix"', function () {
		it('should set the decorator Prefix', function () {
			expect(a.getDecoratorPrefix() === "con").toBeTruthy();
			expect(function () {a.setDecoratorPrefix("dec")}).not.toThrow();
			expect(a.getDecoratorPrefix() === "dec").toBeTruthy();
		});
	});
	describe('method "setParent"', function () {
		it('should set a new parent for the decorator connection', function () {
			newconnection = new jCore.Connection({
               srcPort: new jCore.Port(),
               destPort: new jCore.Port(),
               segmentColor: new PMUI.util.Color(10, 20, 30),
               segmentStyle: "regular"
        	});
        	expect(function () {a.setParent(newconnection)}).not.toThrow();
        	expect(a.parent === newconnection).toBeTruthy();
		});
	});
	describe('method "getParent"', function () {
		it('should return the decorator connection parent', function () {
			newconnection = new jCore.Connection({
               srcPort: new jCore.Port(),
               destPort: new jCore.Port(),
               segmentColor: new PMUI.util.Color(10, 20, 30)
        	});
        	a.setParent(newconnection);
        	expect(a.getParent() === newconnection).toBeTruthy();
		});
	});
	describe('method "setSeparator"', function () {
		it('should set a new saparator for decorator connection', function () {
			expect(a.separator === "-").toBeTruthy();
			expect(function () {a.setSeparator("_")}).not.toThrow();
			expect(a.separator === "_").toBeTruthy();
		});
	});
	describe('method "setSprite"', function () {
		it('should set the sprite for decorator connection', function () {
			expect(a.sprite === "sprite1").toBeTruthy();
			expect(function () {a.setSprite("bpmn_zoom")}).not.toThrow();
			expect(a.sprite === "bpmn_zoom").toBeTruthy();
		});
	});
	describe('method "setCssClass"', function () {
		it('should set the css class for decorator connection', function () {
			expect(function () {a.setCssClass("new_css_class")}).not.toThrow();
			expect(a.cssClass === "new_css_class").toBeTruthy();
		});
	});
	describe('method "getCssClass"', function () {
		it('should return the css class for decorator connection', function () {
			a.setCssClass("new_css_class");
			expect(a.getCssClass() === "new_css_class").toBeTruthy();
		});
	});
});