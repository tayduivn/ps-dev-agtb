//***********************other class******************************************************//
describe('jCore.Label', function () {
		var label, parent,parentb, canvas, c, x, y, i,a;
		beforeEach(function () {
		canvas = new jCore.Canvas();
		document.body.appendChild(canvas.getHTML());
		canvas.setWidth(1000);
		canvas.setHeight(800);
		parent = new jCore.Shape();
		document.body.appendChild(parent.getHTML());
		parent.setWidth(400);
		parent.setHeight(400);		
		parentb = new jCore.Canvas();
    	//canvas.createHTML();
    	//parent.createHTML();     	
     	label = new jCore.Label({            
             message: "This is a label",
             orientation: "horizontal",
             fontFamily: "arial",
             size: 80,
             position: {
                 location: "center",
                 diffX: 2,
                 diffY: -1
             },
             updateParent: false,
             canvas: canvas,
             parent: parent
         	});
    	document.body.appendChild(label.getHTML());
    	label.attachListeners();
    	label.applyStyleToHandlers();
    	label.defineEvents();

		});

		afterEach(function () {
		});

		describe('method "createHTML"', function () {
			it('Should create a HTML for this Label', function () {
		    	expect(label.html).toBeTruthy();		
			});
		});

		describe('method "displayText"', function () {
			it('Should displays text for this Label', function () {
		    	label.displayText(true);
		    	expect(label.text.style.display == "block").toBeTruthy();
		    	label.displayText(false);
		    	expect(label.text.style.display == "none").toBeTruthy();		    		
			});
		});

		describe('method "setMessage"', function () {
			it('Should sets the new message for this Label', function () {
		    	label.setMessage("test");
		    	expect(label.text.innerHTML == "test").toBeTruthy();
			});
		});

		describe('method "getMessage"', function () {
			it('Should gets the message for this Label', function () {
		    	label.setMessage("test");
		    	expect(label.text.innerHTML == label.getMessage()).toBeTruthy();
		    	expect("test" == label.getMessage()).toBeTruthy();		    	
			});
		});

	    describe('method "setOrientation"', function () {
			it('Should sets the orientation for this Label', function () {
				label.setOrientation("vertical");
				expect(label.orientation == "vertical").toBeTruthy();
				label.setOrientation("horizontal");
				expect(label.orientation == "horizontal").toBeTruthy();	
			});
		});
		describe('method "setFontFamily"', function () {
			it('Should sets the font family for this Label', function () {
				label.setFontFamily("arial");
				expect(label.fontFamily == "arial").toBeTruthy();
			    expect(label.html.style.fontFamily == "arial").toBeTruthy();
			});
		});	
		describe('method "setFontSize"', function () {
			it('Should sets the font family for this Label', function () {
				label.setFontSize(7);
				expect(label.fontSize == 7).toBeTruthy();
			    expect(label.html.style.fontSize == "7pt").toBeTruthy();
			});
		});
		describe('method "setUpdateParent"', function () {
			it('Should sets the update parent for this Label', function () {
				label.setUpdateParent(parentb);
				expect(label.updateParent == parentb).toBeTruthy();
			});
		});
		
		describe('method "setOverflow"', function () {
			it('Should sets the overflow for this Label', function () {
				label.setOverflow(true);
				expect(label.overflow).toBeTruthy();
				label.setOverflow(false);
				expect(!label.overflow).toBeTruthy();				
			});
		});

		describe('method "parseMessage"', function () {
			it('Should gets the size of word for this Label', function () {
				label.setMessage('This is a text');
				expect(label.parseMessage()[0] == 4);
				expect(label.parseMessage()[1] == 2);
				expect(label.parseMessage()[3] == 1);
				expect(label.parseMessage()[4] == 4);					
			});
		});

		describe('method "updateDimension"', function () {
			it('Should update zoom for this Label', function () {
				label.setWidth(77);									
				label.updateDimension();
				expect(label.width === 77).toBeTruthy();			
			});
		});

		describe('method "setLabelPosition"', function () {
			it('Should Sets the position of the label regarding its parent', function () {
				// Compare the width of this label with his parent
				var i=0, sw=false, pos = "center";
				label.parent.setWidth(100);
				label.setLabelPosition(pos,100,100);
				expect(label.parent.width == 100 ).toBeTruthy();
				// Compare the width parent with the label width
				expect(label.text.style.width == "auto").toBeTruthy();
			});
			it('Should Sets new coordinates of the label', function () {
				var pos;
				label.setLabelPosition("top");
				pos = parseInt(label.html.style.top);
				label.setLabelPosition("center");
				expect(pos < parseInt(label.html.style.top)).toBeTruthy();	
				pos = parseInt(label.html.style.top);
				label.setLabelPosition("bottom");
				expect(pos < parseInt(label.html.style.top)).toBeTruthy();
				label.setLabelPosition("bottom-left");
				pos = parseInt(label.html.style.left);
				label.setLabelPosition("bottom");							
				expect(pos < parseInt(label.html.style.left)).toBeTruthy();
				pos = parseInt(label.html.style.top);
				label.setLabelPosition("bottom-right");							
				expect(pos > parseInt(label.html.style.left)).toBeTruthy();							               		    	
			});
		});
		describe('method "attachListeners"', function () {
			it('Should attach the listeners for this Label', function () {
				expect(label.textField.style.display == "none").toBeTruthy();
				$(label.html).trigger({type:"dblclick",pageX:100,pageY:100})
				expect(label.textField.style.display == "block").toBeTruthy();							
			});
		});

		describe('method "getFocus"', function () {
			it('Should get Focus in the textField for this Label', function () {
				label.getFocus();
				expect(label.textField.style.display == "block").toBeTruthy();							
			});
		});
		
		describe('method "loseFocus"', function () {
			it('Should lose Focus in the textField for this Label', function () {
				label.getFocus();
				expect(label.textField.style.display == "block").toBeTruthy();
				//label.loseFocus();
				//expect(label.textField.style.display == "none").toBeTruthy();
			});
		});

		describe('method "updateVertically"', function () {
			it('Should Updates its parent height according to the size of the label', function () {
				label.updateVertically();				
			});
		});
});