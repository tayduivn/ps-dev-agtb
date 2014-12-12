//***********************other class******************************************************//
describe('jCore.Shape', function () {
	var shape, shapeb, shapec, canvas, c, x, y, i,a;
	beforeEach(function () {
		shape = new jCore.Shape();
		shapeb = new jCore.Shape();
    	shapec = new jCore.Shape();
		//canvas = new jCore.Canvas();
		//document.body.appendChild(canvas.getHTML());
		//canvas.setWidth(400);
		//canvas.setHeight(400);
		//canvas.html.style.zIndex = 10;
		document.body.appendChild(shape.getHTML());
		shape.setWidth(400);
		shape.setHeight(400);
		shape.html.style.zIndex = 30;
		document.body.appendChild(shapeb.getHTML());
		shapeb.setWidth(200);
		shapeb.setHeight(200);
		shapeb.html.style.zIndex = 20;
		shapeb.setParent(shape);
		//shape.setParent(canvas);
	});
	afterEach(function () {
		$(shape.getHTML()).remove();
		shape = null;
	});
	describe('method "createHandlers"', function () {
		it('should create handlers according to the number of handlers', function () {
			x = {'background-color': "rgb(0, 255, 0)", 'border': '1px solid black'};
			y = {'background-color': "white", 'border': '1px solid black'};
			shape.createHandlers("Rectangle", 4, x, y);
			for(i = 0; i < shape.cornerResizeHandlers.getSize(); i = i +1)
			{
				expect(shape.cornerResizeHandlers.get(i).parent === shape).toBeTruthy();
				expect(shape.cornerResizeHandlers.get(i).zOrder === 103).toBeTruthy();
				expect(shape.cornerResizeHandlers.get(i).representation.type === "Rectangle").toBeTruthy();
				expect(shape.cornerResizeHandlers.get(i).resizableStyle).toBeDefined();
				expect(shape.cornerResizeHandlers.get(i).nonResizableStyle).toBeDefined();
			}
			for(i = 0; i < shape.midResizeHandlers.getSize(); i = i +1)
			{
				expect(shape.midResizeHandlers.get(i).parent === shape).toBeTruthy();
				expect(shape.midResizeHandlers.get(i).zOrder === 103).toBeTruthy();
				expect(shape.midResizeHandlers.get(i).representation.type === "Rectangle").toBeTruthy();
				expect(shape.midResizeHandlers.get(i).resizableStyle).toBeDefined();
				expect(shape.midResizeHandlers.get(i).nonResizableStyle).toBeDefined();
			}
		});
	});
	describe('method "updateHandlers"', function () {
		it('should update the position of the handlers', function () {
			for(i = 0; i < shape.cornerResizeHandlers.getSize(); i = i +1)
			{
				var h = shape.cornerResizeHandlers.get(i);
				expect(h).toBeDefined();
				expect(h).not.toBeNull();
			}
			for(i = 0; i < shape.midResizeHandlers.getSize(); i = i +1)
			{
				var h = shape.midResizeHandlers.datedElementget(i);
				expect(h).toBeDefined();
				expect(h).not.toBeNull();
			}
		});
	});
	describe('method "showOrHideResizeHandlers"', function () {
		it('should set the visibility of the resize handler', function () {
			x = {'background-color': "rgb(0, 255, 0)", 'border': '1px solid black'};
			y = {'background-color': "white", 'border': '1px solid black'};
			shape.createHandlers("Rectangle", 4, x, y);
			expect(shape.cornerResizeHandlers.get(0).visible).toBeFalsy();
			expect(shape.cornerResizeHandlers.get(2).visible).toBeFalsy();
			shape.showOrHideResizeHandlers(true);
			expect(shape.cornerResizeHandlers.get(0).visible).toBeTruthy();
			expect(shape.cornerResizeHandlers.get(1).visible).toBeTruthy();
			expect(shape.cornerResizeHandlers.get(2).visible).toBeTruthy();
			expect(shape.cornerResizeHandlers.get(3).visible).toBeTruthy();
		});
	});
	describe('method "createHTML"', function () {
		it('should create a new HTML element', function () {
			var html = shape.createHTML();
            expect(html).toBeDefined();
            expect(html.tagName).toBeDefined();
            expect(html.nodeType).toBeDefined();
            expect(html.nodeType).toEqual(document.ELEMENT_NODE);
		});
	});
	describe('method "isDraggable"', function () {
		it('should return a boolean value if the object is draggable', function () {
			expect(shape.isDraggable()).toBeFalsy();
			shape.drag = true;
			expect(shape.isDraggable()).toBeTruthy();
		});
	});
	describe('method "setCenter"', function () {
		it('should sets the center point of the shape', function () {
			var point = new jCore.Point(100,100);
			shape.setCenter(point);
			expect(shape.center.x == 100).toBeTruthy();
			expect(shape.center.y == 100).toBeTruthy();
			expect(function(){
				shape.setCenter(100);
				}).toThrow();
			expect(function(){
				shape.setCenter("100");
				}).toThrow();
			expect(function(){
				shape.setCenter({x:100,y:100});
				}).toThrow();
			expect(function(){
				shape.setCenter([100,100]);
				}).toThrow();
			});
	});	
	describe('method "getCenter"', function () {
		it('should gets the center point of the shape', function () {
			var point = new jCore.Point(100,100);
			shape.setCenter(point);
			expect(shape.getCenter() instanceof jCore.Point).toBeTruthy();
		    expect(shape.getCenter().x == 100).toBeTruthy();
		    expect(shape.getCenter().y == 100).toBeTruthy();	    
	    });
	});
	describe('method "setParent"', function () {
		it('should sets the parent of a shape', function () {
			shape.setParent(shapeb,true);
			expect(shape.parent == shapeb).toBeTruthy();			
			expect(shape.parent instanceof jCore.Shape || shape.parent instanceof jCore.Canvas).toBeTruthy();
		});
	});
	describe('method "getParent"', function () {
		it('should gets the parent of a shape', function () {
			expect(shape.getParent() == shape.parent).toBeTruthy();
		//	expect(shape.parent instanceof jCore.Shape || shape.parent instanceof jCore.Canvas).toBeTruthy();
		});
	});
	describe('method "setOldParent"', function () {
		it('should sets the parent of a shape', function () {
			shape.setOldParent(shapeb);
			expect(shape.oldParent == shapeb).toBeTruthy();			
			//expect(shape.parent instanceof jCore.Shape || shape.parent instanceof jCore.Canvas).toBeTruthy();
		});
	});
	describe('method "getOldParent"', function () {
		it('should gets the old parent of a shape', function () {
			expect(shape.getOldParent() == shape.oldParent).toBeTruthy();
		});
	});
	describe('method "refreshShape"', function () {
		it('should refreshs the position and dimension of the shape', function () {
			this.canvas = shapeb;
			shape.x =100;
			shape.y =100;
			shape.width = 100;
			shape.height = 100;			
			shape.refreshShape();
			expect(shape.x==100).toBeTruthy();
			expect(shape.y==100).toBeTruthy();
			if(shape.canvas)
				expect(shape.zoomX == shape.x * shape.canvas.zoomFactor).toBeTruthy();
			else
				expect(shape.zoomX == shape.x).toBeTruthy();

			expect(shape.width == 100).toBeTruthy();
			expect(shape.height == 100).toBeTruthy();
			if (this.canvas)
			{
				if(Math.floor(this.width * this.canvas.zoomFactor)%2 ===0)
					expect(Math.floor(this.width * this.canvas.zoomFactor) == shape.zoomWidth).toBeTruthy();
				else
					expect(shape.zoomWidth = shape.width).toBeTruthy();
        	}		
		});
	});
	describe('method "isResizable"', function () {
		it('Should verify if the shape is resizable or not', function () {			
			expect(!shape.isResizable()).toBeTruthy();
			shape.resize.type = "ResizeBehavior";
			expect(shape.isResizable()).toBeTruthy();
		});
	});
	describe('method "setResizeBehavior"', function () {
		it('Should sets the determined resize behavior to this', function () {			
			var aux = shape.resize;
			shape.setResizeBehavior("drag");
			expect(aux==shape.resize).toBeFalsy();			
		});
	});
	describe('method "setFixed"', function () {
		it('Should sets property fixed', function () {			
			shape.setFixed(true);
			expect(shape.fixed).toBeTruthy();
			shape.setFixed(false);
			expect(shape.fixed).toBeFalsy();					
		});
	});
	describe('method "changePosition"', function () {
		it('Should sets a new position', function () {			
			shape.canvas = canvas;
			shape.changePosition(100,100,200,200);
			expect(shape.canvas.updatedElement.fields[0].oldVal == 100).toBeTruthy();
			expect(shape.canvas.updatedElement.fields[1].oldVal == 100).toBeTruthy();
			expect(shape.canvas.updatedElement.fields[2].oldVal == 200).toBeTruthy();
	        expect(shape.canvas.updatedElement.fields[3].oldVal == 200).toBeTruthy();										
		});
	});
	describe('method "changeSize"', function () {
		it('Should sets a new size', function () {
			shape.canvas = canvas;
			shape.changeSize(150,150);	
			expect(shape.canvas.updatedElement.fields[0].oldVal == 150).toBeTruthy();
			expect(shape.canvas.updatedElement.fields[1].oldVal == 150).toBeTruthy();
		});
	});
	describe('method "changeParent"', function () {
		it('Should sets a new parent for this shape', function () {
		    shape.canvas = canvas;			
		    shape.changeParent(100,150,200,250,shapec,shape.canvas);
			expect(shape.canvas.updatedElement.fields[0].oldVal == 100).toBeTruthy();
			expect(shape.canvas.updatedElement.fields[1].oldVal == 150).toBeTruthy();
			expect(shape.canvas.updatedElement.fields[2].oldVal == 200).toBeTruthy();
	        expect(shape.canvas.updatedElement.fields[3].oldVal == 250).toBeTruthy();	
	        expect(shape.canvas.updatedElement.fields[4].oldVal == shapec).toBeTruthy();	
		});
	});
	describe('method "setDimension"', function () {
		it('Should sets a dimension for this shape', function () {
		    shape.setDimension(100,200);
		    expect(shape.xCorners[0] == 0).toBeTruthy();
		    expect(shape.xCorners[1] == 100).toBeTruthy();
			expect(shape.xCorners[2] == 100).toBeTruthy();
			expect(shape.xCorners[3] == 0).toBeTruthy();
			expect(shape.yCorners[0] == 0).toBeTruthy();
		    expect(shape.yCorners[1] == 0).toBeTruthy();
			expect(shape.yCorners[2] == 200).toBeTruthy();
			expect(shape.yCorners[3] == 200).toBeTruthy();
			expect(shape.xMidPoints[0] == 50).toBeTruthy();
		    expect(shape.xMidPoints[1] == 100).toBeTruthy();
			expect(shape.xMidPoints[2] == 50).toBeTruthy();
			expect(shape.xMidPoints[3] == 0).toBeTruthy();
			expect(shape.yMidPoints[0] == 0).toBeTruthy();
		    expect(shape.yMidPoints[1] == 100).toBeTruthy();
			expect(shape.yMidPoints[2] == 200).toBeTruthy();
			expect(shape.yMidPoints[3] == 100).toBeTruthy();
		});
	});
	describe('method "getHandlesIDs"', function () {
		it('Should Gets the handles IDs used to initialize', function () {
			var res = shape.getHandlesIDs();
			var patron = /#[a-z]{1,2}[0-9A-Za-z]{32}resizehandler/;
			expect(res.n.search(patron)==0).toBeTruthy();
			expect(res.e.search(patron)==0).toBeTruthy();
			expect(res.s.search(patron)==0).toBeTruthy();
			expect(res.w.search(patron)==0).toBeTruthy();
			expect(res.ne.search(patron)==0).toBeTruthy();
			expect(res.nw.search(patron)==0).toBeTruthy();
			expect(res.se.search(patron)==0).toBeTruthy();
			expect(res.sw.search(patron)==0).toBeTruthy();			 
		});
	});

	describe('method "decreaseParentZIndex"', function () {
		it('Should Decreases the zIndex of shapes ancestors by one by one', function () {
			shapeb.decreaseParentZIndex(shape);
			expect(shape.html.style.zIndex == 29).toBeTruthy();
			expect(shapeb.html.style.zIndex == 20).toBeTruthy();			
		});
	});

	describe('method "increaseParentZIndex"', function () {
		it('Should Increases the z-index of shapes ancestors by one', function () {
			shapeb.increaseParentZIndex(shape);
			expect(shape.html.style.zIndex == 31).toBeTruthy();
			expect(shapeb.html.style.zIndex == 20).toBeTruthy();			
		});
	});



});