//***********************other class******************************************************//
describe('jCore.Canvas', function () {
	var a, b, c;
	beforeEach(function () {
		a = new jCore.Canvas({
									width: 400,
									height: 400,
									readOnly: false				
		});
		document.body.appendChild(a.getHTML());
	});
	afterEach(function () {
		$(a.getHTML()).remove();
		a = null;
		b = null;
		c = null;
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
	describe('method "setReadOnly"', function () {
		it('should set the read and write permissions of the canvas', function () {
			expect(a.readOnly).toBeFalsy();
			a.setReadOnly(true);
			expect(a.readOnly).toBeTruthy();
			a.setReadOnly(false);
			expect(a.readOnly).toBeFalsy();
		});
	});
	describe('method "setToReadOnly"', function () {
		it('should set the canvas to read only mode', function () {
			a.createHTML();
			a.setToReadOnly();
			expect(a.readOnlyLayer.html).toBeDefined();
			expect(a.html.appendChild(a.readOnlyLayer.html)).toBeDefined();
			expect(a.readOnly).toBeTruthy();
		});
	});
	describe('method "unsetReadOnly"', function () {
		it('should set the canvas to read and write mode', function () {
			a.createHTML();
			a.setToReadOnly();
			expect(a.html.childElementCount === 0).toBeFalsy();
			expect(a.readOnly).toBeTruthy();
			a.unsetReadOnly();
			expect(a.readOnlyLayer.getHTML()).toBeDefined();
			expect(a.readOnly).toBeFalsy();
			//expect(a.html.childElementCount === 0).toBeTruthy();
		});
	});
	describe('method "setPosition"', function () {
		it('should set the position of the canvas', function () {
			expect(a.x).toBeDefined();
			expect(a.x === 0).toBeTruthy();
			expect(a.y).toBeDefined();
			expect(a.y === 0).toBeTruthy();
			a.setPosition(30,40);
			expect(a.x === 30).toBeTruthy();
			expect(a.y === 40).toBeTruthy();
		});
	});
	describe('method "setX"', function () {
		it('should set the x coordinate of the canvas, zoomX and absoluteX', function () {
			expect(a.x).toBeDefined();
			expect(a.x === 0).toBeTruthy();
			a.setX(30);
			expect(a.zoomX === 30).toBeTruthy();
			expect(a.x === 30).toBeTruthy();
			expect(a.absoluteX === 0).toBeTruthy();
		});
	});
	describe('method "setY"', function () {
		it('should set the y coordinate of the canvas, zoomY and absolutey', function () {
			expect(a.y).toBeDefined();
			expect(a.y === 0).toBeTruthy();
			a.setY(40);
			expect(a.zoomY === 40).toBeTruthy();
			expect(a.y === 40).toBeTruthy();
			expect(a.absoluteY === 0).toBeTruthy();
		});
	});
	describe('method "createHTMLDiv"', function () {
		it('should retrieve the div element has this canvas id', function () {
			a.createHTMLDiv();
			expect(document.getElementsByTagName("div")).not.toBeNull();
		});
	});
	describe('method "hideCurrentConnection"', function () {
		it('should hide current connection if there is one', function () {
			a.hideCurrentConnection();
			expect(a.currentConnection).toBeNull();
		});
	});
	describe('method "addConnection"', function () {
		it('should add a connection to the canvas object', function () {
			expect(a.connections.asArray().length).toEqual(0);			
			var con = new jCore.Connection();
			expect(function () {a.addConnection("any_string")}).toThrow();			
			expect(function () {a.addConnection(con)}).not.toThrow();
			expect(a.connections.asArray().length === 1).toBeTruthy();
		});
	});
	describe('method "addToList"', function () {
		it('should identify the shape family and add into a list', function () {
			expect(a.customShapes.asArray().length === 0).toBeTruthy();
			expect(a.regularShapes.asArray().length === 0).toBeTruthy();
			b = new jCore.CustomShape();
			c = new jCore.RegularShape();
			expect(function () {a.addToList(b)}).not.toThrow();
			expect(a.customShapes.asArray().length === 1).toBeTruthy();
			expect(function () {a.addToList(c)}).not.toThrow();
			//expect(a.regularShapes.asArray().length === 1).toBeTruthy();
		});
	});
	describe('method "applyZoom"', function () {
		it('should apply a zoom scale to the canvas and all its components', function () {
			var scale = 4; 
			b = new jCore.CustomShape();
			c = new jCore.CustomShape();
			a.addToList(b);
			a.addToList(c);
			expect(a.zoomFactor).toEqual(1);
			expect(a.customShapes.asArray().length === 2).toBeTruthy();
			//a.applyZoom(scale);
			//expect(a.zoomPropertiesIndex === scale-1).toBeTruthy();
			//expect(a.zoomFactor).not.toEqual(1);
		});
	});/*
	describe('method "moveAllChildConnections"', function () {
		it('should move all connections of the children', function () {
			
		});
	});*/
	describe('method "getRelativeX"', function () {
		it('should return the value of add absolute and x', function () {
			f = 34;
			a.setX(f);
			expect(a.getRelativeX() === 34).toBeTruthy();
		});
	});
	describe('method "getRelativeY"', function () {
		it('should return the value of add absolute and y', function () {
			f = 34;
			a.setY(f);
			expect(a.getRelativeY() === 34).toBeTruthy();
		});
	});
	describe('method "removeFromList"', function () {
		it('should remove a shape from the list', function () {
			b = new jCore.CustomShape();
			c = new jCore.CustomShape();
			a.addToList(b);
			a.addToList(c);
			expect(a.customShapes.asArray().length === 2).toBeTruthy();
			expect(function () {a.removeFromList(b)}).not.toThrow();
			expect(a.customShapes.asArray().length === 1).toBeTruthy();
			expect(function () {a.removeFromList(c)}).not.toThrow();
			expect(a.customShapes.asArray().length === 0).toBeTruthy();
		});
	});
	describe('method "removeConnection"', function () {
		it('should remove a canvas connection', function () {
			expect(a.connections.asArray().length).toEqual(0);			
			var con = new jCore.Connection();
			expect(function () {a.addConnection(con)}).not.toThrow();
		});
	});
	describe('method "setTopScroll"', function () {
		it('should set the top scroll of the canvas', function () {
			expect(function () {a.setTopScroll(23)}).not.toThrow();
			expect(a.topScroll === 23).toBeTruthy();
		});
	});
	describe('method "setLeftScroll"', function () {
		it('should set the left scroll of the canvas', function () {
			expect(function () {a.setLeftScroll(23)}).not.toThrow();
			expect(a.leftScroll === 23).toBeTruthy();
		});
	});
	describe('method "setZoomFactor"', function () {
		it('should set the zoom factor of the canvas', function () {
			expect(function () {a.setZoomFactor(50)}).not.toThrow();
			expect(a.zoomFactor === 50);
		});
	});
	describe('method "setCurrentConnection"', function () {
		it('should set the currentConnection of the canvas', function () {
			f = new jCore.Connection();
			expect(function () {a.setCurrentConnection(f)}).not.toThrow();
			expect(a.currentConnection === f).toBeTruthy();
		});
	});
	describe('method "getZoomFactor"', function () {
		it('should return the zoom factor', function () {
			expect(function () {a.setZoomFactor(50)}).not.toThrow();
			expect(a.zoomFactor === 50);
			expect(a.getZoomFactor() === 50).toBeTruthy();
		});
	});
	describe('method "getZoomPropertiesIndex"', function () {
		it('should return the zoom properties index', function () {
			expect(a.getZoomPropertiesIndex() === 2).toBeTruthy();
		});
	});
	describe('method "getLeftScroll"', function () {
		it('should get the left scroll of the canvas', function () {
			expect(function () {a.setLeftScroll(23)}).not.toThrow();
			expect(a.getLeftScroll() === 23).toBeTruthy();
		});
	});
	describe('method "getTopScroll"', function () {
		it('should get the top scroll of the canvas', function () {
			expect(function () {a.setTopScroll(23)}).not.toThrow();
			expect(a.getTopScroll() === 23).toBeTruthy();
		});
	});
	describe('method "getCurrentConnection"', function () {
		it('should get the currentConnection of the canvas', function () {
			f = new jCore.Connection();
			expect(function () {a.setCurrentConnection(f)}).not.toThrow();
			expect(a.getCurrentConnection() === f).toBeTruthy();
		});
	});
	describe('method "getConnections"', function () {
		it('should get the connections', function () {
			f = new jCore.Connection();
			expect(a.getConnections().asArray().length === 0).toBeTruthy();
			a.addConnection(f);
			expect(a.getConnections().asArray().length === 1).toBeTruthy();
		});
	});
	describe('method "getCustomShapes"', function () {
		it('should get the custom shapes added in canvas list', function () {
			b = new jCore.CustomShape();
			c = new jCore.CustomShape();
			a.addToList(b);
			a.addToList(c);
			expect(a.getCustomShapes().asArray()[0] === b).toBeTruthy();
			expect(a.getCustomShapes().asArray()[1] === c).toBeTruthy();
		});
	});
	describe('method "getRegularShapes"', function () {
		it('should get the regular shapes added in canvas list', function () {
			b = new jCore.RegularShape();
			a.addToList(b);
			expect(a.getRegularShapes().asArray()[0] === b).toBeTruthy();
		});
	});
	describe('method "getMultipleSelectionHelper"', function () {
		it('should get the multiple selection Container', function () {
			expect(a.getMultipleSelectionHelper()).toBeDefined();
			expect(a.getMultipleSelectionHelper()).not.toBeNull();
			expect(a.getMultipleSelectionHelper().canvas.id === a.id).toBeTruthy();
		});
	});
	describe('method "getHorizontalSnapper"', function () {
		it('should get the horizontal snapper', function () {
			expect(a.getHorizontalSnapper()).toBeDefined();
			expect(a.getHorizontalSnapper()).not.toBeNull();
			expect(a.getHorizontalSnapper().canvas.id === a.id).toBeTruthy();
		});
	});
	describe('method "getVerticalSnapper"', function () {
		it('should get the vertical Snapper', function () {
			expect(a.getVerticalSnapper()).toBeDefined();
			expect(a.getVerticalSnapper()).not.toBeNull();
			expect(a.getVerticalSnapper().canvas.id === a.id).toBeTruthy();
		});
	});
	describe('method "isResizable"', function () {
		it('should return always false', function () {
			expect(a.isResizable()).toBeFalsy();
		});
	});
	describe('method "getCanvas"', function () {
		it('should get the canvas itself', function () {
			f = a.id;
			expect(a.getCanvas().id === f).toBeTruthy();
		});
	});
	describe('method "shapeFactory"', function () {
		it('should Default shape factory for creating shapes.', function () {
			var canvas = new jCore.Canvas();
			document.body.appendChild(canvas.getHTML());
			canvas.setWidth(600);
			canvas.setHeight(600);
			//canvas.shapeFactory();
		});
	});
	describe('method "connectionFactory"', function () {
		it('should factory to create connections', function () {
			var canvas = new jCore.Canvas();
			document.body.appendChild(canvas.getHTML());
			canvas.setWidth(600);
			canvas.setHeight(600);
			expect(canvas.connectionFactory() instanceof jCore.Connection).toBeTruthy();
		});
	});	
});
       