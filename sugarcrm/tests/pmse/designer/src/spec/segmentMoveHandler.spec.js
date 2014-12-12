//***********************other class******************************************************//
describe('jCore.SegmentMoveHandler', function () {
	var segmentMove, segment, rectangle;
	beforeEach(function () {
	 	segment = new jCore.Segment();
	 	rectangle = new jCore.Rectangle();
	 	segmentMoveHandler = new jCore.SegmentMoveHandler({
               width: 8,
               height: 8,
               parent: segment,
               orientation: 0, 
               representation: rectangle,
               color: new jCore.Color(255, 0, 0)  
           });
	});
	afterEach(function () {
	});
	describe('method "paint"', function () {
		it("should Paints this resize handler by calling it's parent's paint and setting the visibility of this resize handler", function () {
            var v = segmentMoveHandler.visible;
            segmentMoveHandler.createHTML();
            segmentMoveHandler.paint();
            expect(segmentMoveHandler.visible == v).toBeTruthy();
		});
	})	
});