//***********************other class******************************************************//
describe('jCore.CustomShape', function () {
  var canvas,customShape;
  beforeEach(function () {
    canvas = new jCore.Canvas({
                  width: 400,
                  height: 400,
                  readOnly: false       
    });
  document.body.appendChild(canvas.getHTML());
  customShape = new jCore.CustomShape({
       connectAtMiddlePoints : true,
       layers: [
                 {
                   layerName : "first-layer",
                   priority: 2,
                   visible: true,
                   style: {
                   cssClasses: ['bpmn_zoom']
                   },
                   zoomSprites : ['img_50_start',
                   'img_75_start', 'img_100_start',
                   'img_125_start', 'img_150_start']
                 }, 
                   {
                   layerName: "second-layer",
                   priority: 3,
                   visible: true
                   }
             ],
            connectionType: "regular"
         });
  });
  describe('method "createLayer"', function () {
    it('should Creates a layer given its configuration options', function () {
      var c1= customShape.layers.getSize();
      customShape.createLayer({
                   layerName : "first-layer",
                   priority: 2,
                   visible: true,
                   style: {
                   cssClasses: ['bpmn_zoom']
                   }
            });
            expect(customShape.layers.getSize()>c1).toBeTruthy();
            expect(customShape.layers.asArray()[2].layerName == "first-layer").toBeTruthy();           
        });
  });
  describe('method "addLabel"', function () {
    it('should Adds a label to the array of labels and also appends its html', function () {
      var c = customShape.labels.asArray().length;
      customShape.addLabel("Label1");
      expect(customShape.labels.asArray()[customShape.labels.asArray().length-1] == "Label1").toBeTruthy();         
      expect(customShape.labels.asArray().length > c).toBeTruthy();
      });
  });
  describe('method "createHTML"', function () {
    it('should create a new HTML element', function () {
      var html = customShape.createHTML();
      expect(html).toBeDefined();
            expect(html.tagName).toBeDefined();
            expect(html.nodeType).toBeDefined();
            expect(html.nodeType).toEqual(document.ELEMENT_NODE);
    });
  });
  describe('method "updateLayers"', function () {
    it('should Updates the properties of this shape layers according to the shape itself', function () {
      customShape.canvas = canvas;
      expect(customShape.getLayers().get(0).canvas == null);
      customShape.updateLayers();
      expect(customShape.getLayers().get(0).canvas == canvas);      
    });
  });
   describe('method "findLayerPosition"', function () {
    it('should Returns what it should be the next layer if there is such in the DOM tree', function () {
      expect(customShape.findLayerPosition(customShape.getLayers().get(0)) == customShape.getLayers().get(1)).toBeTruthy();
    });
  });
   describe('method "addLayer"', function () {
    it('should Adds a new layer to the corresponding shape', function () {
      customShape.addLayer(customShape.getLayers().get(0));
      expect(customShape.getLayers().get(0) == customShape.getLayers().get(2)).toBeTruthy();
    });
  });
  describe('method "findLayer"', function () {
    it('should Finds a given layer by ID or null of it doesnt exist', function () {
      expect(customShape.findLayer(customShape.getLayers().get(0).id) == customShape.getLayers().get(0)).toBeTruthy();
      expect(customShape.findLayer("test") == undefined).toBeTruthy();
    });
  }); 
  describe('method "hideLayer"', function () {
    it('should Finds a given layer by ID or null of it doesnt exist', function () {
      expect(customShape.getLayers().get(0).visible).toBeTruthy();     
      customShape.hideLayer(customShape.getLayers().get(0).id);
      expect(customShape.getLayers().get(0).visible).toBeFalsy();     
    });
  });
  describe('method "showLayer"', function () {
    it('should Makes a layer visible', function () {
      expect(customShape.getLayers().get(0).visible).toBeTruthy();     
      customShape.hideLayer(customShape.getLayers().get(0).id);
      expect(customShape.getLayers().get(0).visible).toBeFalsy();
      customShape.showLayer(customShape.getLayers().get(0).id);
      expect(customShape.getLayers().get(0).visible).toBeTruthy();      
    });
  }); 
});

