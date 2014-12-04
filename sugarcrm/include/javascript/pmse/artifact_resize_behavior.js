/*global jCore*/
var AdamArtifactResizeBehavior = function () {
};

AdamArtifactResizeBehavior.prototype = new jCore.RegularResizeBehavior();
AdamArtifactResizeBehavior.prototype.type = "AdamArtifactResizeBehavior";


/**
 * Sets a shape's container to a given container
 * @param shape
 */
AdamArtifactResizeBehavior.prototype.onResizeStart = function (shape) {
    return jCore.RegularResizeBehavior
        .prototype.onResizeStart.call(this, shape);
};
/**
 * Removes shape from its current container
 * @param shape
 */
AdamArtifactResizeBehavior.prototype.onResize = function (shape) {
    //RegularResizeBehavior.prototype.onResize.call(this, shape);
    return function (e, ui) {
        jCore.RegularResizeBehavior
            .prototype.onResize.call(this, shape)(e, ui);
       //TODO Rewrite resize functionality using paint function based on segments
    };
};
