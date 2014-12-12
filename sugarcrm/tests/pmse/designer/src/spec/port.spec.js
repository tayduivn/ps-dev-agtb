//***********************other class******************************************************//
describe('jCore.Port', function () {
	var a, b, c, s, r;
	beforeEach(function () {
		a = new jCore.Port();
		document.body.appendChild(a.getHTML());
	});
	afterEach(function () {
		$(a.getHTML()).remove();
		a = null;
	});
	describe('method "createHTML"', function () {
		it('should create a new HTML element', function () {
			var h = a.createHTML();
			expect(h).toBeDefined();
            expect(h.tagName).toBeDefined();
            expect(h.nodeType).toBeDefined();
            expect(h.nodeType).toEqual(document.ELEMENT_NODE);
		});
	})
	describe('method "applyBorderMargin"', function () {
		it('should apply Border Margin', function () {
			c = new jCore.CustomShape();
			b = new jCore.Port({
     			width: 8,
     			height: 8,
     			x: 3,
     			y: 4,
     			visible: true,
     			parent: c
 			});
			b.setDirection(2);
		});
	});
	describe('method "setX"', function () {
		it('should set the port x coordinate', function () {
			c = new jCore.Canvas();
			a.setCanvas(c);
			expect(a.x).toBeDefined();
			expect(a.x).not.toBeNull();
			expect(a.x === 0).toBeTruthy();
			a.setX(5);
			expect(a.x === 5).toBeTruthy();
			expect(a.zoomX === 5).toBeTruthy();
			expect(a.canvas.zoomFactor === 1).toBeTruthy();
			expect(a.realX === 5).toBeTruthy();
			expect(a.absoluteX === 5).toBeTruthy();
			expect(a.style.cssProperties.left === 5).toBeTruthy();
		});
	});
	describe('method "setY"', function () {
		it('should set the port y coordinate', function () {
			c = new jCore.Canvas();
			a.setCanvas(c);
			expect(a.y).toBeDefined();
			expect(a.y).not.toBeNull();
			expect(a.y === 0).toBeTruthy();
			a.setY(4);
			expect(a.y === 4).toBeTruthy();
			expect(a.zoomY === 4).toBeTruthy();
			expect(a.canvas.zoomFactor === 1).toBeTruthy();
			expect(a.realY === 4).toBeTruthy();
			expect(a.absoluteY === 4).toBeTruthy();
			expect(a.style.cssProperties.top === 4).toBeTruthy();
		});
	});
	describe('method "setWidth"', function () {
		it('should set the width of the port', function () {
			expect(a.width).toBeDefined();
			expect(a.width).not.toBeNull();
			expect(a.width === 4).toBeTruthy();
			a.setWidth(8);
			expect(a.width === 8).toBeTruthy();
			expect(a.zoomWidth === 8).toBeTruthy();
			expect(a.style.cssProperties.width === 8).toBeTruthy();
		});
	});
	describe('method "setHeight"', function () {
		it('should set the height of the port', function () {
			expect(a.height).toBeDefined();
			expect(a.height).not.toBeNull();
			expect(a.height === 4).toBeTruthy();
			a.setHeight(10);
			expect(a.height === 10).toBeTruthy();
			expect(a.zoomHeight === 10).toBeTruthy();
			expect(a.style.cssProperties.height === 10).toBeTruthy();
		});
	});
	describe('method "setDirection"', function () {
		it('should set the direction to the port', function () {
			expect(a.direction).toBeNull();
			a.setDirection(1);
			expect(a.direction === 1).toBeTruthy();
			a.setDirection(3);
			expect(a.direction === 3).toBeTruthy();
		});
	});
	describe('method "getDirection"', function () {
		it('should get the direction to the port', function () {
			a.setDirection(1);
			expect(a.getDirection() === 1).toBeTruthy();
			a.setDirection(3);
			expect(a.getDirection() === 3).toBeTruthy();
		});
	});
	describe('method "setParent"', function () {
		it('should set the parent to the port', function () {
			s = new jCore.Shape();
			expect(a.parent).toBeNull();
			a.setParent(s);
			expect(a.parent === s).toBeTruthy();
			expect(a.parent.type === "Shape").toBeTruthy();
			expect(a.parent.family === "Shape").toBeTruthy();
		});
	});
	describe('method "getParent"', function () {
		it('should get the parent of the port', function () {
			s = new jCore.Shape();
			expect(a.parent).toBeNull();
			a.setParent(s);
			expect(a.getParent() === s).toBeTruthy();
		});
	});
	describe('method "getOldParent"', function () {
		it('should get the old parent of the port', function () {
			s = new jCore.Shape();
			a.setParent(s);
			expect(a.parent === s).toBeTruthy();
			expect(a.getOldParent()).toBeNull();
		});
	});/*
	describe('method "setConnection"', function () {
		it('should set the connection associated with this port', function () {
			x = new jCore.Connection();
			expect(a.connection).toBeNull();
			a.setConnection(c);
			expect(a.connection === c).toBeTruthy();
		});
	});*/
	describe('method "setRepresentation"', function () {
		it('should set a representation of a port', function () {
			r = new jCore.RegularShape();
			a.setRepresentation(r);
			expect(a.representation === r).toBeTruthy();
		});
	});
	describe('method "getRepresentation"', function () {
		it('should get the representation of a port', function () {
			r = new jCore.RegularShape();
			a.setRepresentation(r);
			expect(a.getRepresentation() === r).toBeTruthy();
		});
	});
	describe('method "getPoint"', function () {
		it('should get the port position', function () {
			s = new jCore.Shape();
			a.setParent(s);
			expect(a.parent === s).toBeTruthy();
			expect(a.getPoint(true).x === 2).toBeTruthy();
			expect(a.getPoint(true).y === 2).toBeTruthy();
			expect(a.getPoint(true).type === "Point").toBeTruthy();
		});
	});
	describe('method "getPercentage"', function () {
		it('should get the percentage of a port relative to its parent', function () {
			expect(a.getPercentage()).toBeNull();
		});
	});
	describe('method "stringify"', function () {
		it('should serialize the port', function () {
			s = new jCore.Shape();
			a.setParent(s);
			expect(a.stringify().x).not.toBeNull();
			expect(a.stringify().x).toBeDefined();
			expect(a.stringify().y).not.toBeNull();
			expect(a.stringify().y).toBeDefined();
			expect(a.stringify().realX).not.toBeNull();
			expect(a.stringify().realY).toBeDefined();
			expect(a.stringify().parent).not.toBeNull();
			expect(a.stringify().parent).toBeDefined();
		});
	});
});