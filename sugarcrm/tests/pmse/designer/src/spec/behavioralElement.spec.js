//***********************other class******************************************************//
describe('jCore.BehavioralElement', function () {
	var a, b, c, 
		dropBehaviors, initialDropBehavior = 'nodrop',
        containerBehaviors,
        initialContainerBehavior = 'nocontainer',
        defaultSettings = {
        	container: initialContainerBehavior,
        	drop: initialDropBehavior
		};
	beforeEach(function () {
		//the following two if evaluations  doesn't shohuld be there, the initialization of the dropBehaviors and 
		//containerBehaviors should be on the var statement.
		/*if(!dropBehaviors) {
			dropBehaviors = {
				"connectioncontainer": PMUI.behavior.ConnectionContainerDropBehavior,
	            "connection": PMUI.behavior.ConnectionDropBehavior,
	            "container": PMUI.behavior.ContainerDropBehavior,
	            "nodrop": PMUI.behavior.NoDropBehavior
	        };
		}
		if(!containerBehaviors) {
			containerBehaviors = {
	            "regularcontainer": PMUI.behavior.RegularContainerBehavior,
	            "nocontainer": PMUI.behavior.NoContainerBehavior
	        }	
		}*/
		a = new jCore.BehavioralElement(defaultSettings);
	});
	describe('constructor', function() {
		it('should be able to create a new instance without config options', function() {
			a = new jCore.BehavioralElement();
			expect(a instanceof jCore.BehavioralElement).toBeTruthy();
		});
		it('should be able to create a new instance using config options', function() {
			expect(a.container instanceof containerBehaviors[initialContainerBehavior]).toBeTruthy();
			expect(a.drop instanceof dropBehaviors[initialDropBehavior]).toBeTruthy();
		});
	});
	describe('method "setDropBehavior"', function () {
		it('should set the drop behavior for the element', function () {
			var key;
			for(key in dropBehaviors) {
				a.setDropBehavior(key);
				expect(a.drop instanceof dropBehaviors[key]).toBeTruthy();
			}
		});
	});
	describe('method "setContainerBehavior"', function () {
		it('should set the container behavior for the element', function () {
			var key;
			for(key in containerBehaviors) {
				a.setContainerBehavior(key);
				expect(a.container instanceof containerBehaviors[key]).toBeTruthy();
			}
		});
	});
	describe('method "updateDimensions"', function(){
		it('should update the dimensions and position of this shape', function(){
			var margin = 5;
			//WTF is suppose to do this method???.
			s = new jCore.Shape();
			s.updateDimensions(margin);
			expect(false).toHaveBeenCalled(true);
		});
	});
	describe('method "updateChildrenPosition"', function () {
		it('should update children position of a container', function () {
			var a = new jCore.Shape(), shapeA = new jCore.Shape(), shapeB = new jCore.Shape(), shapeAPosition = {
				x: 10,
				y: 20
			}, shapeBPosition = {
				x: 40,
				y: 30
			}, positionIncrement = {
				x: 8, 
				y: -2
			}, canvas = new jCore.Canvas({});
			a.setContainerBehavior("regularcontainer");

			a.setCanvas(canvas);
			a.setParent(canvas);
			a.addElement(shapeA, shapeAPosition.x, shapeAPosition.y, true);
			a.addElement(shapeB, shapeBPosition.x, shapeBPosition.y, true);
			a.updateChildrenPosition(positionIncrement.x, positionIncrement.y);
			expect(shapeA.getAbsoluteX()).toBe(shapeAPosition.x + positionIncrement.x);
			expect(shapeA.getY()).toBe(shapeAPosition.y + positionIncrement.y);
			expect(shapeB.getX()).toBe(shapeBPosition.x + positionIncrement.x);
			expect(shapeB.getY()).toBe(shapeBPosition.y + positionIncrement.y);
		});
	});
	describe('method "isContainer"', function() {
		it('should return true when it has the regular container as behavior', function() {
			a.setContainerBehavior('regularcontainer');
			expect(a.isContainer()).toBeTruthy();
		});
		it('should return true when it has the no container behavior', function() {
			a.setContainerBehavior('nocontainer');
			expect(a.isContainer()).toBeFalsy();
		});
	});
	describe('method "addElement"', function() {
		it('should add an element', function() {
			var a = new jCore.Shape(),
				shapeA = new jCore.Shape(), behavior, childrenNum, 
				canvas = new jCore.Canvas({});
			a.setContainerBehavior('regularcontainer');
			a.setCanvas(canvas);
			a.setParent(canvas);
			shapeA.setCanvas(canvas);
			shapeA.setParent(canvas);
			behavior = a.container;
			childrenNum = a.getChildren().getSize();
			a.addElement(shapeA, 0, 0, true);
			expect(a.getChildren().getSize()).toBe(childrenNum + 1);
			expect(a.getChildren().contains(shapeA)).toBeTruthy();
			document.body.appendChild(a.getHTML());
			expect(jQuery(a.getHTML()).find('#' + shapeA.getID()).length).toBe(1);
		});
	});
	describe('method "removeElement"', function() {
		it('should remove an element', function() {
			var shapeA = new jCore.Shape(), behavior;
			a.setContainerBehavior('regularcontainer');
			behavior = a.container;
			spyOn(behavior, "removeFromContainer");
			a.removeElement(shapeA);
			expect(behavior.removeFromContainer).toHaveBeenCalled();
			expect(behavior.removeFromContainer.mostRecentCall.args[0]).toBe(shapeA);
		});
	});
	describe('method "swapElementContainer"', function() {
		it('should swaps shape from this container to a different one', function() {
			var a = new jCore.Shape(), shapeA = new jCore.Shape(), 
                canvas = new jCore.Canvas({}),
                containerB = new jCore.Shape({
                    container: 'regularcontainer',
                }), x = 6, y = 3, childrenNum, childrenNum2;
            a.setContainerBehavior('regularcontainer');
            a.setCanvas(canvas);
            a.setParent(canvas);
            containerB.setCanvas(canvas);
            containerB.setParent(canvas);
			a.addElement(shapeA, 0, 0, true);
			childrenNum2 = containerB.getChildren().getSize();
			childrenNum = a.getChildren().getSize();
			a.swapElementContainer(shapeA, containerB, x, y, true);
			expect(a.getChildren().getSize()).toBe(childrenNum - 1);
			expect(a.getChildren().contains(shapeA)).toBeFalsy();
			expect(containerB.getChildren().getSize()).toBe(childrenNum2 + 1);
			expect(containerB.getChildren().contains(shapeA)).toBeTruthy();
			document.body.appendChild(a.getHTML());
			document.body.appendChild(containerB.getHTML());
			expect(jQuery(a.getHTML()).find('#'+shapeA.getID()).length).toBe(0);
			expect(jQuery(containerB.getHTML()).find('#'+shapeA.getID()).length).toBe(1);
			expect(shapeA.getX()).toBe(x);
			expect(shapeA.getY()).toBe(y);
			expect(parseInt(shapeA.getHTML().style.left)).toBe(x);
			expect(parseInt(shapeA.getHTML().style.top)).toBe(y);
		});

		it('should swaps shape from this container to a different one (without specify x and y)', function() {
			var a = new jCore.Shape(), x = 12, y = 3, shapeA = new jCore.Shape(), 
                canvas = new jCore.Canvas({}), 
                containerB = new jCore.Shape({
                    container: 'regularcontainer',
                }), childrenNum, childrenNum2;
            a.setContainerBehavior('regularcontainer');
            a.setCanvas(canvas);
            a.setParent(canvas);
            containerB.setCanvas(canvas);
            containerB.setParent(canvas);
			a.addElement(shapeA, x, y, true);
			childrenNum2 = containerB.getChildren().getSize();
			childrenNum = a.getChildren().getSize();
			a.swapElementContainer(shapeA, containerB);
			expect(a.getChildren().getSize()).toBe(childrenNum - 1);
			expect(a.getChildren().contains(shapeA)).toBeFalsy();
			expect(containerB.getChildren().getSize()).toBe(childrenNum2 + 1);
			expect(containerB.getChildren().contains(shapeA)).toBeTruthy();
			document.body.appendChild(a.getHTML());
			document.body.appendChild(containerB.getHTML());
			expect(jQuery(a.getHTML()).find('#'+shapeA.getID()).length).toBe(0);
			expect(jQuery(containerB.getHTML()).find('#'+shapeA.getID()).length).toBe(1);
			expect(shapeA.getX()).toBe(x);
			expect(shapeA.getY()).toBe(y);
			expect(parseInt(shapeA.getHTML().style.left)).toBe(x);
			expect(parseInt(shapeA.getHTML().style.top)).toBe(y);
		});
	});
	describe('method "getChildren"', function(){
		it('should return the children property', function(){
			expect(a.getChildren()).toBe(a.children);
		});
	});
	describe('method "setDropAcceptedSelectors"', function(){
		it('should set the selectors of the current drop behavior', function(){
			var selector = '.xxx', overwrite = true;
			spyOn(a.drop, 'updateSelectors');
			a.setDropAcceptedSelectors(selector, overwrite);
			expect(a.drop.updateSelectors).toHaveBeenCalled();
			expect(a.drop.updateSelectors.mostRecentCall.args[0]).toEqual(a);
			expect(a.drop.updateSelectors.mostRecentCall.args[1]).toEqual(selector);
			expect(a.drop.updateSelectors.mostRecentCall.args[2]).toEqual(overwrite);
		});
	});
	describe('method "updateBehaviors"', function(){
		it('should attach the drop behavior to the element', function(){
			spyOn(a.drop, 'attachDropBehavior');
			spyOn(a.drop, 'updateSelectors');
			a.updateBehaviors();
			expect(a.drop.attachDropBehavior).toHaveBeenCalled();
			expect(a.drop.attachDropBehavior.calls.length).toBe(1);
			expect(a.drop.attachDropBehavior.calls[0].args[0]).toBe(a);
			expect(a.drop.updateSelectors).toHaveBeenCalled();
			expect(a.drop.updateSelectors.calls.length).toBe(1);
			expect(a.drop.updateSelectors.calls[0].args[0]).toBe(a);
		});
	});
	describe('method "stringify"', function(){
		it('should stringify the container and drop behavior of this object', function(){
			var theSuperclassClass = jCore.BehavioralElement.superclass, res;
			spyOn(theSuperclassClass.prototype, "stringify");
			res = a.stringify();
			expect(theSuperclassClass.prototype.stringify).toHaveBeenCalled();
			expect(res.container).toEqual(a.container);
			expect(res.drop).toEqual(a.drop);
		});
	});
});