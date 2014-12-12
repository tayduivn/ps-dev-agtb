//***********************other class******************************************************//
describe('jCore.Layer', function () {
  var layer,layer1,customShape;
  beforeEach(function () {
    layer = new jCore.Layer();
    layer1 = new jCore.Layer();    
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
  afterEach(function () {
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
  describe('method "comparisonFunction"', function () {
    it('should Comparison function for ordering layers according to priority', function () {
      layer1.priority = 1;
      expect(layer.comparisonFunction(layer1,layer)).toBeTruthy();
      expect(layer.comparisonFunction(layer,layer1)).toBeFalsy();         
    });
  });
  describe('method "setLayerName"', function () {
    it('should Sets the layer name', function () {
      layer.setLayerName("labeltest");
      expect(layer.layerName == "labeltest").toBeTruthy();
    });
  });  
  describe('method "setZoomSprites"', function () {
    it('should Sets the css classes for the zoom scales', function () {
        layer.setZoomSprites(["class50","class75","class100","class125","class150"]);
        expect(layer.zoomSprites[0] == "class50").toBeTruthy();
        expect(layer.zoomSprites[1] == "class75").toBeTruthy();
        expect(layer.zoomSprites[2] == "class100").toBeTruthy();
        expect(layer.zoomSprites[3] == "class125").toBeTruthy();
        expect(layer.zoomSprites[4] == "class150").toBeTruthy();
    });
  });
  describe('method "setPriority"', function () {
    it('should Sets the priority of the layer', function () {
      layer.setPriority(5);
      expect(layer.priority == 5).toBeTruthy();
    });
  });
});
       