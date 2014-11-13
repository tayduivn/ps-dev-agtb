/*global $, jCore */
var Tree = function () {
};

Tree.treeReload = function (id, items) {
    var shape,
        $elem = $('#adam_tree');
    $elem.empty();

    $elem.pmtree({
        id: id,
        collapsed: true,
        items: items,
        select: function (param) {
            shape = jCore.getActiveCanvas().customShapes.find('id', param.uid);
            if (shape) {
                shape.canvas.emptyCurrentSelection();
                shape.canvas.addToSelection(shape);
                //to disable textbox of label
                if (shape.canvas.currentLabel) {
                    shape.canvas.currentLabel.loseFocus();
                }
                //for property grids
                shape.canvas.project.updatePropertiesGrid(shape);
            }
        },
        unselect: function (id) {
//            alert ('unselect item');
        }

    });
};

var setSelectedNode = function (shape) {
    var id = "#" + $('a[uid ="' + shape.getID() + '"]').attr("desc");

    $(".treechild").attr("status", "unmarked");
    $(".treechild").css("background", "#fff");

    $(id).css("background", "#CEE3F6");
    $(id).attr("status", "marked");
//    var oShape = {};
};

/*global jCore, $

*/
/**
 * @class AdamConnectionDragBehavior
 * Handle the DragBehavior for Shapes
 *
 * @constructor
 * Creates a new object instance
 */
var AdamConnectionDragBehavior = function () {
    jCore.ConnectionDragBehavior.call(this);
};
AdamConnectionDragBehavior.prototype = new jCore.ConnectionDragBehavior();
/**
 * Defines the object type
 * @type {String}
 */
AdamConnectionDragBehavior.prototype.type = "AdamConnectionDragBehavior";

/**
 * Define the functionality associated with drag events
 * @param customShape
 * @return {Function}
 */
//AdamConnectionDragBehavior.prototype.onDrag = function (customShape) {
//    return function (e, ui) {
//        var canvas = customShape.getCanvas(),
//            endPoint = new jCore.Point();
//        if (canvas.connectionSegment) {
//            //remove the connection segment in order to create another one
//            $(canvas.connectionSegment.getHTML()).remove();
//        }
//
//        e.pageX = e.pageX || e.originalEvent.pageX;
//        e.pageY = e.pageY || e.originalEvent.pageY;
//
//        //Determine the point where the mouse currently is
//        endPoint.x = e.pageX - canvas.getX() + canvas.getLeftScroll();
//        endPoint.y = e.pageY - canvas.getY() + canvas.getTopScroll();
//
//        //creates a new segment from where the helper was created to the
//        // currently mouse location
//
////        canvas.connectionSegment = new jCore.Segment({
////            startPoint : customShape.startConnectionPoint,
////            endPoint : endPoint,
////            parent : canvas,
////            zOrder: jCore.Style.MAX_ZINDEX * 2
////        });
////        //We make the connection segment point to helper in order to get
////        // information when the drop occurs
////        canvas.connectionSegment.pointsTo = customShape;
////        //create HTML and paint
////        //canvas.connectionSegment.createHTML();
////        canvas.connectionSegment.paint();
//        console.log("Connection onDrag", e.pageX, e.pageY);
//    };
//};


/**
 * @class CustomShapeDragBehavior
 * Encapsulates the drag behavior of a custom shape (with ports and connections)
 * , it also encapsulates the behavior for multiple drag
 * @extends DragBehavior
 *
 * @constructor Creates a new instance of the class
 *
 */
var AdamShapeDragBehavior = function () {
    jCore.CustomShapeDragBehavior.call(this);
};
AdamShapeDragBehavior.prototype = new jCore.CustomShapeDragBehavior();

/**
 * Type of the instances
 * @property {String}
 */
AdamShapeDragBehavior.prototype.type = "AdamShapeDragBehavior";

/**
 * Attach the drag behavior and ui properties to the corresponding shape
 * @param {CustomShape} customShape
 */
AdamShapeDragBehavior.prototype.attachDragBehavior = function (customShape) {
    var dragOptions,
        $customShape = $(customShape.getHTML());
    dragOptions = {
        revert : false,
        helper : "none",
        cursorAt : false,
        revertDuration : 0,
        disable : false,
        grid : [1, 1],
        start : this.onDragStart(customShape),
        drag : this.onDrag(customShape, true),
        stop : this.onDragEnd(customShape, true),
        containment: "parent",
        scroll: false

    };
    $customShape.draggable(dragOptions);
};
/*global jCore, MessagePanel, translate, $, AdamFlow, AdamCommandReconnect

*/
/**
 * @class AdamContainerDropBehavior
 * Handle the drop behavior for the new AdamShapes
 *
 *
 * @constructor
 * Create a new Drop Behavior object
 * @param {Array} selectors
 *
 */
var AdamContainerDropBehavior = function (selectors) {
    jCore.ContainerDropBehavior.call(this, selectors);
};
AdamContainerDropBehavior.prototype = new jCore.ContainerDropBehavior();

/**
 * Defines the object type
 * @type {String}
 */
AdamContainerDropBehavior.prototype.type = "AdamContainerDropBehavior";

/**
 * Define the hook method when an shape is dropped and validate if the shape
 * is an boundary event
 * @param shape
 * @param e
 * @param ui
 * @return {Boolean}
 */
AdamContainerDropBehavior.prototype.dropHook = function (shape, e, ui) {
    var id = ui.draggable.attr('id'),
        result,
        droppedElement = shape.canvas.customShapes.find('id', id);
    if (droppedElement.type === 'AdamEvent'
            && droppedElement.getEventType() === 'BOUNDARY') {
        droppedElement.setPosition(droppedElement.oldX, droppedElement.oldY);
        result = false;
    } else {
        result = true;
    }
    return result;
};


/**
 * Define the functionality when an shape is dropped
 * @param shape
 * @return {Function}
 */
AdamContainerDropBehavior.prototype.onDrop = function (shape) {
    return function (e, ui) {


        var customShape,
            canvas = shape.getCanvas(),
            selection,
            sibling,
            i,
            command,
            coordinates,
            id,
            shapesAdded =  [],
            mp,
            containerBehavior = shape.containerBehavior;
        if (canvas.readOnly) {
            return false;
        }

        shape.entered = false;
        if (ui.helper && ui.helper.attr('id') === "drag-helper") {
            return false;
        }
        id = ui.draggable.attr('id');
        customShape = canvas.toolBarShapeFactory(id);
        if (customShape === null) {

            customShape = canvas.customShapes.find('id', id);

            if (!customShape || !shape.dropBehavior.dropHook(shape, e, ui)) {
                return false;
            }

            if (!(customShape.parent &&
                customShape.parent.id === shape.id)) {

                selection = canvas.currentSelection;
                for (i = 0; i < selection.getSize(); i += 1) {
                    sibling = selection.get(i);
                    coordinates = jCore.Utils.getPointRelativeToPage(sibling);
                    coordinates = jCore.Utils
                        .pageCoordinatesToShapeCoordinates(shape, null,
                            coordinates.x, coordinates.y);
                    shapesAdded.push({
                        shape : sibling,
                        container : shape,
                        x : coordinates.x,
                        y : coordinates.y,
                        topLeft : false
                    });
                }
                command = new jCore.CommandSwitchContainer(shapesAdded);
                command.execute();
                canvas.commandStack.add(command);
                canvas.multipleDrop = true;

            }

            // fix resize minWidth and minHeight and also fix the dimension
            // of this shape (if a child made it grow)

            shape.updateDimensions(10);

            canvas.updatedElement = null;
        } else {
            e.pageX = e.pageX || e.originalEvent.pageX;
            e.pageY = e.pageY || e.originalEvent.pageY;

            coordinates = jCore.Utils.pageCoordinatesToShapeCoordinates(shape, e);
            if (!canvas.validatePositions(customShape, coordinates)) {
                mp = new MessagePanel({
                    title: 'Error',
                    wtype: 'Error',
                    message: translate('LBL_PMSE_MESSAGE_CANNOTDROPOUTSIDECANVAS')
                });
                mp.show();
                return false;
            }
            shape.addElement(customShape, coordinates.x, coordinates.y,
                customShape.topLeftOnCreation);
            customShape.attachListeners();

            //since it is a new element in the designer, we triggered the
            //custom on create element event
            canvas.updatedElement = customShape;

            // create the command for this new shape
            command = new jCore.CommandCreate(customShape);
            canvas.commandStack.add(command);
            command.execute();
            //shape.updateSize();
            //console.log('Element Added:',customShape);
            //$('input').blur();
            canvas.hideAllFocusedLabels();


            if (customShape.labels.get(0)) {
                customShape.labels.get(0).getFocus();

               //console.log(customShape.labels.get(0).getID());

                $('#' + customShape.labels.get(0).getID()).find('input').select();


                // $(customShape.labels.get(0).textField.html).focus(
                //     function () {
                //         $(this).select();
                //     }
                // );
                // (function() { // select text on focus
                //     $(this).select();
                // });
                // $(customShape.labels.get(0).textField.html).mouseup(function(e){ // fix for chrome and safari
                //     e.preventDefault();
                // });
            }
        }
    };
};

//

/**
* @class AdamConnectionDropBehavior
* Extends the functionality to handle creation of connections
*
* @constructor
* Creates a new instance of the object
*/
var AdamConnectionDropBehavior = function (selectors) {
    jCore.ConnectionDropBehavior.call(this, selectors);
};
AdamConnectionDropBehavior.prototype = new jCore.ConnectionDropBehavior();
/**
* Defines the object type
* @type {String}
*/
AdamConnectionDropBehavior.prototype.type = "AdamConnectionDropBehavior";

/**
 * Defines a Map of the basic Rules
 * @type {Object}
 */
AdamConnectionDropBehavior.prototype.basicRules = {
    AdamEvent : {
        AdamEvent : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamActivity : {
            connection : 'regular',
            type: 'SEQUENCE'
        }
    },
    AdamActivity: {
        AdamActivity : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamArtifact : {
            connection : 'dotted',
            destDecorator: 'con_none',
            type: 'ASSOCIATION'
        },
        AdamIntermediateEvent : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamEndEvent: {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamGateway : {
            connection : 'regular',
            type: 'SEQUENCE'
        }
    },
    AdamStartEvent : {
        AdamActivity : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamIntermediateEvent : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamEndEvent : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamGateway : {
            connection : 'regular',
            type: 'SEQUENCE'
        }
    },
    AdamIntermediateEvent : {
        AdamActivity : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamIntermediateEvent : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamEndEvent : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamGateway : {
            connection : 'regular',
            type: 'SEQUENCE'
        }
    },
    AdamBoundaryEvent : {
        AdamActivity : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamIntermediateEvent : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamEndEvent : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamGateway : {
            connection : 'regular',
            type: 'SEQUENCE'
        }
    },
    AdamGateway : {
        AdamActivity : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamIntermediateEvent : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamEndEvent : {
            connection : 'regular',
            type: 'SEQUENCE'
        },
        AdamGateway : {
            connection : 'regular',
            type: 'SEQUENCE'
        }
    },
    AdamArtifact: {
        AdamActivity: {
            connection : 'dotted',
            destDecorator: 'con_none',
            type: 'ASSOCIATION'
        }
    }
};

/**
 * Defines a Map of the init Rules
 * @type {Object}
 */

AdamConnectionDropBehavior.prototype.initRules = {
    AdamCanvas: {
        AdamCanvas: {
            name: 'AdamCanvas to AdamCanvas',
            rules: AdamConnectionDropBehavior.prototype.basicRules
        }
    },
    AdamActivity: {
        AdamCanvas: {
            name: 'AdamActivity to AdamCanvas',
            rules: AdamConnectionDropBehavior.prototype.basicRules
        }
    }
};


/**
 * Handle the hook functionality when a drop start
 *  @param shape
 */
AdamConnectionDropBehavior.prototype.dropStartHook = function (shape, e, ui) {

    //if (!(ui.helper && ui.helper.attr('id') === "drag-helper")) {
    //  return false;
    //}
    shape.srcDecorator = null;
    shape.destDecorator = null;
    var draggableId = ui.draggable.attr("id"),
        source  = shape.canvas.customShapes.find('id', draggableId),
        prop;
    if (source) {
        prop = this.validate(source, shape);
        if (prop) {
            shape.setConnectionType({
                type: prop.type,
                segmentStyle: prop.connection,
                srcDecorator: prop.srcDecorator,
                destDecorator: prop.destDecorator
            });

            return true;
        } else {
            // verif if port is changed
            if (typeof source !== 'undefined') {
                if (!(ui.helper && ui.helper.attr('id') === "drag-helper")) {
                    return false;
                }
                //showMessage('Invalid Connection');
                shape.setConnectionType('none');
            }
        }
    }

    return true;
};

/**
 * Connection validations method
 * return an object if is valid otherwise return false
 * @param {Connection} source
 * @param {Connection} target
 */
AdamConnectionDropBehavior.prototype.validate = function (source, target) {
    var sType,
        tType,
        rules,
        initRules,
        initRulesName,
        BPMNAuxMap = {
            AdamEvent : {
                'START' : 'AdamStartEvent',
                'END': 'AdamEndEvent',
                'INTERMEDIATE': 'AdamIntermediateEvent',
                'BOUNDARY': 'AdamBoundaryEvent'
            },
            bpmnArtifact : {
                'TEXTANNOTATION': 'bpmnAnnotation'
            }
        };

    if (source && target) {
        if (source.getID() === target.getID()) {
            return false;
        }

        if (this.initRules[source.getParent().getType()]
                && this.initRules[source.getParent().getType()][target.getParent().getType()]) {


            initRules = this.initRules[source.getParent().getType()][target.getParent().getType()].rules;
            initRulesName = this.initRules[source.getParent().getType()][target.getParent().getType()].name;
            // get the types
            sType = source.getType();
            tType = target.getType();
            //Custimize all adam events
            if (sType === 'AdamEvent') {
                if (BPMNAuxMap[sType] && BPMNAuxMap[sType][source.getEventType()]) {
                    sType = BPMNAuxMap[sType][source.getEventType()];
                }
            }
            if (tType === 'AdamEvent') {
                if (BPMNAuxMap[tType] && BPMNAuxMap[tType][target.getEventType()]) {
                    tType = BPMNAuxMap[tType][target.getEventType()];
                }
            }

            if (initRules[sType] && initRules[sType][tType]) {
                rules = initRules[sType][tType];
            } else {
                rules = false;
            }
            if (initRules) {
                switch (initRulesName) {
                case 'bpmnPool to bpmnPool':
                    if (source.getParent().getID() !== target.getParent().getID()) {
                        rules = false;
                    }
                    break;
                case 'bpmnLane to bpmnLane':
                    if (source.getFirstPool(source.parent).getID()
                            !== target.getFirstPool(target.parent).getID()) {
                        if (this.extraRules[sType]
                                && this.extraRules[sType][tType]) {
                            rules = this.extraRules[sType][tType];
                        } else {
                            rules = false;
                        }
                    }
                    break;
                case 'bpmnActivity to bpmnLane':
                    if (this.basicRules[sType]
                            && this.basicRules[sType][tType]) {
                        rules = this.basicRules[sType][tType];
                    } else {
                        rules = false;
                    }
                    break;
                default:
                    break;
                }
            } else {
                rules = false;
            }

            return rules;
        } else {
            // get the types
            sType = source.getType();
            tType = target.getType();
            //
            if (sType === 'AdamEvent') {
                if (BPMNAuxMap[sType] && BPMNAuxMap[sType][source.getEventType()]) {
                    sType = BPMNAuxMap[sType][source.getEventType()];
                }
            }
            if (tType === 'AdamEvent') {
                if (BPMNAuxMap[tType] && BPMNAuxMap[tType][target.getEventType()]) {
                    tType = BPMNAuxMap[tType][target.getEventType()];
                }
            }
            if (this.advancedRules[sType] && this.advancedRules[sType][tType]) {
                rules = this.advancedRules[sType][tType];
            } else {
                rules = false;
            }
            return rules;
        }

    }
};

/**
* Handle the functionality when a shape is dropped
* @param shape
*/
AdamConnectionDropBehavior.prototype.onDrop = function (shape) {
    var that = this;
    return function (e, ui) {
        var canvas  = shape.getCanvas(),
            id = ui.draggable.attr('id'),
            x,
            y,
            currLeft,
            currTop,
            startPoint,
            sourceShape,
            sourcePort,
            endPort,
            endPortXCoord,
            endPortYCoord,
            connection,
            currentConnection = canvas.currentConnection,
            srcPort,
            dstPort,
            port,
            success = false,
            command,
            aux,
            segmentMap,
            prop;
        shape.entered = false;
        if (!shape.dropBehavior.dropStartHook(shape, e, ui)) {
            return false;
        }
        if (shape.getConnectionType() === "none") {
            App.alert.show('warning_connection', {
                level: 'warning',
                messages: translate('LBL_PMSE_ADAM_UI_INVALID_CONNECTION'),
                autoClose: true,
                autoCloseDelay: 9000
            });
            return true;
        }

        if (currentConnection) {
            srcPort = currentConnection.srcPort;
            dstPort = currentConnection.destPort;
            if (srcPort.id === id) {
                port = srcPort;
            } else if (dstPort.id === id) {
                port = dstPort;
            } else {
                port = null;
            }
        }
        if (ui.helper && ui.helper.attr('id') === "drag-helper") {

            //if its the helper then we need to create two ports and draw a
            // connection
            //we get the points and the corresponding shapes involved
            startPoint = shape.canvas.connectionSegment.startPoint;
            sourceShape = shape.canvas.connectionSegment.pointsTo;
            //determine the points where the helper was created
            if (sourceShape.parent && sourceShape.parent.id === shape.id) {
                return true;
            }
            sourceShape.setPosition(sourceShape.oldX, sourceShape.oldY);

            startPoint.x -= sourceShape.absoluteX;
            startPoint.y -= sourceShape.absoluteY;

            //create the ports
            sourcePort = new jCore.Port({
                width: 10,
                height: 10
            });
            endPort = new jCore.Port({
                width: 10,
                height: 10
            });

            //determine the position where the helper was dropped
            endPortXCoord = ui.offset.left - shape.canvas.getX() -
                shape.getAbsoluteX() + shape.canvas.getLeftScroll();
            endPortYCoord = ui.offset.top - shape.canvas.getY() -
                shape.getAbsoluteY() + shape.canvas.getTopScroll();

            // add ports to the corresponding shapes
            // addPort() determines the position of the ports
            sourceShape.addPort(sourcePort, startPoint.x, startPoint.y);
            shape.addPort(endPort, endPortXCoord, endPortYCoord,
                false, sourcePort);

            //add ports to the canvas array for regularShapes
            //shape.canvas.regularShapes.insert(sourcePort).insert(endPort);

            //create the connection
            connection = new AdamFlow({
                srcPort : sourcePort,
                destPort: endPort,
                canvas : shape.canvas,
                segmentStyle: shape.connectionType.segmentStyle,
                flo_type: shape.connectionType.type
            });

            //set its decorators
//            connection.setSrcDecorator(new jCore.ConnectionDecorator({
//                decoratorPrefix: "adam-decorator",
//                decoratorType: "source",
//                width: 11,
//                height: 11,
//                canvas: shape.canvas,
//                parent: connection
//            }));

//            connection.setDestDecorator(new jCore.ConnectionDecorator({
//                decoratorPrefix: "adam-decorator",
//                decoratorType: "target",
//                style: {
//                    cssClasses: ['qennix']
//                },
//                width: 11,
//                height: 11,
//                canvas: shape.canvas,
//                parent: connection
//            }));
//
            connection.setSrcDecorator(new jCore.ConnectionDecorator({
                width: 11,
                height: 11,
                canvas: canvas,
                decoratorPrefix: (typeof shape.connectionType.srcDecorator !== 'undefined'
                    && shape.connectionType.srcDecorator !== null) ?
                        shape.connectionType.srcDecorator : "adam-decorator",
                decoratorType: "source",
                parent: connection
            }));

            connection.setDestDecorator(new jCore.ConnectionDecorator({
                width: 11,
                height: 11,
                canvas: canvas,
                decoratorPrefix: (typeof shape.connectionType.destDecorator !== 'undefined'
                    && shape.connectionType.destDecorator !== null) ?
                        shape.connectionType.destDecorator : "adam-decorator",
                decoratorType: "target",
                parent: connection
            }));

            connection.canvas.commandStack.add(new jCore.CommandConnect(connection));

            //connect the two ports
            connection.connect();
            connection.setSegmentMoveHandlers();

            //add the connection to the canvas, that means insert its html to
            // the DOM and adding it to the connections array
            canvas.addConnection(connection);

            // Filling AdamFlow fields
            connection.setTargetShape(endPort.parent);
            connection.setOriginShape(sourcePort.parent);
            connection.savePoints();

            // now that the connection was drawn try to create the intersections
            //connection.checkAndCreateIntersectionsWithAll();

            //attaching port listeners
            sourcePort.attachListeners(sourcePort);
            endPort.attachListeners(endPort);

            // finally trigger createEvent
            canvas.triggerCreateEvent(connection, []);
        } else if (port) {
            port.setOldParent(port.getParent());
            port.setOldX(port.getX());
            port.setOldY(port.getY());

            x = ui.position.left;
            y = ui.position.top;
            port.setPosition(x, y);
            shape.dragging = false;
            if (shape.getID() !== port.parent.getID()) {
                port.parent.removePort(port);
                currLeft = ui.offset.left - canvas.getX() -
                    shape.absoluteX + shape.canvas.getLeftScroll();
                currTop = ui.offset.top - canvas.getY() -
                    shape.absoluteY + shape.canvas.getTopScroll();
                shape.addPort(port, currLeft, currTop, true);
                canvas.regularShapes.insert(port);
            } else {
                shape.definePortPosition(port, port.getPoint(true));
            }

            // LOGIC: when portChangeEvent is triggered it gathers the state
            // of the connection but since at this point there's only a segment
            // let's paint the connection, gather the state and then disconnect
            // it (the connection is later repainted on, I don't know how)
            port.connection.connect();
            canvas.triggerPortChangeEvent(port);
            port.connection.disconnect();

            command = new jCore.CommandReconnect(port);
            port.canvas.commandStack.add(command);


            connection = port.getConnection();
            if (connection.srcPort.getID() === port.getID()) {
                prop = AdamConnectionDropBehavior.prototype.validate(
                    shape,
                    connection.destPort.getParent()
                );
            } else {
                prop = AdamConnectionDropBehavior.prototype.validate(
                    connection.srcPort.getParent(),
                    shape
                );
            }

            if (prop) {
                port.setOldParent(port.getParent());
                port.setOldX(port.getX());
                port.setOldY(port.getY());

                x = ui.position.left;
                y = ui.position.top;
                port.setPosition(x, y);
                shape.dragging = false;
                if (shape.getID() !== port.parent.getID()) {
                    port.parent.removePort(port);
                    currLeft = ui.offset.left - canvas.getX() -
                        shape.absoluteX + shape.canvas.getLeftScroll();
                    currTop = ui.offset.top - canvas.getY() - shape.absoluteY +
                        shape.canvas.getTopScroll();
                    shape.addPort(port, currLeft, currTop, true);
                    canvas.regularShapes.insert(port);
                } else {
                    shape.definePortPosition(port, port.getPoint(true));
                }

                // LOGIC: when portChangeEvent is triggered it gathers the state
                // of the connection but since at this point there's only a segment
                // let's paint the connection, gather the state and then disconnect
                // it (the connection is later repainted on, I don't know how)

                aux = {
                    before: {
                        condition: connection.flo_condition,
                        type: connection.flo_type,
                        segmentStyle: connection.segmentStyle,
                        srcDecorator: connection.srcDecorator.getDecoratorPrefix(),
                        destDecorator: connection.destDecorator.getDecoratorPrefix()
                    },
                    after: {
                        type : prop.type,
                        segmentStyle: prop.connection,
                        srcDecorator: prop.srcDecorator,
                        destDecorator: prop.destDecorator
                    }
                };
                connection.connect();
                canvas.triggerPortChangeEvent(port);
                command = new AdamCommandReconnect(port, aux);
                command.execute();
                port.canvas.commandStack.add(command);

            } else {
                return false;
            }
        }
        return false;
    };
};

/**
 * @class AdamConnectionContainerDropBehavior
 * Handle the drop behavior for containers
 *
 * @constructor
 * Create a new instance
 * @param {Object} options
 */
var AdamConnectionContainerDropBehavior  = function (options) {
    AdamConnectionDropBehavior.call(this, options);
};
AdamConnectionContainerDropBehavior.prototype = new AdamConnectionDropBehavior();
/**
 * Defines the object type
 * @type {String}
 */
AdamConnectionContainerDropBehavior.prototype.type = "AdamConnectionContainerDropBehavior";


AdamConnectionContainerDropBehavior.prototype.defaultSelector =
    ".custom_shape,.port";
//AdamConnectionContainerDropBehavior.prototype.validRelations = AdamContainerDropBehavior.prototype.validRelations;
/**
 * Extends the drap functionality
 * @param {Object} shape
 * @return {Function}
 */
AdamConnectionContainerDropBehavior.prototype.onDrop = function (shape) {
    return function (e, ui) {
        if (!AdamConnectionDropBehavior.prototype.onDrop.call(this, shape)(e, ui)) {
            AdamContainerDropBehavior.prototype.onDrop.call(this, shape)(e, ui);
        }
    };
};
AdamConnectionContainerDropBehavior.prototype.getSpecificType = AdamContainerDropBehavior.prototype.getSpecificType;

AdamConnectionContainerDropBehavior.prototype.validDrop = AdamContainerDropBehavior.prototype.validDrop;
AdamConnectionContainerDropBehavior.prototype.dropHook = AdamContainerDropBehavior.prototype.dropHook;

/*global jCore, AdamConnectionDragBehavior, AdamShapeDragBehavior, AdamConnectionDropBehavior, AdamConnectionContainerDropBehavior */
/**
 * @class AdamShape
 * Class to encapsulate common functionality for BPMN Elements
 *
 * @constructor
 * Create a new AdamShape object
 * @param {Object} options
 */
var AdamShape = function (options) {
    jCore.CustomShape.call(this, options);
    /**
     * Stores the label object used to show into the canvas
     * @type {Object}
     * @private
     */
    this.label = this.labels.get(0);
};
AdamShape.prototype = new jCore.CustomShape();

/**
 * Defines the object type
 * @type {String}
 */
AdamShape.prototype.type = "AdamShape";

/**
 * Defines the connection behavior
 * @type {Object}
 */
AdamShape.prototype.adamConnectionDropBehavior = null;

/**
 * Return the object type
 * @return {String}
 */
AdamShape.prototype.getType = function () {
    return this.type;
};

/**
 * Sets the label element
 * @param {String} value
 * @return {*}
 */
AdamShape.prototype.setName = function (value) {
    var item;
    if (this.label) {
        this.label.setMessage(value);
        if (listPanelError){
            if ( listPanelError.items.length ) {
                item = listPanelError.getItemById(this.id);
                if ( item ) {
                    if ( value.trim().length ) {
                        item.setTitle(value);                        
                    } else {
                        item.setTitle("[unnamed]");
                    }
                }
            }
        }
    }
    return this;
};
/**
 * Returns the label text
 * @return {String}
 */
AdamShape.prototype.getName = function () {
    var text = "";
    if (this.label) {
        text = this.label.getMessage();
    }
    return text;
};

/**
 * Create an abstract class to generate context menus
 * @return {Array}
 * @abstract
 */
AdamShape.prototype.getContextMenu = function () {
    return {};
};
/**
 * Create an abstract class to generate callbacks
 * @return {Object}
 * @abstract
 */
AdamShape.prototype.getContextMenuCallbacks = function () {
    return {};
};

/**
 * Overwrite the parent function to set the dimesion
 * @param {Number} x
 * @param {Number} y
 * @return {*}
 */
AdamShape.prototype.setDimension = function (x, y) {
    var factor;
    jCore.CustomShape.prototype.setDimension.call(this, x, y);
    if (this.getType() === 'AdamEvent' || this.getType() === 'AdamGateway') {
        factor = 3;
    } else {
        if (this.getType() === 'AdamActivity' && this.act_task_type === 'SCRIPTTASK') {
            factor = 3;
        } else {
            factor = 1;
        }

    }
    if (this.label) {
        this.label.setDimension((this.zoomWidth * 0.9 * factor) / this.canvas.zoomFactor,
            this.label.height);
        this.label.setLabelPosition(this.label.location, this.label.diffX, this.label.diffY);
    }

    return this;
};

/**
 * Extends the factory to accept custom drag behaviors
 * @param {String} type
 * @return {*}
 */

AdamShape.prototype.dragBehaviorFactory = function (type) {
    if (type === "regular") {
        if (!this.regularDragBehavior) {
            this.regularDragBehavior = new jCore.RegularDragBehavior();
        }
        return this.regularDragBehavior;
    }
    if (type === "connection") {
        if (!this.connectionDragBehavior) {
            this.connectionDragBehavior = new AdamConnectionDragBehavior();
        }
        return this.connectionDragBehavior;
    }
    if (type === "customshapedrag") {
        if (!this.customShapeDragBehavior) {
            //this.customShapeDragBehavior = new jCore.CustomShapeDragBehavior();
            this.customShapeDragBehavior = new AdamShapeDragBehavior();
        }
        return this.customShapeDragBehavior;
    }
    if (!this.noDragBehavior) {
        this.noDragBehavior = new jCore.NoDragBehavior();
    }
    return this.noDragBehavior;
};

/**
 * Extends the factory to accept custom drop behaviors
 * @param {String} type
 * @param {Array} selectors
 * @return {*}
 */
AdamShape.prototype.dropBehaviorFactory = function (type, selectors) {
    if (type === "nodrop") {
        if (!this.noDropBehavior) {
            this.noDropBehavior = new jCore.NoDropBehavior(selectors);
        }
        return this.noDropBehavior;
    }
    if (type === "container") {
        if (!this.containerDropBehavior) {
            this.containerDropBehavior = new jCore.ContainerDropBehavior(selectors);
        }
        return this.containerDropBehavior;
    }
    if (type === "connection") {
        if (!this.connectionDropBehavior) {
            this.connectionDropBehavior = new AdamConnectionDropBehavior(selectors);
        }
        return this.connectionDropBehavior;
    }
    if (type === "connectioncontainer") {
        if (!this.connectionContainerDropBehavior) {
            this.connectionContainerDropBehavior =
                new AdamConnectionContainerDropBehavior(selectors);
        }
        return this.connectionContainerDropBehavior;
    }
};

AdamShape.prototype.getDestElements = function () {
    var elements = [],
        i,
        port,
        connection;

    for (i = 0; i < this.getPorts().getSize(); i += 1) {
        port = this.getPorts().get(i);
        connection = port.connection;
        if (connection.srcPort.parent.getID() === this.getID()) {
            elements.push(connection.destPort.parent);
        }
    }
    return elements;
};

/**
 * Set flow as a default and update the other flows
 * @param {String} destID
 * @returns {AdamShape}
 */
AdamShape.prototype.setDefaultFlow = function (floID) {
    var i,
        port,
        connection;
    for (i = 0; i < this.getPorts().getSize(); i += 1) {
        port = this.getPorts().get(i);
        connection = port.connection;
        this.updateDefaultFlow(0);
        if (connection.srcPort.parent.getID() === this.getID()) {

            if (connection.getID() === floID) {
                this.updateDefaultFlow(floID);
                connection.setFlowCondition("");
                connection.changeFlowType('default');
                connection.setFlowType("DEFAULT");
            } else if (connection.getFlowType() === 'DEFAULT') {
                connection.changeFlowType('sequence');
                connection.setFlowType("SEQUENCE");
            }
        }

    }
    return this;
};


AdamShape.prototype.updateFlowConditions = function () {
    var i, connection, updatedElement;
    for (i = 0; i < this.getPorts().getSize(); i += 1) {
        connection = this.getPorts().get(i).connection;
        if (connection.flo_element_origin === this.getID()) {
            if (connection.flo_condition && connection.flo_condition !== '') {
                updatedElement = [
                    {
                        id: connection.getID(),
                        type: connection.type,
                        relatedObject: connection,
                        fields: [
                            {
                                field: "condition",
                                newVal: '',
                                oldVal: connection.getFlowCondition()
                            }
                        ]
                    }
                ];
                connection.setFlowCondition('');
                connection.getCanvas().triggerDefaultFlowChangeEvent(updatedElement);
            }
        }
    }
};

AdamShape.prototype.getFamilyNumber = function (shape) {
    var map = {
        'AdamActivity' : 5,
        'AdamEvent' : 6,
        'AdamGateway': 7,
        'AdamData': 8,
        'AdamArtifact': 9
    };
    return map[shape.getType()];
};

AdamShape.prototype.attachErrorToShape = function (objArray) {
    var i, j,
        sw,
        message,
        ruleArray,
        testCount,
        rule,
        error,
        sizeItems,
        allErrors = [];
    this.BPMNError = new jCore.ArrayList();
    for (i = 0; i < objArray.length; i += 1) {
        message  = objArray[i].message;
        ruleArray = objArray[i].rules;

        sw = (objArray[i].family === 8 && objArray[i].familyType === 4) ?
            false : true;
        for (j = 0; j < ruleArray.length; j += 1) {
            rule = ruleArray[j];

            testCount = this.countFlow(rule.element, rule.direction);
            if (objArray[i].family === 8 && objArray[i].familyType === 4) {
                sw = sw || (testCount > rule.value);

            } else {
                switch (rule.compare){
                    case '=':
                        sw = sw && (testCount === rule.value);
                        break;
                    case '>':
                        sw = sw && (testCount > rule.value);
                        break;
                    case '<':
                        sw = sw && (testCount < rule.value);
                        break;
                }
            }
        }
        if (!sw){
            //TODO attach error to shape
            this.BPMNError.insert({
                code: objArray[i].id,
                //element: rule.element,
                direction: rule.direction,
                description: message
            });
            //this.canvas.diagram.refreshErrorGrid(this);

        }
    }
    if (this.BPMNError.getSize() > 0) {
        this.addErrorLayer('error', 2);

    } else {
        this.clearErrors();
    }

    //listPanelError.setItems(items)
    //console.log(listPanelError);
    listPanelError.setItems(this.getShapeWithErros());
        if (countErrors){
            if (listPanelError.getItems().length){
                countErrors.style.display = "block";
                sizeItems = listPanelError.getAllErros();
                countErrors.textContent =  sizeItems === 1 ? sizeItems + translate('LBL_PMSE_BPMN_WARNING_SINGULAR_LABEL') : sizeItems + translate('LBL_PMSE_BPMN_WARNING_LABEL');
            } else {
                countErrors.textContent = "0" + translate('LBL_PMSE_BPMN_WARNING_SINGULAR_LABEL');
            }
        }
};


AdamShape.prototype.getShapeWithErros = function () {
    var i, shape, errors, error, f = [], items = [], object = {}, subObject = {};
    for (i = 0; i < canvas.getCustomShapes().getSize(); i += 1) {
        shape = canvas.getCustomShapes().get(i);
        if(shape.BPMNError !== undefined) {
            if (shape.BPMNError.getSize()){
                object = {};
                object.title = shape.getName()||'[unnamed]';

                object.errorType =  this.getShapeType(shape.getType(), shape);
                object.items = [];
                object.errorId = shape.getID();
                errors = shape.BPMNError;
                for ( j = 0; j < errors.getSize(); j += 1) {
                    subObject = {};
                    error = errors.get(j);
                    subObject.messageId =  error.code;
                    subObject.message = error.description;
                    object.items.push(subObject);
                }
                items.push(object);
                }
        }
    }
    return items;
};

AdamShape.prototype.getShapeType = function (type, shape) {
    var shapeType, shapeMessage, itemType = "";
    switch (type){
        case "AdamActivity" :
            shapeType = shape.act_task_type;
            itemType = type +shapeType; 
        break;
        case "AdamEvent" :
            shapeType = shape.getEventType();
            shapeMessage = shape.getEventMessage()||shape.getEventMarker();
            itemType = type +shapeType + shapeMessage;  
        break;
        case "AdamGateway" :
            shapeType = shape.getGatewayType();
            shapeMessage = shape.getGatewayType();
            itemType = type +shapeType;  
        break;
    };
    return itemType;
};

AdamShape.prototype.countFlow = function (element, direction) {
    var i,
        eleMap = {
            'sequenceFlow': 'regular',
            'associationFlow': 'dotted'
        },
        port,
        connection,
        count = 0;

    for (i = 0; i < this.getPorts().getSize(); i += 1) {
        port = this.getPorts().get(i);
        connection = port.getConnection();
        switch(direction) {
            case 'incoming':
                if (eleMap[element] === connection.segmentStyle){
                    if (port.getID() === connection.getDestPort().getID()){
                        count += 1;
                    }
                }
                break;
            case 'outgoing':
                if (eleMap[element] === connection.segmentStyle){
                    if (port.getID() === connection.getSrcPort().getID()){
                        count += 1;
                    }
                }break;
            case 'none':
                if (port.getID() === connection.getSrcPort().getID()){
                    count += 1;
                }
                break;
        }

    }

    return count;
};

AdamShape.prototype.addErrorLayer = function (cssMarker, position) {
    var layer, cl, cs, zoom, options;
    layer = this.layers.find('id', this.id + 'Layer-Errors');
    if (typeof position === 'undefined' || position === null) {
        cl = cssMarker;
        cs = 'bpmn_zoom';
    } else {
        cl = 'div-empty';
        cs = '';
    }
    if (typeof layer === 'undefined') {
        options = {
            layerName : "error-layer",
            priority: 3,
            visible: true,
            style: {
                cssClasses: []
            }
        };
        // Creating a layer
        layer = this.createLayer(options);

    } else {
        if (typeof position === 'undefined' || position === null) {
            layer.setElementClass(cl);
        }
    }
    if (typeof position !== 'undefined' && position !== null) {
        this.addErrors(layer, position);
    }
};

AdamShape.prototype.clearErrors = function () {
    var i, lMarker, ifExist;
    for (i = 0; i < this.markersArray.getSize(); i += 1){
        lMarker = this.markersArray.get(i);
        if (lMarker.position === 2) {
            //ifExist = true;

            lMarker.removeAllClasses();
            break;
        }
    }
};

AdamShape.prototype.addErrors = function (newLayer, pos) {
    var  nMarker, x, lMarker, ifExist = false,
        errorArrayClass = [], cls, i;

    for (i = 0; i < this.markersArray.getSize(); i += 1){
        lMarker = this.markersArray.get(i);
        if (lMarker.position === pos) {
            ifExist = true;
            break;
        }
    }
    for (i = 0; i < newLayer.ZOOMSCALES; i += 1) {
        cls = 'adam-status-' + ((i * 25) + 50) + '-warning adam-error-color icon-exclamation-sign';
        errorArrayClass.push(cls);
    }
    if (!ifExist) {
        nMarker = new AdamMarker({
            parent : newLayer,
            position : pos,
            height : 17,
            width : 17,
            markerZoomClasses : errorArrayClass
        });
        this.markersArray.insert(nMarker);

        nMarker.paint();
        nMarker.setElementClass(errorArrayClass);
    } else {
        lMarker.setElementClass(errorArrayClass);
    }
};
/*global jCore, $, AdamShape */
/**
 * @class AdamFlow
 * Handle the designer flows
 *
 * @constructor
 * Create a new flow object
 * @param {Object} options
 */
var AdamFlow = function (options) {
    jCore.Connection.call(this, options);
    /**
     * Unique Idenfier
     * @type {String}
     */
    this.flo_uid = null;
    /**
     * Defines the connecion/flow type
     * @type {String}
     */
    this.flo_type = null;
    /**
     * Defines the connection/flow name
     * @type {String}
     */
    this.flo_name = null;
    /**
     * Unique Identifier of the source shape
     * @type {String}
     */
    this.flo_element_origin = null;
    /**
     * Defines the type of shape for the source
     * @type {String}
     */
    this.flo_element_origin_type = null;
    /**
     * Unique Identifier of the target shape
     * @type {String}
     */
    this.flo_element_dest = null;
    /**
     * Defines the type of shape for the target
     * @type {String}
     */
    this.flo_element_dest_type = null;
    /**
     * Defines if the flow was followed inmediately
     * @type {Boolean}
     */
    this.flo_is_inmediate = null;
    /**
     * Defines the condition to follow the flow
     * @type {String}
     */
    this.flo_condition = null;
    /**
     * X1 Coordinate
     * @type {Number}
     */
    this.flo_x1 = null;
    /**
     * Y1 Coordinate
     * @type {Number}
     */
    this.flo_y1 = null;
    /**
     * X2 Coordinate
     * @type {Number}
     */
    this.flo_x2 = null;
    /**
     * Y2 Coordinate
     * @type {Number}
     */
    this.flo_y2 = null;
    /**
     * Array of segments that conform the connection
     * @type {Array}
     */
    this.flo_state = null;

    this.label = null;

    AdamFlow.prototype.initObject.call(this, options);
};
AdamFlow.prototype = new jCore.Connection();
/**
* Defines the object type
* @type {String}
*/
AdamFlow.prototype.type = "Connection";  //TODO Replace this type by AdamFlow when jCore will be updated

/**
 * Initialize the object with default values
 * @param {Object} options
 */
AdamFlow.prototype.initObject = function (options) {
    var  defaults = {
        flo_type: 'SEQUENCE',
        flo_is_inmediate: true,
        flo_x1: 0,
        flo_y1: 0,
        flo_x2: 0,
        flo_y2: 0,
        name: ''
    };
    $.extend(true, defaults, options);
    this.setFlowType(defaults.flo_type)
        .setFlowUid(defaults.flo_uid)
        .setIsInmediate(defaults.flo_is_inmediate)
        .setOriginPoint(defaults.flo_x1, defaults.flo_y1)
        .setTargetPoint(defaults.flo_x2, defaults.flo_y2);

    this.setFlowName(defaults.name || null);
    this.setFlowOrigin(defaults.flo_element_origin || null, defaults.flo_element_origin_type || null);
    this.setFlowTarget(defaults.flo_element_dest || null, defaults.flo_element_dest_type || null);
    this.setFlowCondition(defaults.flo_condition || null);
    this.setFlowState(defaults.flo_state || null);
};

/**
 * Returns the flow's name
 * @return {String}
 */
AdamFlow.prototype.getName = function () {
    return this.flo_name;
};

AdamFlow.prototype.setName = function (name) {
    //if (typeof name !== 'undefined') {
    if (name) {
        this.flo_name = name;
    }
    return this;
};

/**
 * Returns the flow conditions
 * @return {String}
 */
AdamFlow.prototype.getFlowCondition = function () {
    return this.flo_condition;
};

/**
 * Defines the unique identiier property
 * @param {String} value
 * @return {*}
 */
AdamFlow.prototype.setFlowUid = function (value) {
    this.flo_uid = value;
    return this;
};

/**
 * Defines the connection type
 * @param {String} type
 * @return {*}
 */
AdamFlow.prototype.setFlowType = function (type) {
    this.flo_type = type;
    return this;
};

/** Return Flow Type
 *
 * @returns {String}
 */
AdamFlow.prototype.getFlowType = function () {
    return this.flo_type;
};

/**
 * Sets the inmediately behavior of the connection
 * @param {Boolean} value
 * @return {*}
 */
AdamFlow.prototype.setIsInmediate = function (value) {
    //if (_.isBoolean(value)) {
    if (value instanceof Boolean) {
        this.flo_is_inmediate = value;
    }
    return this;
};

/**
 * Sets the origin point
 * @param {Number} x
 * @param {Number} y
 * @return {*}
 */
AdamFlow.prototype.setOriginPoint = function (x, y) {
    this.flo_x1 = x;
    this.flo_y1 = y;
    return this;
};

/**
 * Sets the target point
 * @param {Number} x
 * @param {Number} y
 * @return {*}
 */
AdamFlow.prototype.setTargetPoint = function (x, y) {
    this.flo_x2 = x;
    this.flo_y2 = y;
    return this;
};

/**
 * Sets the connection label
 * @param {String} name
 * @return {*}
 */
AdamFlow.prototype.setFlowName = function (name) {
    this.flo_name = name;
    return this;
};

/**
 * Set the shape origin using input data
 * @param {String} code
 * @param {String} type
 * @return {*}
 */
AdamFlow.prototype.setFlowOrigin = function (code, type) {
    this.flo_element_origin = code;
    this.flo_element_origin_type = type;
    return this;
};

/**
 * Set the shape target using input data
 * @param {String} code
 * @param {String} type
 * @return {*}
 */
AdamFlow.prototype.setFlowTarget = function (code, type) {
    this.flo_element_dest = code;
    this.flo_element_dest_type = type;
    return this;
};

/**
 * Sets the flow conditions
 * @param value
 * @return {*}
 */
AdamFlow.prototype.setFlowCondition = function (value) {
    this.flo_condition = value;
    return this;
};

/**
 * Sets the array of segments that conform the connection
 * @param {Array} state
 * @return {*}
 */
AdamFlow.prototype.setFlowState = function (state) {
    this.flo_state = state;
    return this;
};

/**
 * Sets the origin data from a Shape
 * @param {AdamShape} shape
 * @return {*}
 */
AdamFlow.prototype.setOriginShape = function (shape) {
    var data;
    if (shape instanceof AdamShape) {
        data = this.getNativeType(shape);
        this.flo_element_origin = data.code;
        this.flo_element_origin_type = data.type;
    }
    return this;
};

/**
 * Sets the target data from a Shape
 * @param {AdamShape} shape
 * @return {*}
 */
AdamFlow.prototype.setTargetShape = function (shape) {
    var data;
    if (shape instanceof AdamShape) {
        data = this.getNativeType(shape);
        this.flo_element_dest = data.code;
        this.flo_element_dest_type = data.type;
    }
    return this;
};

/**
 * Returns the clean object to be sent to the backend
 * @return {Object}
 */
AdamFlow.prototype.getDBObject = function () {
    var typeMap = {
            regular: 'SEQUENCE',
            segmented: 'MESSAGE',
            dotted: 'ASSOCIATION'
        },
        state = this.getPoints();
    return {
        flo_uid : this.flo_uid,
        flo_type : typeMap[this.segmentStyle],
        flo_name : this.flo_name,
        flo_element_origin : this.flo_element_origin,
        flo_element_origin_type : this.flo_element_origin_type,
        flo_element_dest : this.flo_element_dest,
        flo_element_dest_type : this.flo_element_dest_type,
        flo_is_inmediate : this.flo_is_inmediate,
        flo_condition : this.flo_condition,
        flo_state : state
    };
};

/**
 * Converts the type to be sent to backend
 * @param {AdamShape} shape
 * @return {Object}
 */
AdamFlow.prototype.getNativeType = function (shape) {
    var type = shape.getType(),
        code;
    switch (shape.getType()) {
    case 'AdamActivity':
        type = "bpmnActivity";
        code = shape.act_uid;
        break;
    case 'AdamGateway':
        type = "bpmnGateway";
        code = shape.gat_uid;
        break;
    case 'AdamEvent':
        type = 'bpmnEvent';
        code = shape.evn_uid;
        break;
    case 'AdamArtifact':
        type = "bpmnArtifact";
        code = shape.art_uid;
        break;
    }
    return {
        "type" : type,
        "code" : code
    };
};

AdamFlow.prototype.showMoveHandlers = function () {
    jCore.Connection.prototype.showMoveHandlers.call(this);
    this.canvas.updatedElement = [{
        relatedObject: this
    }];
    $(this.html).trigger('selectelement');

    return this;
};

/**
 * Get Segment Width
 * @returns {Number}
 */
AdamFlow.prototype.getSegmentHeight = function (index) {
    return Math.abs(this.lineSegments.get(index).endPoint.y
        - this.lineSegments.get(index).startPoint.y);
};
/**
 * Get Segment Width
 * @returns {Number}
 */
AdamFlow.prototype.getSegmentWidth = function (index) {
    return Math.abs(this.lineSegments.get(index).endPoint.x
        - this.lineSegments.get(index).startPoint.x);
};
/**
 * Get Label Coordinates
 * @returns {Point}
 */

AdamFlow.prototype.getLabelCoordinates = function () {
    var  x, y, index = 0, diffX, diffY, i, max;
    max = (this.getSegmentWidth(0) > this.getSegmentHeight(0)) ?
            this.getSegmentWidth(0) : this.getSegmentHeight(0);

    for (i = 1; i < this.lineSegments.getSize(); i += 1) {
        diffX = this.getSegmentWidth(i);
        diffY = this.getSegmentHeight(i);
        if (diffX > max + 1) {
            max = diffX;
            index = i;
        } else if (diffY > max + 1) {
            max = diffY;
            index = i;
        }
    }
    diffX = (this.lineSegments.get(index).endPoint.x
        - this.lineSegments.get(index).startPoint.x) / 2;
    diffY = (this.lineSegments.get(index).endPoint.y
        - this.lineSegments.get(index).startPoint.y) / 2;
    x = this.lineSegments.get(index).startPoint.x + diffX;
    y = this.lineSegments.get(index).startPoint.y + diffY;

    return new jCore.Point(x, y);
};

/**
 * Connects two Adam Figures
 * @returns {Connection}
 */
AdamFlow.prototype.connect = function (options) {
    var labelPoint;
    jCore.Connection.prototype.connect.call(this, options);
//    labelPoint = this.getLabelCoordinates();
//
//    this.label = new jCore.Label({
//        message: this.getName(),
//        canvas: this.canvas,
//        parent: this,
//        position: {
//            location: "center",
//            diffX: labelPoint.getX() / this.canvas.zoomFactor,
//            diffY: labelPoint.getY() / this.canvas.zoomFactor
//
//        }
//    });
//    this.html.appendChild(this.label.getHTML());
    return this;
};

AdamFlow.prototype.changeFlowType = function (type) {
    var segmentStyle, destDecorator,
        typeMap = {
            'default': {
                srcPrefix: 'adam-decorator_default',
                destPrefix: 'adam-decorator'
            },
            'conditional': {
                srcPrefix: 'adam-decorator_conditional',
                destPrefix: 'adam-decorator'
            },
            'sequence': {
                srcPrefix: 'adam-decorator',
                destPrefix: 'adam-decorator'
            }
        }, srcDecorator;

    if (type === 'association') {
        segmentStyle = "dotted";
        destDecorator = "con-none";
    } else {
        segmentStyle = "regular";
    }
    this.setSegmentStyle(segmentStyle);
    this.originalSegmentStyle = segmentStyle;

    if (type === 'association') {
        if (srcDecorator &&  this.srcDecorator) {
            this.srcDecorator
                .setDecoratorPrefix(srcDecorator);
        } else {
            this.srcDecorator
                .setDecoratorPrefix("adam-decorator");

        }
        this.srcDecorator.paint();
    } else {
        this.srcDecorator.setDecoratorPrefix(typeMap[type].srcPrefix)
            .setDecoratorType("source")
            .paint();

        this.destDecorator.setDecoratorPrefix(typeMap[type].destPrefix)
            .setDecoratorType("target")
            .paint();
        this.disconnect()
            .connect()
            .setSegmentMoveHandlers()
            .checkAndCreateIntersectionsWithAll();
        return this;
    }


    if (destDecorator && this.srcDecorator) {
        this.destDecorator
            .setDecoratorPrefix(destDecorator);
    } else {
        this.destDecorator
            .setDecoratorPrefix("adam-decorator");
    }
    this.srcDecorator.paint();
    this.disconnect();
    this.connect();
    return this;
};

AdamFlow.prototype.saveAndDestroy = function () {
    jCore.Connection.prototype.saveAndDestroy.call(this);
    if (this.getFlowType() === 'DEFAULT') {
        this.getSrcPort().getParent().updateDefaultFlow("");
    }
};

/*global jCore,
 */
/**
 * @class CommandAdam
 * Extends jCore.Command to handle changes of custom shapes
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} receiver
 * @param {Array} propertyNames
 * @param {Array} newValues
 *
 */
var CommandAdam = function (receiver, propertyNames, newValues) {
    var oldValues = [],
        i;
    jCore.Command.call(this, receiver);
    for (i = 0; i < propertyNames.length; i += 1) {
        oldValues.push(receiver[propertyNames[i]]);
    }
    /**
     * Defines the old values
     * @type {Array}
     * @private
     */
    this.oldValues = oldValues;
    /**
     * Defines the new values
     * @type {Array}
     * @private
     */
    this.newValues = newValues;
    /**
     * Define the property names
     * @type {Array}
     * @private
     */
    this.propertyNames = propertyNames;
};
CommandAdam.prototype = new jCore.Command();
/**
 * Define the object type
 * @type {String}
 */
CommandAdam.prototype.type = 'CommandAdam';
/**
 * Execute the command
 */
CommandAdam.prototype.execute = function () {
    var e;
    for (e = 0;  e < this.newValues.length; e += 1) {
        this.receiver[this.propertyNames[e]] = this.newValues[e];
    }
    this.canvas.triggerCommandAdam(this.receiver, this.propertyNames, this.oldValues, this.newValues);
};
/**
 * Execute de UNDO action
 */
CommandAdam.prototype.undo = function () {
    var e;
    for (e = 0;  e < this.newValues.length; e += 1) {
        this.receiver[this.propertyNames[e]] = this.oldValues[e];
    }
    this.canvas.triggerCommandAdam(this.receiver, this.propertyNames, this.newValues, this.oldValues);
};
/**
 * Execute de REDO action
 */
CommandAdam.prototype.redo = function () {
    this.execute();
};

var AdamShapeLayerCommand = function (receiver, options) {
    jCore.Command.call(this, receiver);
    this.layers = [];
    this.updateType = null;
    this.beforeStyle = [];
    this.beforeValues = [];
    this.afterStyle = [];
    this.afterValues = [];
    this.propertyNames = [];
    AdamShapeLayerCommand.prototype.initObject.call(this, receiver, options);
};

AdamShapeLayerCommand.prototype = new jCore.Command();

AdamShapeLayerCommand.prototype.type = "AdamShapeLayerCommand";

AdamShapeLayerCommand.prototype.initObject = function (receiver, options) {
    var i, newZoom, css, marker, type;
    this.updateType = options.type;
    this.layers = options.layers;
    switch (this.updateType) {
    case 'changetypegateway':

        this.beforeStyle.push(this.layers[0].zoomSprites);
        newZoom = [];
        for (i = 0; i < this.beforeStyle[0].length; i += 1) {
            newZoom.push('adam-shape-' + ((i * 25) + 50) + '-gateway-' + options.changes.toLowerCase());
        }
        this.afterStyle.push(newZoom);
        this.propertyNames.push('gat_type');
        this.beforeValues.push(receiver['gat_type']);
        this.afterValues.push(options.changes);
        if (parseInt(receiver['gat_default_flow']) !== 0 &&
            (options.changes === 'PARALLEL'
                || options.changes === 'EVENTBASED')) {

            this.propertyNames.push('gat_default_flow');
            this.beforeValues.push(receiver['gat_default_flow']);
            this.afterValues.push(0);
        }
        if (options.changes === 'EVENTBASED') {
            this.propertyNames.push('gat_direction');
            this.beforeValues.push(receiver['gat_direction']);
            this.afterValues.push('UNSPECIFIED');
        }

        break;
    case 'changeeventmarker':
        this.beforeStyle.push(this.layers[0].zoomSprites);
        newZoom = [];
        marker = (options.changes.evn_message  && (options.changes.evn_message !== "")) ? options.changes.evn_message : options.changes.evn_marker;
        type = (this.receiver.evn_type === "BOUNDARY") ? 'INTERMEDIATE' : this.receiver.evn_type;
        for (i = 0; i < this.beforeStyle[0].length; i += 1) {
            css = 'adam-marker-' + ((i * 25) + 50) + '-' + type.toLowerCase();
            css += '-' + options.changes.evn_behavior.toLowerCase() + '-';
            css += marker.toLowerCase();
            newZoom.push(css);
        }
        this.afterStyle.push(newZoom);
        if (typeof options.changes.evn_behavior !== 'undefined') {
            this.propertyNames.push('evn_behavior');
            this.beforeValues.push(this.receiver['evn_behavior']);
            this.afterValues.push(options.changes.evn_behavior);
        }
        if (typeof options.changes.evn_marker !== 'undefined') {
            this.propertyNames.push('evn_marker');
            this.beforeValues.push(this.receiver['evn_marker']);
            this.afterValues.push(options.changes.evn_marker);
        }

        this.propertyNames.push('evn_message');
        this.beforeValues.push(this.receiver['evn_message']);
        this.afterValues.push(options.changes.evn_message || '');

        break;
    case 'changeeventtype':
        this.beforeStyle.push(this.layers[0].zoomSprites);
        newZoom = [];
        for (i = 0; i < this.beforeStyle[0].length; i += 1) {
            newZoom.push('adam-shape-' + ((i * 25) + 50) + '-event-' + options.changes.evn_type.toLowerCase());
        }
        this.afterStyle.push(newZoom);
        newZoom = [];
        this.beforeStyle.push(this.layers[1].zoomSprites);
        for (i = 0; i < this.beforeStyle[1].length; i += 1) {
            css = 'adam-marker-' + ((i * 25) + 50) + '-';
            css += options.changes.evn_type.toLowerCase() + '-';
            css += options.changes.evn_behavior.toLowerCase() + '-';
            css += options.changes.evn_marker.toLowerCase();
            newZoom.push(css);
        }
        this.afterStyle.push(newZoom);
        if (typeof options.changes.evn_type !== 'undefined') {
            this.propertyNames.push('evn_type');
            this.beforeValues.push(this.receiver['evn_type']);
            this.afterValues.push(options.changes.evn_type);
        }
        if (typeof options.changes.evn_behavior !== 'undefined') {
            this.propertyNames.push('evn_behavior');
            this.beforeValues.push(this.receiver['evn_behavior']);
            this.afterValues.push(options.changes.evn_behavior);
        }
        if (typeof options.changes.evn_marker !== 'undefined') {
            this.propertyNames.push('evn_marker');
            this.beforeValues.push(this.receiver['evn_marker']);
            this.afterValues.push(options.changes.evn_marker);
        }
        if (typeof options.changes.evn_message !== 'undefined') {
            this.propertyNames.push('evn_message');
            this.beforeValues.push(this.receiver['evn_message']);
            this.afterValues.push(options.changes.evn_message);
        }

        break;
    case 'changescripttypeactivity':
        this.beforeStyle.push(this.layers[0].zoomSprites);
        newZoom = [];
        for (i = 0; i < this.beforeStyle[0].length; i += 1) {
            newZoom.push('adam-shape-' + ((i * 25) + 50) + '-activity-scripttask-' + options.changes.toLowerCase());
        }
        this.afterStyle.push(newZoom);
        this.propertyNames.push('act_script_type');
        this.beforeValues.push(receiver['act_script_type']);
        this.afterValues.push(options.changes);
        break;
    }
};

AdamShapeLayerCommand.prototype.switchDefaultFlow = function (id, newVal, oldVal) {
    var connection, updatedElement;
    connection = this.receiver.canvas.connections.find('id', id);
    connection.changeFlowType(newVal.toLowerCase());
    connection.setFlowType(newVal);
    updatedElement = [{
        id: connection.getID(),
        type: connection.type,
        relatedObject: connection,
        fields: [{
            field: "type",
            newVal: newVal,
            oldVal: oldVal
        }]
    }];
    this.receiver.getCanvas().triggerDefaultFlowChangeEvent(updatedElement);
};

AdamShapeLayerCommand.prototype.execute = function () {
    var i;
    for (i = 0; i < this.layers.length; i += 1) {
        this.layers[i].zoomSprites = this.afterStyle[i];
        this.layers[i].paint();
    }
    for (i = 0; i < this.propertyNames.length; i += 1) {
        this.receiver[this.propertyNames[i]] = this.afterValues[i];
        if (this.propertyNames[i] === 'gat_default_flow') {
            this.switchDefaultFlow(this.beforeValues[i], 'SEQUENCE', 'DEFAULT');
        }
        if (this.receiver.getType() === 'AdamGateway') {
            this.receiver.updateFlowConditions();
        }

    }
    this.canvas.triggerCommandAdam(this.receiver, this.propertyNames, this.beforeValues, this.afterValues);
};

AdamShapeLayerCommand.prototype.undo = function () {
    var i;
    for (i = 0; i < this.layers.length; i += 1) {
        this.layers[i].zoomSprites = this.beforeStyle[i];
        this.layers[i].paint();
    }
    for (i = 0; i < this.propertyNames.length; i += 1) {
        this.receiver[this.propertyNames[i]] = this.beforeValues[i];
        if (this.propertyNames[i] === 'gat_default_flow') {
            this.switchDefaultFlow(this.beforeValues[i], 'DEFAULT', 'SEQUENCE');
        }
    }
    this.canvas.triggerCommandAdam(this.receiver, this.propertyNames, this.afterValues, this.beforeValues);
};

AdamShapeLayerCommand.prototype.redo = function () {
    this.execute();
};

var AdamShapeMarkerCommand = function (receiver, options) {
    jCore.Command.call(this, receiver);

    this.updateType = null;
    this.markers = [];
    this.beforeMarkerStyle = [];
    this.beforeMarkerType = [];
    this.afterMarkerStyle = [];
    this.afterMarkerType = [];
    this.beforeValues = [];
    this.afterValues = [];
    this.propertyNames = [];

    AdamShapeMarkerCommand.prototype.initObject.call(this, receiver, options);
};

AdamShapeMarkerCommand.prototype = new jCore.Command();

AdamShapeMarkerCommand.prototype.type = 'AdamShapeMarkerCommand';

AdamShapeMarkerCommand.prototype.initObject = function (receiver, options) {
    var i, newZoom;

    this.updateType = options.type;
    this.markers = options.markers;

    switch (this.updateType) {
    case 'changeactivitymarker':
        this.beforeMarkerStyle.push(this.markers[0].markerZoomClasses);
        newZoom = [];
        for (i = 0; i < this.beforeMarkerStyle[0].length; i += 1) {
            newZoom.push('adam-marker-' + ((i * 25) + 50) + '-' + options.changes.taskType.toLowerCase());
        }
        this.afterMarkerStyle.push(newZoom);
        this.beforeMarkerType.push(this.receiver['act_task_type']);
        this.afterMarkerType.push(options.changes.taskType);

        this.propertyNames.push('act_task_type');
        this.beforeValues.push(this.receiver['act_task_type']);
        this.afterValues.push(options.changes.taskType);

        break;
    }
};

AdamShapeMarkerCommand.prototype.execute = function () {
    var i;

    for (i = 0; i < this.markers.length; i += 1) {
        this.markers[i].markerType = this.afterMarkerType[i];
        this.markers[i].markerZoomClasses = this.afterMarkerStyle[i];
        this.markers[i].paint(true);
    }
    for (i = 0; i < this.propertyNames.length; i += 1) {
        this.receiver[this.propertyNames[i]] = this.afterValues[i];
    }
    this.canvas.triggerCommandAdam(this.receiver, this.propertyNames, this.beforeValues, this.afterValues);
};

AdamShapeMarkerCommand.prototype.undo = function () {
    var i;
    for (i = 0; i < this.markers.length; i += 1) {
        this.markers[i].markerType = this.beforeMarkerType[i];
        this.markers[i].markerZoomClasses = this.beforeMarkerStyle[i];
        this.markers[i].paint(true);
    }
    for (i = 0; i < this.propertyNames.length; i += 1) {
        this.receiver[this.propertyNames[i]] = this.beforeValues[i];
    }
    this.canvas.triggerCommandAdam(this.receiver, this.propertyNames, this.afterValues, this.beforeValues);
};

AdamShapeMarkerCommand.prototype.redo = function () {
    this.execute();
};


/*global jCore,

*/
var CommandAnnotationResize = function (receiver) {
    jCore.CommandResize.call(this, receiver);
};

CommandAnnotationResize.prototype.type = 'commandAnnotationResize';

CommandAnnotationResize.prototype.execute = function () {
    jCore.CommandResize.prototype.execute.call(this);
    //this.receiver.graphics.clear();
    this.receiver.paint();
};

CommandAnnotationResize.prototype.undo = function () {
    jCore.CommandResize.prototype.undo.call(this);
    //this.receiver.graphics.clear();
    this.receiver.paint();
};

CommandAnnotationResize.prototype.redo = function () {
    this.execute();
};

/*global jCore

 */
var CommandSingleProperty = function (receiver, options) {
    jCore.Command.call(this, receiver);
    this.propertyName = null;
    this.before = null;
    this.after = null;
    CommandSingleProperty.prototype.initObject.call(this, options);
};

CommandSingleProperty.prototype = new jCore.Command();

CommandSingleProperty.prototype.type = "commandSingleProperty";

CommandSingleProperty.prototype.initObject = function (options) {
    this.propertyName = options.propertyName;
    this.before = options.before;
    this.after = options.after;
};

CommandSingleProperty.prototype.execute = function () {
    this.receiver[this.propertyName] = this.after;
    this.receiver.canvas.triggerCommandAdam(this.receiver, [this.propertyName], [this.before], [this.after]);
    this.receiver.canvas.bpmnValidation();
};

CommandSingleProperty.prototype.undo = function () {
    this.receiver[this.propertyName] = this.before;
    this.receiver.canvas.triggerCommandAdam(this.receiver, [this.propertyName], [this.after], [this.before]);
    this.receiver.canvas.bpmnValidation();
};

CommandSingleProperty.prototype.redo = function () {
    this.execute();
};

/*global jCore

 */
/**
 * @class AdamActivityContainerBehavior
 * Handle the behavior when an activity acts like a container
 *
 * @constructor
 * Create a new instance of the object
 */
var AdamActivityContainerBehavior = function () {};
AdamActivityContainerBehavior.prototype = new jCore.RegularContainerBehavior();
/**
 * Defines the object type
 * @type {String}
 */
AdamActivityContainerBehavior.prototype.type = "AdamActivityContainerBehavior";

/**
 * Adds a shape into the container
 * @param {AdamActivity} container
 * @param {AdamEvent} shape
 * @param {Number} x
 * @param {Number} y
 * @param {Number} topLeftCorner
 */
AdamActivityContainerBehavior.prototype.addToContainer = function (container,
                                                               shape, x, y,
                                                               topLeftCorner) {
    var shapeLeft = 0,
        shapeTop = 0,
        shapeWidth,
        shapeHeight,
        canvas,
        topLeftFactor = (topLeftCorner === true) ? 0 : 1;

    if (container.family === "Canvas") {
        canvas = container;
    } else {
        canvas = container.canvas;
    }

    shapeWidth = shape.getZoomWidth();
    shapeHeight = shape.getZoomHeight();

    shapeLeft += x - (shapeWidth / 2) * topLeftFactor;
    shapeTop += y - (shapeHeight / 2) * topLeftFactor;

    shapeLeft /= container.zoomFactor;
    shapeTop /= container.zoomFactor;

    shape.setParent(container);
    container.getChildren().insert(shape);
    this.addShape(container, shape, shapeLeft, shapeTop);

    // fix the zIndex of this shape and it's children
    shape.fixZIndex(shape, 0);


    // adds the shape to either the customShape arrayList or the regularShapes
    // arrayList if possible
    canvas.addToList(shape);

    //setting boundary position
    if (shape.getType() === 'AdamEvent' && shape.evn_type === 'BOUNDARY') {
        shape.setAttachedTo(container.act_uid);
        container.boundaryArray.insert(shape);
        if (container.boundaryPlaces.isEmpty()) {
            container.makeBoundaryPlaces(shape);
        }
        shape.attachToActivity();
    }
    //container.updateDimensions(10);
};
/*global jCore, $ */
/**
 * @class AdamActivityResizeBehavior
 * Defines the resize behavior for the activities
 *
 * @constructor
 * Create a new Resize Behavior object
 */
var AdamActivityResizeBehavior = function () {
};
AdamActivityResizeBehavior.prototype = new jCore.RegularResizeBehavior();
/**
 * Defines the object type
 * @type {String}
 */
AdamActivityResizeBehavior.prototype.type = "AdamActivityResizeBehavior";


/**
 * Sets a shape's container to a given container
 * @param container
 * @param shape
 */
AdamActivityResizeBehavior.prototype.onResizeStart = function (shape) {
    return function (e, ui) {
        var zoomFactor = shape.canvas.getZoomFactor();
        $(this).resizable("option", "minHeight", shape.getMinHeight() * zoomFactor);
        $(this).resizable("option", "minWidth", shape.getMinWidth() * zoomFactor);

        $(this).resizable("option", "maxHeight", shape.getMaxHeight() * zoomFactor);
        $(this).resizable("option", "maxWidth", shape.getMaxWidth() * zoomFactor);

        shape.canvas.hideAllFocusedLabels();
        jCore.RegularResizeBehavior
            .prototype.onResizeStart.call(this, shape);

    };
};
/**
 * Removes shape from its current container
 * @param shape
 */
AdamActivityResizeBehavior.prototype.onResize = function (shape) {
    //RegularResizeBehavior.prototype.onResize.call(this, shape);

    return function (e, ui) {
        var i,
            port,
            canvas = shape.canvas;
        shape.setPosition(ui.position.left / canvas.zoomFactor,
            ui.position.top / canvas.zoomFactor);
        shape.setDimension(ui.size.width / canvas.zoomFactor,
            ui.size.height / canvas.zoomFactor);

        // fix the position of the shape's ports (and the positions and port
        // position of its children)
        // parameters (shape, resizing, root)
        shape.fixConnectionsOnResize(shape.resizing, true);

        // fix the labels positions on resize (on x = true and y = true)
        shape.updateLabelsPosition(true, true);
        //shape.updateBoundaryPositions(false);

        for (i = 0; i < shape.markersArray.getSize(); i += 1) {
            shape.markersArray.get(i).paint();
        }

    };

};

/**
 * Adds a shape to a given container
 * @param container
 * @param shape
 */
AdamActivityResizeBehavior.prototype.onResizeEnd = function (shape) {
    return function (e, ui) {
        var i, size, port, canvas = shape.canvas;
        jCore.RegularResizeBehavior.prototype.onResizeEnd.call(this, shape)(e, ui);

        for (i = 0, size = shape.getPorts().getSize(); i < size; i += 1) {
            port = shape.getPorts().get(i);
            //canvas.triggerUpdatePortPositionEvent(port);
            canvas.triggerPortChangeEvent(port);
        }
        //shape.label.setLabelPosition(shape.label.location, shape.label.diffX, shape.label.diffY);
        //shape.fixConnectionsOnResize(shape.resizing, true);
        //shape.refreshChildrenPositions(true);
        //shape.refreshConnections(false, true);
       // shape.showAllChilds();
    };

};

/**
 * Updates the min height and max height of the JQqueryUI's resizable plugin
 * @param shape
 */
AdamActivityResizeBehavior.prototype.updateResizeMinimums = function (shape) {
    var minW,
        minH,
        children = shape.getChildren(),
    //limits = children.getDimensionLimit(),
        limits,
        margin = 15,
        $shape = $(shape.getHTML()),
        i,
        child,
        childWithoutBoundaries = new jCore.ArrayList();

    for (i = 0; i < children.getSize(); i += 1) {
        child = children.get(i);
        if (!(child.type === 'AdamEvent' && child.evn_type === 'BOUNDARY')) {
            childWithoutBoundaries.insert(child);
        }
    }
    limits = childWithoutBoundaries.getDimensionLimit();
        // TODO: consider the labels width and height
//    if (subProcess.label.orientation === 'vertical') {
//        minW = Math.max(limits[1], Math.max(labelH, subProcess.label.height)) +
//            margin + 8;
//        minH = Math.max(limits[2], Math.max(labelW, subProcess.label.width)) +
//            margin;
//    } else {
//        minW = Math.max(limits[1], Math.max(labelW, subProcess.label.width)) +
//            margin;
//        minH = Math.max(limits[2], Math.max(labelH, subProcess.label.height)) +
//            margin + 8;
//    }

    minW = limits[1] + margin;
    minH = limits[2] + margin;

    // update jQueryUI's minWidth and minHeight
    $shape.resizable('option', 'minWidth', minW);
    $shape.resizable('option', 'minHeight', minH);
};
/*global SUGAR_URL, $, AdamCanvas, RestClient, window, setInterval, PropertiesGrid,
 AdamEvent, AdamActivity, AdamGateway, AdamArtifact, AdamFlow, setSelectedNode,
 CommandSingleProperty, CommandAnnotationResize, CommandConnectionCondition, MessagePanel,
 jCore, translate
*/
/**
 * @class AdamProject
 * This class represents a Sugar Project
 *
 * @constructor
 * Create a new version of the class
 */
var callbackCS;
var AdamProject = function (settings) {
    /**
     * Unique Identifier for the project
     * @type {String}
     */
    this.uid = null;
    /**
     * Project Name
     * @type {String}
     */
    this.name = null;
    /**
     * Project Description
     * @type {String}
     */
    this.description = null;
    /**
     * Canvas associated to the project
     * @type {AdamCanvas}
     */
    this.canvas = null;
    /**
     * REST End Point associated to this project
     * @type {String}
     */
    this.url = '/rest/v10/Project/';
    /**
     * RestClient library object
     * @type {Object}
     */
    this.restClient = null;
    /**
     * Stores if the project was loaded correctly
     * @type {Boolean}
     */
    this.loaded = false;
    /**
     * Stores if the project has elements without save
     * @type {Boolean}
     */
    this.isDirty = false;
    /**
     * Stores id the  project is waiting for a response
     * @type {Boolean}
     */
    this.isWaitingResponse = false;
    /**
     * Stores the interval of time for auto save feature
     * @type {Number}
     */
    this.saveInterval = 30000;
    this.showWarning = false;
    /**
     * Object Structure to save elements without save
     * @type {Array}
     */
    this.dirtyElements = [
        {
            activities: {},
            gateways: {},
            events: {},
            artifacts: {},
            flows: {}

        },
        {
            activities: {},
            gateways: {},
            events: {},
            artifacts: {},
            flows: {}
        }
    ];
    /**
     * Grid that contains the current selected element's properties
     * @type {Object} This object is a used from the jquery propi plugin
     */
    this.propertiesGrid = null;
    /**
     * Object that contains project's metadata.
     * @type {Object}
     */
    this._metadata = {};

    this.process_definition = {};
    AdamProject.prototype.preinit.call(this, settings);
};
/**
 * Object type
 * @type {String}
 */
AdamProject.prototype.type = "AdamProject";
/**
 * Initializes the AdamProject.
 */
AdamProject.prototype.preinit = function (settings) {
    var defaults = {
        metadata: []
    };

    jQuery.extend(true, defaults, settings);

    this.setMetadata(defaults.metadata);
};
/**
 * Returns the project uid
 * @return {String}
 */
AdamProject.prototype.getUid = function () {
    return this.uid;
};
/**
 * Sets the project uid
 * @param value
 */
AdamProject.prototype.setUid = function (value) {
    this.uid = value;
};
/**
 * Sets the project name
 * @param value
 */
AdamProject.prototype.setName = function (value) {
    var $title, $title_box;
    this.name = value;
    $title = $('#ProjectTitle');
    $title_box = $('#txt-title');
    $title.html(value);
    $title_box.html(value);
    //console.log("name", value);
};
/**
 * Sets the project description
 * @param  {String} description [description]
 */
AdamProject.prototype.setDescription = function (description) {
    this.description = description;
    return this;
};
/**
 * Sets the canvas related to the project
 * @param {AdamCanvas} value
 * @return {*}
 */
AdamProject.prototype.setCanvas = function (value) {
    if (value instanceof AdamCanvas) {
        this.canvas = value;
        this.canvas.setProject(this);
        $(this.canvas.html).on('selectelement', this.onSelectElementHandler(this.canvas));
    } else {
        this.canvas = null;
    }
    return this;
};
/**
 * Sets the RestClient object associated to this project
 * @param {Object} rc
 * @return {*}
 */
AdamProject.prototype.setRestClient = function (rc) {
    if (rc instanceof RestClient) {
        this.restClient = rc;
    }
    return this;
};
/**
 * Sets the REST url associated to this project
 * @param {String} url
 * @return {*}
 */
AdamProject.prototype.setRestURL = function (url) {
    this.url = url;
    return this;
};
/**
 * Sets the time interval used to save automatically
 * @param {Number} interval Expressed in miliseconds
 * @return {*}
 */
AdamProject.prototype.setSaveInterval = function (interval) {
    this.saveInterval = interval;
    return this;
};
/**
 * Loads the project through a REST call
 * @param {String} id
 * @return {Boolean}
 */
AdamProject.prototype.load = function (id, callback) {
    var status = false,
        self = this,
        url,
        attributes = {};
    if (typeof id !== 'undefined') {
        this.uid = id;
    }
    url = App.api.buildURL("pmse_Project/project/" + this.uid, null, null);
    attributes = {};

    App.api.call('read', url, attributes, {
        success: function (data) {
//            console.log(data);
            self.loadProject(data);
            status = true;
//            console.log(success);
            if (callback && callback.success) {
                callback.success.call(this, data);
            }    
            if (canvas){
                canvas.bpmnValidation();
                //jQuery(".pane.ui-layout-center").append(countErrors);
            }
        },
        error: function (err) {
            //TODO Process HERE error at loading project
        }
        /*if (canvas) {
            
            console.log(1);
        }*/
    });
    return status;
};
/**
 * Initialize the project cycle: saving and updates
 */
AdamProject.prototype.init = function () {
    var self;
    self = this;
    if (this.loaded) {
        window.onbeforeunload = function () {
            if (self.isDirty && !self.showWarning) {
                return true;
            }
        };
        setInterval(function () {
            self.save();
        }, this.saveInterval);
        this.propertiesGrid = new PropertiesGrid('#properties-grid');
        this.canvas.commandStack.setHandler(AdamProject.prototype.updateUndoRedo);
    }
};

/**
 * Save project if is dirty and is not waiting response
 */
AdamProject.prototype.save = function () {
    var self = this,
        url,
        attributes = {};
    if (this.isDirty && !this.isWaitingResponse) {
        this.isWaitingResponse = true;
        url = App.api.buildURL("pmse_Project/project/" + this.uid, null, null);
        //console.log(url);
        attributes = {
            data: this.getDirtyObject(),
            id:this.uid,
            operation : "update",
            wrapper: "Project"
    };
        App.api.call('update', url, attributes, {
            success: function (data) {
                self.isWaitingResponse = false;
                if (data.success) {
                    self.updateDirtyProject();
                } else {
                    self.mergeDirtyElements();
                    //TODO Process HERE the failure at saving time
                }
            },
            error: function (err) {
                self.isWaitingResponse = false;
                self.mergeDirtyElements();
                self.isDirty = false;
                //TODO Process HERE the failure at saving time
            }
        });
    }
};

/**
 * Loads the project from a JSON response
 * @param {Object} response
 * @private
 */
AdamProject.prototype.loadProject = function (response) {
    var diagram, i, result;
    if (response.project) {
        diagram = response.project.diagram[0];
        this.setName(response.project.prj_name);
        this.setDescription(response.project.prj_description);

        this.process_definition.pro_module = response.project.process_definition.pro_module;
        this.process_definition.pro_status = response.project.process_definition.pro_status;
        this.process_definition.pro_locked_variables = response.project.process_definition.pro_locked_variables;
        this.process_definition.pro_terminate_variables = response.project.process_definition.pro_terminate_variables;

        this.canvas.setDiaUid(diagram.dia_uid);

        for (i = 0; i < diagram.activities.length; i += 1) {
            this.loadShape(diagram.activities[i], 'AdamActivity');
        }

        for (i = 0; i < diagram.events.length; i += 1) {
            this.loadShape(diagram.events[i], 'AdamEvent');
        }

        for (i = 0; i < diagram.gateways.length; i += 1) {
            this.loadShape(diagram.gateways[i], 'AdamGateway');
        }

        for (i = 0; i < diagram.artifacts.length; i += 1) {
            this.loadShape(diagram.artifacts[i], 'AdamArtifact');
        }

        for (i = 0; i < diagram.flows.length; i += 1) {
            this.loadFlow(diagram.flows[i]);
        }

        this.loaded = true;
    } else {
        this.loaded = false;
    }
};
/**
 * Add Element to the list of unsaved elements
 * @param {Object} element
 * @private
 */
AdamProject.prototype.addElement = function (element) {
    var object,
        pk_name,
        list,
        i,
        pasteElement;
    if (element.relatedElements.length > 0) {
        for (i = 0; i < element.relatedElements.length; i += 1) {
            pasteElement = element.relatedElements[i];
            list = this.getUpdateList(pasteElement.type);
            object = pasteElement.getDBObject();
            object.action = "CREATE";
            list[pasteElement.id] = object;
        }
    } else {
        pk_name = this.formatProperty(element.type, 'uid');
        list = this.getUpdateList(element.type);
        element.relatedObject[pk_name] = element.id;
        object = element.relatedObject.getDBObject();
        object.action = "CREATE";
        list[element.id] = object;
    }
    this.isDirty = true;
    this.updateToolbar();
};
/**
 * Removes element(s) for the unsaved list
 * @param {Array} updateElement
 * @private
 */
AdamProject.prototype.removeElement = function (updateElement) {
    var object,
        dirtyEmptyCounter,
        element,
        i,
        pk_name,
        list,
        emptyObject = {};
    for (i = 0; i < updateElement.length; i += 1) {
        element = updateElement[i];

        pk_name = this.formatProperty(element.type, 'uid');
        list = this.getUpdateList(element.type);
        if (list[element.id]) {
            if (list[element.id].action === 'CREATE') {
                delete list[element.id];
            } else {
                list[element.id].action = 'REMOVE';
            }
        } else {
            object = {action: 'REMOVE'};
            object[pk_name] = element.id;
            list[element.id] = object;
        }
    }
    this.isDirty = true;
    if (!this.isWaitingResponse) {
        dirtyEmptyCounter = true;
        dirtyEmptyCounter = dirtyEmptyCounter && (this.dirtyElements[0].activities === emptyObject);
        dirtyEmptyCounter = dirtyEmptyCounter && (this.dirtyElements[0].gateways === emptyObject);
        dirtyEmptyCounter = dirtyEmptyCounter && (this.dirtyElements[0].events === emptyObject);
        dirtyEmptyCounter = dirtyEmptyCounter && (this.dirtyElements[0].artifacts === emptyObject);
        dirtyEmptyCounter = dirtyEmptyCounter && (this.dirtyElements[0].flows === emptyObject);
        if (dirtyEmptyCounter) {
            this.isDirty = false;
        }
    }
    this.updateToolbar();
};
/**
 * Updates the information of the unsaved elements
 * @param {Array} updateElement
 * @private
 */
AdamProject.prototype.updateElement = function (updateElement) {
    var element,
        i,
        shape,
        object,
        list;
    for (i = 0; i < updateElement.length; i += 1) {
        element = updateElement[i];
        shape = element.relatedObject;

        object = this.formatObject(element);
        list = this.getUpdateList(element.type);
        if (list[element.id]) {
            $.extend(true, list[element.id], object);
            if (element.type === 'Connection') {
                list[element.id].flo_state = object.flo_state;
            }
        } else {
            object.action = "UPDATE";
            list[element.id] = object;
        }
    }
    this.isDirty = true;
    this.updateToolbar();
};
/**
 * Gets the dirty object to send through REST
 * @return {Object}
 * @private
 */
AdamProject.prototype.getDirtyObject = function () {
    var dirtyObj;
    dirtyObj = this.dirtyElements[0];
    dirtyObj.prj_uid = this.getUid();
    return dirtyObj;
};
/**
 * Merge the actual and temporal dirty element arrays
 * @private
 */
AdamProject.prototype.mergeDirtyElements = function () {
    //TODO Merge Dirty Elements Array and Clean TMP Dirty Object
    this.updateToolbar();
};
/**
 * Update the actual and temporal dirty element arrays
 * @private
 */
AdamProject.prototype.updateDirtyProject = function () {
    this.isDirty = false;
    //this.dirtyElements[0] = {};
    this.dirtyElements[0] = this.dirtyElements[1];
    this.dirtyElements[1] = {
        activities: {},
        events: {},
        gateways: {},
        flows: {},
        artifacts: {}
    };
    this.updateToolbar();
};
/**
 * Gets the changed fields from an object
 * @param {Object} element
 * @return {Object}
 */
AdamProject.prototype.formatObject = function (element) {
    var i,
        field,
        formattedElement = {},
        property;
    formattedElement[this.formatProperty(element.type, 'uid')] = element.id;

    if (element.adam) {
        for (i = 0; i < element.fields.length;  i += 1) {
            field = element.fields[i];
            formattedElement[field.field] = field.newVal;
        }
    } else {
        for (i = 0; i < element.fields.length;  i += 1) {
            field = element.fields[i];
            property = this.formatProperty(element.type, field.field);
            if (property === "element_uid") {
                field.newVal = field.newVal.id;
            } //else if (property === "bou_x") {
//                field.newVal = element.relatedObject.absoluteX;
//            } else if (property === "bou_y") {
//                field.newVal = element.relatedObject.absoluteY;
//            }
            formattedElement[property] = field.newVal;
        }
    }

    return formattedElement;
};
/**
 * Returns the list where the element will be added/updated
 * @param {String} type
 * @return {*}
 * @private
 */
AdamProject.prototype.getUpdateList = function (type) {
    var listName = {
            "AdamActivity" : "activities",
            "AdamGateway" : "gateways",
            "AdamEvent" : "events",
            "AdamFlow" : "flows",
            "AdamArtifact" : "artifacts",
            "Connection" : "flows"
        },
        dirtyArray;
    dirtyArray = (this.isWaitingResponse) ? 1 : 0;
    return this.dirtyElements[dirtyArray][listName[type]];
};
/**
 * Returns the field name formated
 * @param {String} type Object Type
 * @param {String }property  Property name
 * @return {String}
 */
AdamProject.prototype.formatProperty = function (type, property) {
    var prefixes = {
            "AdamActivity" : "act",
            "AdamGateway" : "gat",
            "AdamEvent" : "evn",
            "AdamArtifact" : "art"
        },
        map = {
            // map for shapes
            x: "bou_x",
            y: "bou_y",
            width: "bou_width",
            height: "bou_height"
        },
        out;

    if (type === "AdamFlow" || type === 'Connection') {
        out = "flo_" + property;
    } else if (map[property]) {
        out = map[property];
    } else {
        out = prefixes[type] + '_' + property;
    }
    return out;
};

/**
 * Loads the AdamShape into the diagram
 * @param {Object} shape
 * @param {String} type
 */
AdamProject.prototype.loadShape = function (shape, type) {
    var customShape, shapeType, behavior, message, uid, marker, direction, addShape = true, activity;
    switch (type) {
    case 'AdamEvent':
        uid = shape.evn_uid;
        shapeType = shape.evn_type.toLowerCase();
        if (shapeType === 'boundary') {
            shapeType = 'intermediate';
        }
        behavior = shape.evn_behavior.toLowerCase();
        //behavior = shape.evn_behavior;
        message = shape.evn_message;
        marker = message || shape.evn_marker;
        marker = marker.toLowerCase();
        customShape = new AdamEvent({
            id: uid,
            canvas : this.canvas,
            width : 33,
            height : 33,
            style: {
                cssClasses: [""]
            },
            labels: [{
                message: shape.evn_name || '',
                position : {
                    location : "bottom",
                    diffX : 0,
                    diffY : 0
                }
            }],
            layers: [
                {
                    layerName : "first-layer",
                    priority: 2,
                    visible: true,
                    style: {
                        cssClasses: []
                    },
                    zoomSprites : [
                        'adam-shape-50-event-' + shapeType,
                        'adam-shape-75-event-' + shapeType,
                        'adam-shape-100-event-' + shapeType,
                        'adam-shape-125-event-' + shapeType,
                        'adam-shape-150-event-' + shapeType
                    ]
                },
                {
                    layerName : "second-layer",
                    priority: 3,
                    visible: true,
                    style: {
                        cssClasses: []
                    },
                    zoomSprites : [
                        'adam-marker-50-' + shapeType  + '-' + behavior + '-' + marker,
                        'adam-marker-75-' + shapeType  + '-' + behavior + '-' + marker,
                        'adam-marker-100-' + shapeType  + '-' + behavior + '-' + marker,
                        'adam-marker-125-' + shapeType  + '-' + behavior + '-' + marker,
                        'adam-marker-150-' + shapeType  + '-' + behavior + '-' + marker
                    ]
                }
            ],
            drag: 'customshapedrag',
            resizeHandlers: {
                type: "Rectangle",
                total: 4,
                resizableStyle: {
                    cssProperties: {
                        'background-color': "rgb(0, 255, 0)",
                        'border': '1px solid black'
                    }
                },
                nonResizableStyle: {
                    cssProperties: {
                        'background-color': "white",
                        'border': '1px solid black'
                    }
                }
            },
            drop : {type: 'connection'},
            evn_name: shape.evn_name,
            evn_type: shape.evn_type.toLowerCase(),
            evn_marker: shape.evn_marker,
            evn_behavior: behavior,
            evn_message: message,
            evn_uid: uid
        });
        if (shape.evn_type === 'BOUNDARY') {
            addShape = false;
            activity = this.canvas.getCustomShapes().find('id', shape.evn_attached_to);
            if (activity) {
                activity.activityContainerBehavior.addToContainer(
                    activity,
                    customShape,
                    parseInt(shape.bou_x, 10),
                    parseInt(shape.bou_y, 10),
                    true
                );
                customShape.attachListeners();
            }
        }
        break;
    case 'AdamActivity':
        uid = shape.act_uid;
        marker = shape.act_task_type;
        shapeType = shape.act_type;
        if (shape.act_task_type === 'USERTASK') {
            customShape = new AdamActivity({
                id: uid,
                act_uid: uid,
                canvas : this.canvas,
                width: parseInt(shape.bou_width, 10),
                height: parseInt(shape.bou_height, 10),
                container : 'activity',
                style: {
                    cssClasses: ['']
                },
                layers: [
                    {
                        /* added by mauricio */
                        // since the class bpmn_activity has border and
                        // moves the activity, then move it a few pixels
                        // back to make it look pretty
                        x: -2,
                        y: -2,
                        layerName : "first-layer",
                        priority: 2,
                        visible: true,
                        style: {
                            cssClasses: ['adam-activity-task']
                        }
                    }
                ],
                connectAtMiddlePoints: true,
                drag: 'customshapedrag',
                resizeBehavior: "activityResize",
                resizeHandlers: {
                    type: "Rectangle",
                    total: 8,
                    resizableStyle: {
                        cssProperties: {
                            'background-color': "rgb(0, 255, 0)",
                            'border': '1px solid black'
                        }
                    },
                    nonResizableStyle: {
                        cssProperties: {
                            'background-color': "white",
                            'border': '1px solid black'
                        }
                    }
                },
                drop : {
                    //type: 'connectioncontainer',
                    type: 'connection'
                    //selectors : ["#AdamEventBoundary", '.adam_boundary_event']
                },
                labels : [
                    {
                        message : shape.act_name || "",
                        //x : 10,
                        //y: 10,
                        width : 0,
                        height : 0,
                        orientation : 'horizontal',
                        position : {
                            location: 'center',
                            diffX : 0,
                            diffY : 0

                        },
                        updateParent : true,
                        updateParentOnLoad: false
                    }
                ],
                markers: [
                    {
                        markerType: marker,
                        x: 5,
                        y: 5,
                        markerZoomClasses: [
                            "adam-marker-50-" + marker.toLowerCase(),
                            "adam-marker-75-" + marker.toLowerCase(),
                            "adam-marker-100-" + marker.toLowerCase(),
                            "adam-marker-125-" + marker.toLowerCase(),
                            "adam-marker-150-" + marker.toLowerCase()
                        ]
                    }
                ],
                act_type: shapeType,
                act_task_type: marker,
                act_name: shape.act_name,
                act_script: shape.act_script,
                act_script_type: shape.act_script_type,
                act_default_flow: shape.act_default_flow ? shape.gat_default_flow : 0,
                minHeight: 50,
                minWidth: 100,
                maxHeight: 300,
                maxWidth: 400
            });
        } else {
            customShape = new AdamActivity({
                id: uid,
                act_uid: uid,
                canvas : this.canvas,
                width: parseInt(shape.bou_width, 10),
                height: parseInt(shape.bou_height, 10),
                container : 'activity',
                style: {
                    cssClasses: ['']
                },
                layers: [
                    {
                        /* added by mauricio */
                        // since the class bpmn_activity has border and
                        // moves the activity, then move it a few pixels
                        // back to make it look pretty
                        x: -2,
                        y: -2,
                        layerName : "first-layer",
                        priority: 2,
                        visible: true,
                        style: {
                            cssClasses: ['adam-activity-task']
                        }
                    },
                    {
                        x: -2,
                        y: -2,
                        layerName: "second-layer",
                        priority: 3,
                        visible: true,
                        style: {
                            cssClasses: []
                        },
                        zoomSprites : [
                            'adam-shape-50-activity-scripttask-' + shape.act_script_type.toLowerCase(),
                            'adam-shape-75-activity-scripttask-' + shape.act_script_type.toLowerCase(),
                            'adam-shape-100-activity-scripttask-' + shape.act_script_type.toLowerCase(),
                            'adam-shape-125-activity-scripttask-' + shape.act_script_type.toLowerCase(),
                            'adam-shape-150-activity-scripttask-' + shape.act_script_type.toLowerCase()
                        ]
                    }
                ],
                connectAtMiddlePoints: true,
                drag: 'customshapedrag',
                labels : [
                    {
                        message : shape.act_name || "",
                        position : {
                            location: 'bottom',
                            diffX : 0,
                            diffY : 4

                        },
                        updateParent : false
                    }
                ],
                //resizeBehavior: "activityResize",
                resizeHandlers: {
                    type: "Rectangle",
                    total: 4,
                    resizableStyle: {
                        cssProperties: {
                            'background-color': "rgb(0, 255, 0)",
                            'border': '1px solid black'
                        }
                    },
                    nonResizableStyle: {
                        cssProperties: {
                            'background-color': "white",
                            'border': '1px solid black'
                        }
                    }
                },
                drop : {type: 'connection'},
                // drop : {
                //     type: 'connectioncontainer',
                //     selectors : ["#AdamEventBoundary", '.adam_boundary_event']
                // },
                act_type: shapeType,
                act_task_type: marker,
                act_name: shape.act_name,
                act_script: shape.act_script,
                act_script_type: shape.act_script_type
            });
        }
        break;
    case 'AdamGateway':
        uid = shape.gat_uid;
        shapeType = shape.gat_type.toLowerCase();
        direction = shape.gat_direction.toLowerCase();

        customShape = new AdamGateway({
            id: uid,
            gat_uid: uid,
            canvas : this.canvas,
            width : 45,
            height : 45,
            gat_type: shapeType,
            gat_direction: direction,
            gat_name: shape.gat_name,
            style: {
                cssClasses: [""]
            },
            labels : [
                {
                    message : shape.gat_name || "",
                    position : {
                        location : "bottom",
                        diffX : 0,
                        diffY : 0
                    }
                }

            ],
            layers: [
                {
                    layerName : "first-layer",
                    priority: 2,
                    visible: true,
                    style: {
                        cssClasses: []
                    },
                    zoomSprites : [
                        'adam-shape-50-gateway-' + shapeType,
                        'adam-shape-75-gateway-' + shapeType,
                        'adam-shape-100-gateway-' + shapeType,
                        'adam-shape-125-gateway-' + shapeType,
                        'adam-shape-150-gateway-' + shapeType
                    ]
                }
            ],
            connectAtMiddlePoints: true,
            drag: 'regular',
            resizeBehavior: "no",
            resizeHandlers: {
                type: "Rectangle",
                total: 4,
                resizableStyle: {
                    cssProperties: {
                        'background-color': "rgb(0, 255, 0)",
                        'border': '1px solid black'
                    }
                },
                nonResizableStyle: {
                    cssProperties: {
                        'background-color': "white",
                        'border': '1px solid black'
                    }
                }
            },
            drop : {type: 'connection'},
            gat_default_flow: (shape.gat_default_flow) ? shape.gat_default_flow : 0
        });
        break;
    case 'AdamArtifact':
        uid = shape.art_uid;
        shapeType = shape.art_type;
        customShape = new AdamArtifact({
            id: uid,
            art_uid: uid,
            canvas : this.canvas,
            width: parseInt(shape.bou_width, 10),
            height: parseInt(shape.bou_height, 10),
            style: {
                cssClasses: []
            },
            layers: [
                {
                    layerName : "first-layer",
                    priority: 2,
                    visible: true
                }
            ],
            connectAtMiddlePoints: true,
            drag: 'regular',
            //resizeBehavior: "yes",
            resizeBehavior: "adamArtifactResize",
            resizeHandlers: {
                type: "Rectangle",
                total: 8,
                resizableStyle: {
                    cssProperties: {
                        'background-color': "rgb(0, 255, 0)",
                        'border': '1px solid black'
                    }
                },
                nonResizableStyle: {
                    cssProperties: {
                        'background-color': "white",
                        'border': '1px solid black'
                    }
                }
            },
            labels : [
                {
                    message : shape.at_name || "",
                    width : 0,
                    height : 0,
                    //orientation:'vertical',
                    position: {
                        location : 'center-right',
                        diffX : 0,
                        diffY : 0
                    },
                    updateParent : true
                }
            ],
            drop : {type: 'connection'},
            art_type: shapeType,
            art_name: shape.art_name
        });
        break;
    }
    if (addShape) {
        this.canvas.addElement(customShape, parseInt(shape.bou_x, 10), parseInt(shape.bou_y, 10), true);
        customShape.attachListeners();
        this.canvas.updatedElement = customShape;
    }
};


/**
 * Loads the connection into the diagram
 * @param {Object} conn
 */
AdamProject.prototype.loadFlow = function (conn) {
    var sourceObj,
        targetObj,
        startPoint,
        endPoint,
        sourcePort,
        targetPort,
        connection,
        segmentMap = {
            'SEQUENCE' : 'regular',
            'MESSAGE' : 'segmented',
            'DATAASSOCIATION' : 'dotted',
            'ASSOCIATION' : 'dotted',
            'DEFAULT' : 'regular',
            'CONDITIONAL' : 'regular'
        },
        srcDecorator = {
            'SEQUENCE' : 'adam-decorator',
            'MESSAGE' : 'adam-decorator_message',
            'DATAASSOCIATION' : 'adam-decorator',
            'ASSOCIATION' : 'adam-decorator_',
            'DEFAULT' : 'adam-decorator_default',
            'CONDITIONAL' : 'adam-decorator_conditional'
        },
        destDecorator = {
            'SEQUENCE' : 'adam-decorator',
            'MESSAGE' : 'adam-decorator_message',
            'DATAASSOCIATION' : 'adam-decorator_association',
            'ASSOCIATION' : 'adam-decorator_association',
            'DEFAULT' : 'adam-decorator',
            'CONDITIONAL' : 'adam-decorator'
        };

    sourceObj = this.getElementByUid(conn.flo_element_origin);
    targetObj = this.getElementByUid(conn.flo_element_dest);

    startPoint = new jCore.Point(conn.flo_x1, conn.flo_y1);
    endPoint = new jCore.Point(conn.flo_x2, conn.flo_y2);

    sourcePort = new jCore.Port({
        width: 10,
        height: 10
    });

    targetPort = new jCore.Port({
        width: 10,
        height: 10
    });

    sourceObj.addPort(sourcePort, startPoint.x - sourceObj.absoluteX, startPoint.y - sourceObj.absoluteY);
    targetObj.addPort(targetPort, endPoint.x - targetObj.absoluteX, endPoint.y - targetObj.absoluteY, false, sourcePort);
    connection = new AdamFlow({
        id : conn.flo_uid,
        srcPort : sourcePort,
        destPort : targetPort,
        canvas : this.canvas,
        segmentStyle : segmentMap[conn.flo_type],
        flo_type : conn.flo_type,
        name : conn.flo_name,
        flo_condition : conn.flo_condition,
        flo_state : conn.flo_state
    });

    connection.setSrcDecorator(new jCore.ConnectionDecorator({
        decoratorPrefix : srcDecorator[conn.flo_type],
        decoratorType : "source",
        style : {
            cssClasses: []
        },
        width : 11,
        height : 11,
        canvas : this.canvas,
        parent : connection
    }));

    connection.setDestDecorator(new jCore.ConnectionDecorator({
        decoratorPrefix : destDecorator[conn.flo_type],
        decoratorType : "target",
        style : {
            cssClasses : []
        },
        width : 11,
        height : 11,
        canvas : this.canvas,
        parent : connection
    }));

    //connection.connect();
    connection.setSegmentMoveHandlers();

    //add the connection to the canvas, that means insert its html to
    // the DOM and adding it to the connections array

    this.canvas.addConnection(connection);

    // Filling AdamFlow fields
    connection.setTargetShape(targetPort.parent);
    connection.setOriginShape(sourcePort.parent);
    connection.savePoints();

    // now that the connection was drawn try to create the intersections
    connection.checkAndCreateIntersectionsWithAll();

    //attaching port listeners
    sourcePort.attachListeners(sourcePort);
    targetPort.attachListeners(targetPort);
};


/**
 * Returns the shape into the diagram
 * @param {String} uid
 * @return {Object|undefined}
 */

AdamProject.prototype.getElementByUid = function (uid) {
    var element, shapes, i, activity;

    element = this.canvas.getCustomShapes().find('id', uid);
    if (!element) {
        shapes = this.canvas.getCustomShapes();
        for (i = 0; i < shapes.getSize(); i += 1) {
            if (shapes.get(i).getType() === 'AdamActivity') {
                activity = shapes.get(i);
                element = activity.getChildren().find('id', uid);
                if (element) {
                    break;
                }
            }
        }
    }
    return element;
};



AdamProject.prototype.updateToolbar = function () {
    var $title, $title_box, $savebutton, $undobutton, $redobutton, value, undo, redo, undoClass, redoClass;
    $title = $('#ProjectTitle');
    $title_box = $('#txt-title');
    $savebutton = $('#ButtonSave > i');
    $savebutton.removeClass();
    if (this.isDirty) {
        //value = "*" + this.name;
        $savebutton.addClass('adam-icon-save-on');
    } else {
        //value = this.name;
        $savebutton.addClass('adam-icon-save-off');
    }
    $title.html(this.name);
    $title_box.html(this.name);
};

AdamProject.prototype.updateUndoRedo = function () {
    var undo, redo, undoClass, redoClass, $undobutton, $redobutton;

    $undobutton = $('#ButtonUndo > i');
    $redobutton = $('#ButtonRedo > i');

    undo = (this.canvas.commandStack.getUndoSize() > 0);
    redo = (this.canvas.commandStack.getRedoSize() > 0);

    $undobutton.removeClass();
    undoClass = (undo) ? 'adam-icon-undo-on' : 'adam-icon-undo-off';
    $undobutton.addClass(undoClass);

    $redobutton.removeClass();
    redoClass = (redo) ? 'adam-icon-redo-on' : 'adam-icon-redo-off';
    $redobutton.addClass(redoClass);
    //this.canvas.commandStack.debug(true);
};

AdamProject.prototype.onCanvasClick = function () {
//    this.propertiesGrid.forceFocusOut();
};

AdamProject.prototype.updatePropertiesGrid = function (element) {
    if (!element) {
        this.propertiesGrid.clear();
        return;
    }
    var aux,
        readOnly,
        options,
        setup = {
            id: element.getID(),
            width: "100%",
            rows:  [
                {
                    name: "uid",
                    label: translate("LBL_PMSE_PROPERTY_GRID_UID"),
                    value: element.getID(),
                    type: "text",
                    readOnly: true
                },
                {
                    name: "name",
                    label: translate("LBL_PMSE_PROPERTY_GRID_NAME"),
                    value: element.getName(),
                    type: "text",
                    readOnly: element.type === 'Connection' ? false: true
                }
            ],
            onRowDeselected: function () {
                jCore.getActiveCanvas().currentLabel = false;
            },
            onRowsInitialized: function () {
                if (jCore.getActiveCanvas()) {
                    jCore.getActiveCanvas().currentLabel = false;
                }
            },
            onChangeDiscarded: function () {
                jCore.getActiveCanvas().currentLabel = false;
            },
            onViewMode: function () {
                jCore.getActiveCanvas().currentLabel = false;
            },
            onEditMode: function (data) {
                if (element.type !== 'Connection') {
                    jCore.getActiveCanvas().currentLabel = jCore.getActiveCanvas().customShapes.find('id', data.id).label;
                } else {
                    jCore.getActiveCanvas().currentLabel = jCore.getActiveCanvas().connections.find('id', data.id).label;
                }
            }
        };
    if (!((element.type === 'AdamEvent' && element.evn_type === 'BOUNDARY') || element.type === 'Connection')) {
        setup.rows.push({
            name: "x",
            label: translate("LBL_PMSE_PROPERTY_GRID_X"),
            value: element.getX(),
            type: "text",
            validate: "integer",
            readOnly: true
        });
        setup.rows.push({
            name: "y",
            label: translate("LBL_PMSE_PROPERTY_GRID_Y"),
            value: element.getY(),
            type: "text",
            validate: "integer",
            readOnly: true
        });
    }
    if (element.type !== 'AdamEvent' && element.type !== 'AdamGateway' && element.type !== 'Connection') {

        readOnly = (element.act_task_type ==="SCRIPTTASK") ? true:false;
        setup.rows.push({
            name: 'width',
            label: translate('LBL_PMSE_PROPERTY_GRID_WIDTH'),
            type: 'text',
            validate: 'integer',
            value: element.getWidth(),
            readOnly: true
        });
        setup.rows.push({
            name: 'height',
            label: translate('LBL_PMSE_PROPERTY_GRID_HEIGHT'),
            type: 'text',
            validate: 'integer',
            value: element.getHeight(),
            readOnly: true
        });
    }
    switch (element.type) {
    case 'AdamActivity':
        setup.rows.push({
            name: 'act_cancel_remaining_instances',
            label: translate('LBL_PMSE_PROPERTY_GRID_CANCEL_REMAINING_INSTANCES'),
            type: 'yesNo',
            value: element.getCancelRemainingInstances(),
            readOnly: true
        });
        setup.rows.push({
            name: 'act_is_for_compensation',
            label: translate('LBL_PMSE_PROPERTY_GRID_COMPENSATION'),
            type: 'yesNo',
            yesNoValueMode: 'int',
            value: element.getIsForCompensation(),
            readOnly: true
        });
        setup.rows.push({
            name: 'act_completion_quantity',
            label: translate('LBL_PMSE_PROPERTY_GRID_COMPLETION_QUANTITY'),
            value: element.getCompletionQuantity(),
            type: 'text',
            validate: 'integer',
            readOnly: true
        });
        setup.rows.push({
            name: 'act_is_global',
            label: translate('LBL_PMSE_PROPERTY_GRID_GLOBAL'),
            type: 'yesNo',
            value: element.getIsGlobal(),
            readOnly: true
        });
        setup.rows.push({
            name: 'act_referer',
            label: translate('LBL_PMSE_PROPERTY_GRID_REFERER'),
            value: element.act_referer,
            type: 'text',
            readOnly: true
        });
        setup.rows.push({
            name: 'act_start_quantity',
            label: translate('LBL_PMSE_PROPERTY_GRID_START_QUANTITY'),
            value: element.getStartQuantity(),
            type: 'text',
            validate: 'integer'
        });
        setup.rows.push({
            name: "type",
            label: translate("LBL_PMSE_PROPERTY_GRID_TYPE"),
            value: element.getActivityType(),
            type: "text",
            readOnly: true
        });
        setup.rows.push({
            name: 'act_task_type',
            label: translate('LBL_PMSE_PROPERTY_GRID_TASK_TYPE'),
            options: [
                {
                    label: translate("LBL_PMSE_PROPERTY_GRID_USER_TASK"),
                    value: "USERTASK",
                    selected: element.act_task_type
                },
                {
                    label: translate("LBL_PMSE_PROPERTY_GRID_SCRIPT_TASK"),
                    value: "SCRIPTTASK",
                    selected: element.act_task_type
                }
            ],
            type: 'select',
            readOnly: true
        });

        if (element.act_task_type !== 'SCRIPTTASK') {
                /*setup.rows.push({
                    name: 'act_implementation',
                    label: 'Implementation',
                    type: 'text',
                    value: element.getImplementation()
                });
                setup.rows.push({
                    name: 'act_instantiate',
                    label: 'Instantiate',
                    type: 'yesNo',
                    value: element.getInstantiate()
                });*/
        } else {
            setup.rows.push({
                name: 'act_script',
                label: translate('LBL_PMSE_PROPERTY_GRID_SCRIPT'),
                type: 'text',
                value: element.getScript(),
                readOnly: true
            });
            setup.rows.push({
                name: 'act_script_type',
                label: translate('LBL_PMSE_PROPERTY_GRID_SCRIPT_TYPE'),
                type: 'text',
                value: element.getScriptType(),
                readOnly: true
            });
        }
        if (element.act_loop_type.toLowerCase() !== 'none') {
            setup.rows.push({
                name: 'act_loop_behavior',
                label: translate('LBL_PMSE_PROPERTY_GRID_LOOP_BEHAVIOR'),
                type: 'text',
                value: element.act_loop_behavior,
                readOnly: true
            });
            setup.rows.push({
                name: 'act_loop_cardinality',
                label: translate('LBL_PMSE_PROPERTY_GRID_LOOP_CARDINALITY'),
                type: 'text',
                validate: 'integer',
                value: element.act_loop_cardinality,
                readOnly: true
            });
            setup.rows.push({
                name: 'act_loop_maximum',
                label: translate('LBL_PMSE_PROPERTY_GRID_LOOP_MAXIMUM'),
                type: 'text',
                validate: 'integer',
                value: element.act_loop_maximum,
                readOnly: true
            });
            aux = element.act_loop_type.toUpperCase();
            setup.rows.push({
                name: 'act_loop_type',
                label: translate('LBL_PMSE_PROPERTY_GRID_LOOP_TYPE'),
                type: 'select',
                options: [
                    {
                        label: translate("LBL_PMSE_PROPERTY_GRID_NONE"),
                        value: "NONE",
                        selected: aux
                    },
                    {
                        label: translate("LBL_PMSE_PROPERTY_GRID_STANDARD"),
                        value: "STANDARD",
                        selected: aux
                    },
                    {
                        label: translate("LBL_PMSE_PROPERTY_GRID_MULTI_INSTANCE_PARALLEL"),
                        value: "PARALLEL",
                        selected: aux
                    },
                    {
                        label: translate("LBL_PMSE_PROPERTY_GRID_MULTI_INSTANCE_SEQUENCIAL"),
                        value: "SEQUENCIAL",
                        selected: aux
                    }
                ],
                readOnly: true
            });
        }
        break;
    case 'AdamGateway':
        if (element.gat_type === 'EVENTBASED') {
            readOnly: true
            options = [{
                label: translate('LBL_PMSE_PROPERTY_GRID_UNSPECIFIED'),
                value: "UNSPECIFIED",
                selected: element.gat_direction
            }];
        } else {
            readOnly: true
            options = [
//                {
//                    label: 'Unspecified',
//                    value: "UNSPECIFIED",
//                    selected: element.gat_direction
//                },

                {
                    label: translate('LBL_PMSE_PROPERTY_GRID_CONVERGING'),
                    value: 'CONVERGING',
                    selected: element.gat_direction
                },
                {
                    label: translate('LBL_PMSE_PROPERTY_GRID_DIVERGING'),
                    value: 'DIVERGING',
                    selected: element.gat_direction
                }
//                {
//                    label: 'Mixed',
//                    value: 'MIXED',
//                    selected: element.gat_direction
//                }
            ];
        }

        setup.rows.push({
            name: 'gat_direction',
            label: translate('LBL_PMSE_PROPERTY_GRID_DIRECTION'),
            type: 'select',
            readOnly: true,
            options: options
        });
        setup.rows.push({
            name: "type",
            label: translate("LBL_PMSE_PROPERTY_GRID_TYPE"),
            type: "text",
            value: element.gat_type,
            readOnly: true
        });
        break;
    case 'AdamEvent':
        if (element.evn_type === 'INTERMEDIATE' || element.evn_type === 'BOUNDARY') {
            setup.rows.push({
                name: "action",
                label: translate("LBL_PMSE_PROPERTY_GRID_ACTION"),
                type: "select",
                options: [
                    {
                        label: translate('LBL_PMSE_PROPERTY_GRID_SEND_MESSAGE'),
                        value: 'MESSAGE-THROW',
                        selected: element.evn_marker + '-' + element.evn_behavior
                    },
                    {
                        label: translate('LBL_PMSE_PROPERTY_GRID_RECEIVE_MESSAGE'),
                        value: 'MESSAGE-CATCH',
                        selected: element.evn_marker + '-' + element.evn_behavior
                    },
                    {
                        label: translate('LBL_PMSE_PROPERTY_GRID_TIMER'),
                        value: 'TIMER-CATCH',
                        selected: element.evn_marker + '-CATCH'
                    }
                ],
                readOnly: true
            });
        }
        if (element.evn_type === 'START' || element.evn_type === 'BOUNDARY') {
            setup.rows.push({
                name: "evn_is_interrupting",
                label: translate("LBL_PMSE_PROPERTY_GRID_INTERUPTING"),
                value: element.evn_is_interrupting,
                type: "yesNo",
                readOnly: true
            });
        }
        if (element.evn_type !== 'START' && element.evn_marker === "MESSAGE") {
            setup.rows.push({
                name: 'evn_message',
                label: translate('LBL_PMSE_PROPERTY_GRID_MESSAGE'),
                type: 'text',
                value: element.evn_message,
                readOnly: true
            });
            setup.rows.push({
                name: 'evn_operation_implementation',
                label: translate('LBL_PMSE_PROPERTY_GRID_OPERATION_IMPLEMENTATION_REF'),
                type: 'text',
                value: element.evn_operation_implementation,
                readOnly: true
            });
            setup.rows.push({
                name: 'evn_operation_name',
                label: translate('LBL_PMSE_PROPERTY_GRID_OPERATION_NAME'),
                type: 'text',
                value: element.evn_operation_name,
                readOnly: true
            });
        }
        switch (element.evn_type) {
        case 'START':
            setup.rows.push({
                name: "listen",
                label: translate("LBL_PMSE_PROPERTY_GRID_LISTEN"),
                type: "select",
                options: [
                    {
                        label: translate('LBL_PMSE_PROPERTY_GRID_LEAD'),
                        value: 'Leads',
                        selected: element.evn_message
                    },
                    {
                        label: translate('LBL_PMSE_PROPERTY_GRID_DOCUMENT'),
                        value: 'Documents',
                        selected: element.evn_message
                    },
                    {
                        label: translate('LBL_PMSE_PROPERTY_GRID_OPPORTUNITY'),
                        value: 'Opportunities',
                        selected: element.evn_message
                    },
                    {
                        label: translate('LBL_PMSE_PROPERTY_GRID_OTHER_MODULE'),
                        value: '',
                        selected: element.evn_message
                    }
                ],
                readOnly: true
            });
            break;
        case 'INTERMEDIATE':
            if (element.evn_marker === 'TIMER') {
                setup.rows.push({
                    name: 'evn_time_cycle',
                    label: translate('LBL_PMSE_PROPERTY_GRID_TIME_CYCLE'),
                    type: 'text',
                    value: element.evn_time_cycle,
                    readOnly: true
                });
                setup.rows.push({
                    name: 'evn_time_date',
                    label: translate('LBL_PMSE_PROPERTY_GRID_TIME_DATE'),
                    type: 'text',
                    value: element.evn_time_date,
                    readOnly: true
                });
                setup.rows.push({
                    name: 'evn_time_duration',
                    label: translate('LBL_PMSE_PROPERTY_GRID_TIME_DURATION'),
                    type: 'text',
                    value: element.evn_time_duration,
                    readOnly: true
                });
            }
            break;
        case 'END':
            setup.rows.push({
                name: 'result',
                label: translate('LBL_PMSE_PROPERTY_GRID_RESULT'),
                type: 'select',
                options: [
                    {
                        label: translate('LBL_PMSE_PROPERTY_GRID_EMPTY'),
                        value: 'EMPTY',
                        selected: element.evn_marker
                    },
                    {
                        label: translate('LBL_PMSE_PROPERTY_GRID_MESSAGE'),
                        value: 'MESSAGE',
                        selected: element.evn_marker
                    },
                    {
                        label: translate('LBL_PMSE_PROPERTY_GRID_TERMINATE'),
                        value: 'TERMINATE',
                        selected: element.evn_marker
                    }
                ],
                readOnly: true
            });
            break;
        case 'BOUNDARY':
            break;
        }
        setup.rows.push({
            name: "type",
            label: translate("LBL_PMSE_PROPERTY_GRID_TYPE"),
            value: element.evn_type,
            type: "text",
            readOnly: true
        });
        break;
    case 'Connection':
//        if (element.flo_type !== "ASSOCIATION") {
//            setup.rows.push({
//                name: "flo_condition",
//                label: 'Conditions',
//                type: 'text',
//                value: element.flo_condition,
//                readOnly:true
//            });
//        }
        setup.rows.push({
            name: 'flo_type',
            label: translate('LBL_PMSE_PROPERTY_GRID_TYPE'),
            type: 'text',
            value: element.flo_type,
            readOnly: true
        });
        break;
    }

    setup.onValueChanged = function (e) {
        var command = null,
            aux,
            elm,
            mp,
            valid;
        //check if the row name is set and it is different than an empty string

        if (typeof e.fieldName === 'undefined'  || e.fieldName === '') {
            throw new Error('missing name for ' + e.fieldLabel);
        }

        elm = (element.type !== 'Connection')
            ? element.canvas.customShapes.find('id', e.id)
            : element.canvas.connections.find('id', e.id);

        switch (e.fieldName) {
        case 'name':
            valid = element.canvas.validateName(element.label, e.value);
            if (!valid.valid) {
                mp = new MessagePanel({
                    title: 'Error',
                    wtype: 'Error',
                    message: valid.message
                });
                mp.show();
                break;
            }
            command = new jCore.CommandEditLabel(element.label, e.value);
            break;
        case 'x':
        case 'y':
            element.setOldX(elm.getX());
            element.setX(e.fieldName === 'x' ? e.value : elm.getX());
            element.setOldY(elm.getY());
            element.setY(e.fieldName === 'y' ? e.value : elm.getY());
            command = new jCore.CommandMove((new jCore.ArrayList()).insert(elm));
            break;
        case 'width':
        case 'height':
            element.setOldX(element.getX());
            element.setOldY(element.getY());
            element.oldWidth = element.getWidth();
            element.setWidth(e.fieldName === 'width' ? e.value : element.getWidth());
            element.oldHeight = element.getHeight();
            element.setHeight(e.fieldName === 'height' ? e.value : element.getHeight());
            if (element.type === 'AdamArtifact') {
                command = new CommandAnnotationResize(element);
            } else {
                command = new jCore.CommandResize(element);
            }
            break;
        case 'act_task_type':
            element.updateTaskType(e.value);
            break;
        case 'listen':
            element.updateEventMarker({
                evn_message: e.value,
                evn_marker: "MESSAGE",
                evn_behavior: "CATCH"
            });
            break;
        case 'action':
            aux = e.value.split("-");
            element.updateEventMarker({
                evn_marker: aux[0],
                evn_behavior: aux[1]
            });
            break;
        case 'result':
            element.updateEventMarker({
                evn_marker: e.value,
                evn_behavior: "THROW"
            });
            break;
        case 'flo_condition':
            command = new CommandConnectionCondition(element, e.value);
            break;
        default:
            command = new CommandSingleProperty(element, {
                propertyName: e.fieldName,
                before: elm[e.fieldName],
                after: e.value
            });
        }
        if (command) {
            command.execute();
            element.getCanvas().commandStack.add(command);
        }
    };

    //this.propertiesGrid.load(setup);
};

AdamProject.prototype.onSelectElementHandler = function (canvas) {
    var that = this;
    return function () {
        //that.propertiesGrid.clear();
        if (canvas.getCurrentSelection().getSize() === 1 || canvas.updatedElement[0].relatedObject.type === 'Connection') {
            if (canvas.updatedElement[0].relatedObject.type !== 'Connection') {
                canvas.project.updatePropertiesGrid(canvas.getCurrentSelection().get(0));
                setSelectedNode(canvas.getCurrentSelection().get(0));
            } else {
                canvas.project.updatePropertiesGrid(canvas.updatedElement[0].relatedObject);
            }
        }
    };
};

AdamProject.prototype.addMetadata = function (metadataName, config, replaceIfExists) {
    var meta, proxy;
    config = config || {};
    if (typeof config !== "object") {
        throw new Error("addMetadata(): the second (which is optional) must be an object or null.");
    }
    if (!this._metadata[metadataName] || replaceIfExists) {
        meta = this._metadata[metadataName] = {};
        if (typeof config.dataURL === "string" && config.dataURL) {
            meta.dataURL = config.dataURL;
            meta.dataRoot = config.dataRoot;
            proxy = new SugarProxy();
            proxy.url = config.dataURL; 
            proxy.getData(null, {
                success: function (data) {
                    meta.data = config.dataRoot ? data[config.dataRoot] : data;
                    if (typeof config.success === "function") {
                        config.success(meta.data);
                    }
                }
            });
            return;
        } else if (config.data) {
            meta.data = config.data
        }    
    }

    if (typeof config.success === "function") {
        config.success(this._metadata[metadataName].data);
    }
    
    return this;
};

AdamProject.prototype.setMetadata = function (metadata) {
    var i, metadataName;
    if(!jQuery.isArray(metadata)) {
        throw new Error("setMetadata(): The parameter must be an array.");
    }
    for (i = 0; i < metadata.length; i++) {
        if (typeof metadata[i] !== 'object') {
            throw new Error("setMetadata(): All the elements of the array parameter must be objects.");
        }
        if (metadataName = metadata[i].name) {
            this.addMetadata(metadataName, metadata[i], true);
        }
    }

    return this;
};

AdamProject.prototype.getMetadata = function (metadataName) {
    return (this._metadata[metadataName] && this._metadata[metadataName].data) || null;
};

/*global jCore, $, HiddenField, TextareaField, TextField, ItemMatrixField,
 PROJECT_LOCKED_VARIABLES, SUGAR_URL, RestProxy, ComboboxField, adamUID,
 PROJECT_MODULE, project, MessagePanel, PROJECT_LOCKED_VARIABLES, Form, Window,
 Menu, AdamContainerDropBehavior, AdamProject, Tree, translate, sprintf, LabelField,
 PMSE_DESIGNER_FORM_TRANSLATIONS
*/
/**
 * @class AdamCanvas
 * Class to handle the designer canvas
 *
 * @constructor
 * Creates a new AdamCanvas object
 * @param {Object} options
 */
var AdamCanvas = function (options) {
    jCore.Canvas.call(this, options);
    /**
     * Diagram ID
     * @type {null}
     */
    this.dia_id = null;
    /**
     * Project ID
     * @type {String}
     */
    this.projectUid = "";
    /**
     * Asssociation with current Project
     * @type {AdamProject}
     */
    this.project = null;

    this.currentMenu = null;

    this.isClicked = false;

    /**
     * BPMN General Rules for validations,
     * @type {object}
     *
     */
    this.bpmnRules = {
        AdamEvent : {
            start :[
                {
                    id : '106107',
                    type: 1,
                    family: 6,
                    familyType: 1,
                    familySubType: 0,
                    action: 1,
                    message: translate('LBL_PMSE_MESSAGE_ERROR_START_EVENT_OUTGOING'),
                    rules: [
                        {
                            compare: '>',
                            value: 0,
                            direction: 'outgoing',
                            element: 'sequenceFlow'
                        }
                    ]
                }
            ],
            end: [
                {
                    id : '106108',
                    type: 1,
                    family: 6,
                    familyType: 3,
                    familySubType: 0,
                    action: 1,
                    message: translate('LBL_PMSE_MESSAGE_ERROR_END_EVENT_INCOMING'),
                    rules: [
                        {
                            compare: '>',
                            value: 0,
                            direction: 'incoming',
                            element: 'sequenceFlow'
                        }
                    ]
                }
            ],
            intermediate: [
                {
                    id : '106109',
                    type: 1,
                    family: 6,
                    familyType: 2,
                    familySubType: 0,
                    action: 1,
                    message: translate('LBL_PMSE_MESSAGE_ERROR_INTERMEDIATE_EVENT_INCOMING'),
                    rules: [
                        {
                            compare: '>',
                            value: 0,
                            direction: 'incoming',
                            element: 'sequenceFlow'
                        }
                    ]
                },
                {
                    id : '106112',
                    type: 1,
                    family: 6,
                    familyType: 2,
                    familySubType: 0,
                    action: 1,
                    message: translate('LBL_PMSE_MESSAGE_ERROR_INTERMEDIATE_EVENT_OUTGOING'),
                    rules: [
                        {
                            compare: '=',
                            value: 1,
                            direction: 'outgoing',
                            element: 'sequenceFlow'
                        }
                    ]
                }
            ],
            boundary: [
                {
                    id : '106115',
                    type: 1,
                    family: 6,
                    familyType: 4,
                    familySubType: 1,
                    action: 1,
                    message: translate('LBL_PMSE_MESSAGE_ERROR_BOUNDARY_EVENT_OUTGOING'),
                    rules: [
                        {
                            compare: '=',
                            value: 1,
                            direction: 'outgoing',
                            element: 'sequenceFlow'

                        }
                    ]
                }
            ]
        },
        AdamActivity : {
            task: [
                {
                    id : '105101',
                    type: 1,
                    family: 5,
                    familyType: 1,
                    familySubType: 0,
                    action: 1,
                    message: translate('LBL_PMSE_MESSAGE_ERROR_ACTIVITY_INCOMING'),
                    rules: [
                        {
                            compare: '>',
                            value: 0,
                            direction: 'incoming',
                            element: 'sequenceFlow'
                        }
                    ]
                },
                {
                    id : '105102',
                    type: 1,
                    family: 5,
                    familyType: 1,
                    familySubType: 0,
                    action: 1,
                    message: translate('LBL_PMSE_MESSAGE_ERROR_ACTIVITY_OUTGOING'),
                    rules: [
                        {
                            compare: '>',
                            value: 0,
                            direction: 'outgoing',
                            element: 'sequenceFlow'
                        }
                    ]
                }
            ]
        },
        AdamGateway: {
            diverging : [
                {
                    id : '107101',
                    type: 1,
                    family: 7,
                    familyType: 1,
                    familySubType: 0,
                    action: 1,
                    message: translate('LBL_PMSE_MESSAGE_ERROR_GATEWAY_DIVERGING_INCOMING'),
                    rules: [
                        {
                            compare: '>=',
                            value: 1,
                            direction: 'incoming',
                            element: 'sequenceFlow'
                        }
                    ]
                },
                {
                    id : '107102',
                    type: 1,
                    family: 7,
                    familyType: 1,
                    familySubType: 0,
                    action: 1,
                    message: translate('LBL_PMSE_MESSAGE_ERROR_GATEWAY_DIVERGING_OUTGOING'),
                    rules: [
                        {
                            compare: '>',
                            value: 1,
                            direction: 'outgoing',
                            element: 'sequenceFlow'
                        }
                    ]
                }
            ],
            converging : [
                {
                    id : '107201',
                    type: 1,
                    family: 7,
                    familyType: 2,
                    familySubType: 0,
                    action: 1,
                    message: translate('LBL_PMSE_MESSAGE_ERROR_GATEWAY_CONVERGING_INCOMING'),
                    rules: [
                        {
                            compare: '>',
                            value: 1,
                            direction: 'incoming',
                            element: 'sequenceFlow'
                        }
                    ]
                },
                {
                    id : '107202',
                    type: 1,
                    family: 7,
                    familyType: 2,
                    familySubType: 0,
                    action: 1,
                    message: translate('LBL_PMSE_MESSAGE_ERROR_GATEWAY_CONVERGING_OUTGOING'),
                    rules: [
                        {
                            compare: '=',
                            value: 1,
                            direction: 'outgoing',
                            element: 'sequenceFlow'
                        }
                    ]
                }
            ],
            mixed : [
                {
                    id : '107301',
                    type: 1,
                    family: 7,
                    familyType: 3,
                    familySubType: 0,
                    action: 1,
                    message: translate('LBL_PMSE_MESSAGE_ERROR_GATEWAY_MIXED_INCOMING'),
                    rules: [
                        {
                            compare: '>',
                            value: 1,
                            direction: 'incoming',
                            element: 'sequenceFlow'
                        }
                    ]
                },
                {
                    id : '107302',
                    type: 1,
                    family: 7,
                    familyType: 3,
                    familySubType: 0,
                    action: 1,
                    message: translate('LBL_PMSE_MESSAGE_ERROR_GATEWAY_MIXED_OUTGOING'),
                    rules: [
                        {
                            compare: '>',
                            value: 1,
                            direction: 'outgoing',
                            element: 'sequenceFlow'
                        }
                    ]
                }
            ]

        },
        AdamArtifact: {
            textannotation : [
                {
                    id : '109101',
                    type: 1,
                    family: 9,
                    familyType: 1,
                    familySubType: 0,
                    action: 1,
                    message: translate('LBL_PMSE_MESSAGE_ERROR_ANNOTATION'),
                    rules: [
                        {
                            compare: '>',
                            value: 0,
                            direction: 'none',
                            element: 'associationLine'
                        }
                    ]
                }
            ]
        }
    };

    AdamCanvas.prototype.initObject.call(this, options);
};
AdamCanvas.prototype = new jCore.Canvas();
/**
 * Object Type
 * @type {String}
 */
AdamCanvas.prototype.type = "AdamCanvas";
/**
 * Returns the project id
 * @return {String}
 */
AdamCanvas.prototype.getProjectUid = function () {
    return this.projectUid;
};
/**
 * Returns the type of element
 * @return {String}
 */
AdamCanvas.prototype.getType = function () {
    return this.type;
};
/**
 * Set the diagram id
 * @param {String} id
 * @return {*}
 */
AdamCanvas.prototype.setDiaUid = function (id) {
    this.dia_id = id;
    return this;
};
/**
 * Set the project id
 * @param value
 * @return {*}
 */
AdamCanvas.prototype.setProjectUid = function (value) {
    this.projectUid = value;
    return this;
};
/**
 * Asssociate the AdamProject Object
 * @param {AdamProject} value
 * @return {*}
 */
AdamCanvas.prototype.setProject = function (value) {
    this.project = value;
    return this;
};

AdamCanvas.prototype.setCurrentMenu = function (obj) {
    if (this.currentMenu) {
        this.currentMenu.hide();
    }
    this.currentMenu = obj;
    return this;
};

/**
 * Initialize the default options
 * @param {Object} options
 */
AdamCanvas.prototype.initObject = function (options) {
    var defaultOptions = {
        projectUid : null
    };
    $.extend(true, defaultOptions, options);
    this.setProjectUid(defaultOptions.projectUid)
        .setDiaUid(defaultOptions.dia_id);
};
/**
 * Extends the JCoreObject property to configure the context menus
 * @return {Array}
 */
AdamCanvas.prototype.getContextMenu = function () {
    var f, w,
        hiddenTerminateField,
        hiddenNameModule,
        itemMatrix,
        fnTerminateFields,
        fieldsItems,
        processName,
        processDescription,
        comboModulesFields,
        comboModules,
        comboOperators,
        criteriaField,
        proxyModule,
        callbackModule,
        saveAction,
        refreshAction,
        zoom50Action,
        zoom75Action,
        zoom100Action,
        zoom125Action,
        zoom150Action,
        wAlert,
        fAlert,
        proOldModuleField,
        oldModule,
        proxyConfirm,
        proModuleField,
        alertLabel,
        message,
        result,
        mp2,
        modules,
        data,
        errorModulem,
        checkModuleAndSaveData,
        cancelInformation,
        proLockedFieldBKP,
        url;

    /** FORM MODULES **/
    hiddenNameModule = new HiddenField({name: 'pro_module'});

    processName = new TextField({
        name: 'prj_name',
        label: translate('LBL_PMSE_LABEL_PROCESS_NAME'),
        required: true
    });
    processDescription = new TextareaField({
        name: 'prj_description',
        label: translate('LBL_PMSE_LABEL_DESCRIPTION')
    });

    itemMatrix = new ItemMatrixField({
        jtype: 'itemmatrix',
        label: translate('LBL_PMSE_LABEL_LOCKED_FIELDS'),
        name: 'pro_locked_variables',
        submit: true,
        fieldWidth: 350,
        fieldHeight: 90,
        visualStyle : 'table',
        nColumns: 2
    });
    criteriaField = new CriteriaField({
        name: 'pro_terminate_variables',
        label: translate('LBL_PMSE_LABEL_TERMINATE_PROCESS'),
        required: false,
        fieldWidth: 250,
        fieldHeight: 80,
        decimalSeparator: SUGAR.App.config.defaultDecimalSeparator,
        numberGroupingSeparator: SUGAR.App.config.defaultNumberGroupingSeparator,
        operators: {
            logic: true,
            group: true
        },
        constant: false,
        decimalSeparator: PMSE_DECIMAL_SEPARATOR
    });

    fieldsItems = function (value, initial) {
        App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
        var val = new SugarProxy({
            url: 'pmse_Project/CrmData/fields/' + value,
            //restClient: this.canvas.project.restClient,
            uid: '',
            callback: null
        }),
            modulesFields;
         val.getData(null, {
            success: function (modulesFields) {
                hiddenNameModule.setValue(value);
                //if (typeof initial !== "undefined") {
                if (initial !== undefined) {
                    itemMatrix.setList(modulesFields.result, PROJECT_LOCKED_VARIABLES);
                } else {
                    itemMatrix.setList(modulesFields.result);
                }
                App.alert.dismiss('upload');
                w.html.style.display = 'inline';
            }
        });

    };

    comboModules = new ComboboxField({
        jtype: 'combobox',
        label: translate('LBL_PMSE_FORM_LABEL_MODULE'),
        name: 'comboModules',
        submit: false,
        change: function () {
            return fieldsItems(this.value);
        },
        proxy: new SugarProxy({
            url: 'pmse_Project/CrmData/modules',
            //restClient: this.canvas.project.restClient,
            uid: '',
            callback: null
        })
    });

    proxyModule = new SugarProxy({
        url: 'pmse_Project/CrmData/project/' + adamUID,
        //restClient: this.canvas.project.restClient,
        uid: adamUID,
        callback: null
    });

    callbackModule = {
        'loaded' : function (data) {
            App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
            //w.style.display = 'none';
            //$('.adam-window').hide();
            var arrOperator = [
                {'value': 'equal', 'text': '='}
            ],
                modules;

            var options = [];
            criteriaField.setModuleEvaluation({
                dataURL: "pmse_Project/CrmData/related/" + PROJECT_MODULE,
                dataRoot: "result",
                fieldDataURL: 'pmse_Project/CrmData/fields/{{MODULE}}',
                fieldDataRoot: "result"
            });
            processName.setValue(project.name);

            //modulesList = App.metadata.getModules();
            //for( var property in modulesList ){
            //    //console.log( property );
            //    //console.log( modulesList[property] );
            //    if (modulesList[property].favoritesEnabled) {
            //        options.push({'value': property, 'text': property});
            //    }
            //}
            //options.sort(function(a, b){
            //    var nameA=a.text.toLowerCase(), nameB=b.text.toLowerCase();
            //    if (nameA < nameB) //sort string ascending
            //        return -1
            //    if (nameA > nameB)
            //        return 1
            //    return 0 //default return value (no sorting)
            //});
            //comboModules.setOptions(options);
            //console.log(PROJECT_MODULE);
            //comboModules.setValue(PROJECT_MODULE || options[0].value);

            comboModules.proxy.getData(null, {
                success: function (modules) {
                    comboModules.setOptions(modules.result);
                    comboModules.setValue(PROJECT_MODULE || modules.result[0].value);
                    processName.setValue(project.name);
                    processDescription.setValue(project.description);
                    criteriaField.setValue(project.process_definition.pro_terminate_variables);
                    PROJECT_LOCKED_VARIABLES = project.process_definition.pro_locked_variables.slice();
                    fieldsItems(PROJECT_MODULE || modules.result[0].value, true);
                    itemMatrix.setLockedFields(PROJECT_LOCKED_VARIABLES);
                    oldModule = comboModules.value;

                }
            });

        },
        'submit' : function (data) {

            if (processName.value !== project.name) {
                url = App.api.buildURL('pmse_Project', null, null, {
                    filter: [{'name':processName.value}]
                });
                App.api.call("read", url, null, {
                    success:function (a) {
                      if (a.records.length === 0) {
                            checkModuleAndSaveData(data);
                        } else {
                            var mp = new MessagePanel({
                                title: 'Error',
                                wtype: 'Error',
                                message: translate('LBL_PMSE_MESSAGE_THEPROCESSNAMEALREADYEXISTS', 'pmse_Project', processName.value)//response.message
                            });
                            mp.show();
                        }
                    }
                });
                //project.restClient.getCall({
                //    url: 'pmse_Project/CrmData/validateProjectName',
                //    id: processName.value,
                //    data: {},
                //    success: function (xhr, response) {
                //        if (response.result) {
                //            /*data = {
                //                prj_name: processName.value,
                //                prj_description: processDescription.value,
                //                pro_locked_variables: comboModules.value,
                //                pro_module: comboModules.value
                //            };
                //            project.setDescription(PROJECT_DESCRIPTION = processDescription.value);
                //            project.setName(PROJECT_NAME = processName.value);
                //            proxyModule.sendData(data);
                //            //NAME MODULE
                //            PROJECT_MODULE = comboModules.value;
                //            //LOCKED VARIABLES
                //            PROJECT_LOCKED_VARIABLES = itemMatrix.getLockedField();*/
                //            checkModuleAndSaveData(data);
                //            /*if (comboModules.value !== oldModule) {
                //                wAlert.show();
                //            } else {
                //                data = {
                //                    prj_description: processDescription.value,
                //                    pro_locked_variables: comboModules.value,
                //                };
                //                project.setDescription(PROJECT_DESCRIPTION = processDescription.value);
                //                proxyModule.sendData(data);
                //                //LOCKED VARIABLES
                //                PROJECT_LOCKED_VARIABLES = itemMatrix.getLockedField();
                //                w.close();
                //            }*/
                //        } else {
                //            var mp = new MessagePanel({
                //                title: 'Error',
                //                wtype: 'Error',
                //                message: response.message
                //            });
                //            mp.show();
                //        }
                //    },
                //    failure: function (xhr, response) {
                //        //console.log(response);
                //        //TODO Process HERE error at loading project
                //    }
                //});
            } else {
                /*data = {
                    prj_description: processDescription.value,
                    pro_locked_variables: comboModules.value,
                    pro_module: comboModules.value
                };
                project.setDescription(PROJECT_DESCRIPTION = processDescription.value);
                proxyModule.sendData(data);
                //NAME MODULE
                PROJECT_MODULE = comboModules.value;
                //LOCKED VARIABLES
                PROJECT_LOCKED_VARIABLES = itemMatrix.getLockedField();*/
                checkModuleAndSaveData(data);
                /*if (comboModules.value !== oldModule) {
                    wAlert.show();
                } else {
                    data = {
                        prj_description: processDescription.value,
                        pro_locked_variables: comboModules.value,
                    };
                    project.setDescription(PROJECT_DESCRIPTION = processDescription.value);
                    proxyModule.sendData(data);
                    //LOCKED VARIABLES
                    PROJECT_LOCKED_VARIABLES = itemMatrix.getLockedField();
                    w.close();
                }*/
            }
        }
    };

    checkModuleAndSaveData = function (oldData) {

        if (comboModules.value !== oldModule) {
            //PROJECT_LOCKED_VARIABLES_BPK = oldData.pro_locked_variables;
            proLockedFieldBKP = oldData.pro_locked_variables;
            PROJECT_LOCKED_VARIABLES = itemMatrix.getLockedField();
            mp2.show();
        } else {
            data = {
                description: processDescription.value,
                pro_terminate_variables:criteriaField.value,
                pro_locked_variables: oldData.pro_locked_variables
            };
            if (processName.value !== null && processName.value !== '') {
                data = {
                    name: processName.value,
                    description: processDescription.value,
                    pro_locked_variables: oldData.pro_locked_variables,
                    pro_terminate_variables:criteriaField.value
                };
                project.setName(PROJECT_NAME = processName.value);
            }

            project.process_definition.pro_terminate_variables=criteriaField.value;
            project.setDescription(PROJECT_DESCRIPTION = processDescription.value);
            proxyModule.sendData(data);
            //LOCKED VARIABLES
            PROJECT_LOCKED_VARIABLES = itemMatrix.getLockedField();
            project.process_definition.pro_locked_variables=itemMatrix.getLockedField();
            w.close();
        }
    };

    proxyConfirm = new SugarProxy({
        url: 'pmse_Project/CrmData/putData/' + adamUID,
        //restClient: this.canvas.project.restClient,
        uid: adamUID,
        callback: null
    });
    //proxyConfirm.restClient.setRestfulBehavior(SUGAR_REST);
    //if (!SUGAR_REST) {
    //    proxyConfirm.restClient.setBackupAjaxUrl(SUGAR_AJAX_URL);
    //}

    proModuleField = new HiddenField({
        name: 'pro_new_module'
    });
    proOldModuleField = new HiddenField({
        name: 'pro_old_module'
    });
    alertLabel  = new LabelField({
        name: 'lblAlert',
        label: translate('LBL_PMSE_FORM_LABEL_THE_WARNING'),
        options: {
            marginLeft : 35
        }
    });


    mp2 = new MessagePanel({
        title: "Module change warning",
        wtype: 'Confirm',
        message: translate('LBL_PMSE_MESSAGE_REMOVE_ALL_START_CRITERIA'),
        buttons: [
            {
                jtype: 'normal',
                caption: translate('LBL_PMSE_BUTTON_OK'),
                handler: function () {
                    data = {
                        prj_name: processName.value,
                        prj_description: processDescription.value,
                        pro_locked_variables: proLockedFieldBKP,
                        pro_module: comboModules.value
                    };
                    project.setDescription(PROJECT_DESCRIPTION = processDescription.value);
                    project.setName(PROJECT_NAME = processName.value);
                    proxyModule.sendData(data);
                    //NAME MODULE
                    PROJECT_MODULE = comboModules.value;
                    //LOCKED VARIABLES
                    //PROJECT_LOCKED_VARIABLES = itemMatrix.getLockedField();
                    project.canvas.cleanAllFlowConditions();
                    //Submit change modules
                    data = {
                        pro_new_module: PROJECT_MODULE,
                        pro_old_module: oldModule
                    };
                    proxyConfirm.sendData(data, {
                        //success: function (xhr, response) {
                        success: function (response) {
                            //TODO SUCCESS ALERT
                            if (!response.success) {
                                errorModule = new MessagePanel({
                                    title: "Error",
                                    wtype: 'Error',
                                    message: translate('LBL_PMSE_ADAM_ENGINE_ERROR_UPDATEBPMFLOW')
                                });
                                errorModule.show();
                            } else {
                                w.close();
                            }

                        },
                        failure: function (xhr, response) {
                            //console.log(response);
                            //TODO FAILURE ALERT
                        }
                    });
                    mp2.hide();
                }
            },
            {
                jtype: 'normal',
                caption: translate('LBL_PMSE_BUTTON_CANCEL'),
                handler: function () {
                    comboModules.removeOptions();
                    comboModules.proxy.getData(null, {
                        success: function (modules) {
                            processName.setValue(project.name);
                            processDescription.setValue(project.description);
                            comboModules.setOptions(modules.result);
                            comboModules.setValue(oldModule);
                            criteriaField.setValue(project.process_definition.pro_terminate_variables);
                            PROJECT_LOCKED_VARIABLES = project.process_definition.pro_locked_variables.slice();
                            fieldsItems(PROJECT_MODULE || modules.result[0].value, true);
                            itemMatrix.setLockedFields(PROJECT_LOCKED_VARIABLES);
                            oldModule = PROJECT_MODULE;
                            mp2.hide();

                        }}


                    );

                }
            }
        ]
    });

    f = new Form({
        items: [
            processName,
            processDescription,
            comboModules,
            criteriaField,
            itemMatrix,
            hiddenNameModule
        ],
        //closeContainerOnSubmit: true,
        buttons: [
           // { jtype: 'submit', caption: 'Save' },

            { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_SAVE'), handler: function () {
                f.submit();
            }},


            { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_CANCEL'), handler: function () {
                if (f.isDirty()) {
                    cancelInformation =  new MessagePanel({
                        title: "Confirm",
                        wtype: 'Confirm',
                        message: translate('LBL_PMSE_MESSAGE_CANCEL_CONFIRM'),
                        buttons: [
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_YES'),
                                handler: function () {
                                    PROJECT_LOCKED_VARIABLES = project.process_definition.pro_locked_variables.slice();
                                    cancelInformation.close();
                                    w.close();
                                }
                            },
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_NO'),
                                handler: function () {
                                    cancelInformation.close();
                                }
                            }
                        ]
                    });
                    cancelInformation.show();
                } else {
                    w.close();
                }
            }}
        ],
        callback: callbackModule,
        proxy: null,
        language: PMSE_DESIGNER_FORM_TRANSLATIONS
    });

    w = new Window({
        width: 580,
        height: 450,
        modal: true,
        title: translate('LBL_PMSE_CONTEXT_MENU_PROCESS_DEFINITION')
    });
    w.addPanel(f);
    /** END FORM MODULES **/

    saveAction  = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_SAVE'),
        cssStyle : 'adam-menu-icon-save',
        handler: function () {
            project.save();
            jCore.getActiveCanvas().RemoveCurrentMenu();
        },
        disabled: !project.isDirty
    });

    refreshAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_REFRESH'),
        cssStyle : 'adam-menu-icon-refresh',
        handler: function () {
            document.location.reload(true);
        }
    });

    zoom50Action = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_50'),
        cssStyle : '',
        handler: function () {
            jCore.getActiveCanvas().applyZoom(1);
            $('#zoom').val(1);
        },
        disabled: (jCore.getActiveCanvas().getZoomFactor() === 0.5)
    });

    zoom75Action = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_75'),
        cssStyle : '',
        handler: function () {
            jCore.getActiveCanvas().applyZoom(2);
            $('#zoom').val(2);
        },
        disabled: (jCore.getActiveCanvas().getZoomFactor() === 0.75)
    });

    zoom100Action = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_100'),
        cssStyle : '',
        handler: function () {
            jCore.getActiveCanvas().applyZoom(3);
            $('#zoom').val(3);
        },
        disabled: (jCore.getActiveCanvas().getZoomFactor() === 1)
    });

    zoom125Action = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_125'),
        cssStyle : '',
        handler: function () {
            jCore.getActiveCanvas().applyZoom(4);
            $('#zoom').val(4);
        },
        disabled: (jCore.getActiveCanvas().getZoomFactor() === 1.25)
    });

    zoom150Action = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_150'),
        cssStyle : '',
        handler: function () {
            jCore.getActiveCanvas().applyZoom(5);
            $('#zoom').val(5);
        },
        disabled: (jCore.getActiveCanvas().getZoomFactor() === 1.5)
    });

    return {
        items: [
            new Action({
                text: translate('LBL_PMSE_CONTEXT_MENU_PROCESS_DEFINITION'),
                cssStyle: 'adam-menu-icon-configure',
                handler : function () {
                    w.show();
                    w.html.style.display = 'none';
                }
            }),
            {
                jtype: 'separator'
            },
            saveAction,
            refreshAction,
            {
                label: translate('LBL_PMSE_CONTEXT_MENU_ZOOM'),
                icon: 'adam-menu-icon-zoom',
                menu: {
                    items: [
                        zoom50Action,
                        zoom75Action,
                        zoom100Action,
                        zoom125Action,
                        zoom150Action
                    ]
                }

            }
        ]
    };
};

/**
 * Set the context menu creation
 * @param {Object} element
 * @param {Number} x
 * @param {Number} y
 */
AdamCanvas.prototype.onRightClickHandler = function (element, x, y) {
    var contextMenu, factoryCMenu;
    factoryCMenu = element.getContextMenu();
    if (factoryCMenu.items) {
        factoryCMenu.canvas = this;
        contextMenu = new Menu(factoryCMenu);
        contextMenu.setParent(element);
        contextMenu.show(x, y);
    } else {
        this.RemoveCurrentMenu();
    }
    //element.preventDefault();
};
/**
 * Create a dropBehaviorFactory to insert the custom DropBehaviors
 * @param type
 * @param selectors
 * @return {*}
 */
AdamCanvas.prototype.dropBehaviorFactory = function (type, selectors) {
    var out;
    if (type === 'container') {
        if (!this.containerDropBehavior) {
            this.containerDropBehavior = new AdamContainerDropBehavior(selectors);
        }
        out = this.containerDropBehavior;
    } else {
        out = jCore.BehavioralElement.prototype.dropBehaviorFactory.call(this, type, selectors);
    }
    return out;
};

/**
 * Define the action when the element is created into the canvas
 * @param {Object} element
 */
AdamCanvas.prototype.onCreateElementHandler = function (element) {
    this.RemoveCurrentMenu();
    if (this.project instanceof AdamProject) {
        this.project.addElement(element);

        var items2 = this.getDiagramTree();
        Tree.treeReload('tree', items2);
    }
    this.bpmnValidation();
};
/**
 *  Define the action when the element is updated into the canvas
 * @param element
 */
AdamCanvas.prototype.onChangeElementHandler = function (element) {
    if (this.project instanceof AdamProject) {
        this.project.updateElement(element);

        var items2 = this.getDiagramTree();
        Tree.treeReload('tree', items2);
        if (element.length === 1) {
            this.project.updatePropertiesGrid(
                element[0].type !== 'Connection' ? this.customShapes.find('id', element[0].id)
                    : this.connections.find('id', element[0].id)
            );
        }
    }
};
/**
 * Define the action when the element is deleted from the canvas
 * @param element
 */
AdamCanvas.prototype.onRemoveElementHandler = function (element) {
    var i, items, sizeItems, item;
    if (this.project instanceof AdamProject) {
        this.project.removeElement(element);

        var items2 = this.getDiagramTree();
        Tree.treeReload('tree', items2);
        this.project.updatePropertiesGrid();
    }
    //console.log('Remove Element');
    if (listPanelError){
        if (listPanelError.items.length){
            for ( i = 0 ; i < element.length ; i+=1 ) {
                if (!(element[i].type === "Connection")){
                    item = listPanelError.getItemById(element[i].getID()); 
                    if (item){
                        listPanelError.removeItemById(element[i].getID());
                    }
                }
            }   
        }
    }
    this.bpmnValidation();
    if (countErrors){
        if (listPanelError.getItems().length){
                countErrors.style.display = "block";
                sizeItems = listPanelError.getAllErros();
                countErrors.textContent =  sizeItems === 1 ? sizeItems + translate('LBL_PMSE_BPMN_WARNING_SINGULAR_LABEL') : sizeItems + translate('LBL_PMSE_BPMN_WARNING_LABEL');
        } else {
            countErrors.textContent = "0" + translate('LBL_PMSE_BPMN_WARNING_SINGULAR_LABEL');
        }
    }
};
/**
 * Throws an event when the CommandAdam is executed
 * @param {Object} receiver
 * @param {Array} propertyNames
 * @param {Array} oldValues
 * @param {Array} newValues
 */
AdamCanvas.prototype.triggerCommandAdam = function (receiver, propertyNames, oldValues, newValues) {
    var fields = [],
        i;

    for (i = 0; i < propertyNames.length; i += 1) {
        fields.push({
            field: propertyNames[i],
            newVal: newValues[i],
            oldVal: oldValues[i]
        });
    }

    this.updatedElement = [{
        fields: fields,
        id: receiver.id,
        relatedObject: receiver,
        type: receiver.type,
        adam: true
    }];
    $(this.html).trigger('changeelement');
};

AdamCanvas.prototype.triggerFlowConditionChangeEvent = function (element, oldValues) {
    this.updatedElement = [{
        id: element.id,
        type: element.type,
        fields: [{
            field: "condition",
            oldVal: oldValues.condition,
            newVal: element.getFlowCondition()
        },
            {
                field: "type",
                oldVal: oldValues.type,
                newVal: element.getFlowType()
            }]
    }];
    $(this.html).trigger('changeelement');
};

/**
 * Overwrite XXX
 * @param {Object} element
 * @param {String} oldText
 * @param {String} newText
 */
AdamCanvas.prototype.triggerTextChangeEvent = function (element, oldText, newText) {
    var valid, reg, e, nText, mp;
    reg = /<[^\s]/g;
    nText = newText.trim();
    e = reg.test(nText);
    if (e) {
        nText = nText.replace(/</g, '< ');
    }
    valid = this.validateName(element, nText);
    if (!valid.valid) {
        element.parent.updateLabelsPosition(true, true);
        element.parent.setName(oldText);
        mp = new MessagePanel({
            title: 'Error',
            wtype: 'Error',
            message: valid.message
        });
        mp.show();
        return;
    }

    this.updatedElement = [{
        id : element.parent.id,
        type : element.parent.type,
        fields : [{
            field : "name",
            oldVal : oldText,
            newVal : nText
        }]
    }];
    element.parent.setName(nText);
    $(this.html).trigger("changeelement");
};/*
AdamCanvas.prototype.triggerMarkerChangeEvent = function (shape, oldMarker,
                                                      newMarker, field) {

    this.updatedElement = [{
        id : shape.id,
        type : shape.type,
        fields : [
            {
                field : field,
                oldVal : oldMarker,
                newVal : newMarker
            }
        ],
        relatedObject: shape
    }];
    $(this.html).trigger('changeelement');
};*/

AdamCanvas.prototype.triggerDefaultFlowChangeEvent = function (elements) {
    this.updatedElement = elements;
    $(this.html).trigger("changeelement");
};

AdamCanvas.prototype.triggerConnectionConditionChangeEvent = function (element, fields) {
    this.updatedElement = [{
        id : element.id,
        type : element.type,
        relatedObject: element,
        fields : fields
    }];
    $(this.html).trigger("changeelement");
};

/**
 * Fires the {@link Canvas#event-changeelement} event, and elaborates the structure of the object that will
 * be passed to the handlers, the structure contains the following fields (considering old values and new values):
 *
 * - x
 * - y
 * - parent (the shape that is parent of this shape)
 * - state (of the connection)
 *
 * @param {Port} port The port updated
 * @chainable
 */

AdamCanvas.prototype.triggerPortChangeEvent = function (port) {
    // check if this port is source or dest
    var direction = port.connection.srcPort.getID() === port.getID() ?
            "src" : "dest",
        map = {
            src: {
                x: "x1",
                y: "y1",
                parent: "element_origin",
                type: 'element_origin_type'
            },
            dest: {
                x: "x2",
                y: "y2",
                parent: "element_dest",
                type: 'element_dest_type'
            }
        },
        point,
        state,
        zomeedState = [],
        i;

    // save the points of the new connection
    port.connection.savePoints();
    state = port.connection.getPoints();

    for (i = 0; i < state.length; i += 1) {
        point = port.connection.points[i];
        zomeedState.push(new jCore.Point(point.x / this.zoomFactor, point.y / this.zoomFactor));
    }
    point = direction === "src" ? zomeedState[0] : zomeedState[state.length - 1];

    this.updatedElement = [{
        id: port.connection.getID(),
        type: port.connection.type,
        fields: [
            {
                field: map[direction].x,
                oldVal: point.x,        // there's no old value
                newVal: point.x
            },
            {
                field: map[direction].y,
                oldVal: point.y,        // there's no old value
                newVal: point.y
            },
            {
                field: map[direction].parent,
                oldVal: (port.getOldParent()) ? port.getOldParent().getID() : null,
                newVal: port.getParent().getID()
            },
            {
                field: map[direction].type,
                oldVal: port.connection.getNativeType(port.getParent()).type,
                newVal: port.connection.getNativeType(port.getParent()).type
            },
            {
                field: "state",
                oldVal: port.connection.getOldPoints(),
                newVal: zomeedState
            },
            {
                field: "condition",
                oldVal: "",
                newVal: port.connection.getFlowCondition()
            }
        ],
        relatedObject: port
    }];
//    this.triggerConnectionStateChangeEvent(port.connection);
    $(this.html).trigger('changeelement');
};

AdamCanvas.prototype.RemoveCurrentMenu = function () {
    if (this.currentMenu) {
        this.currentMenu.hide();
    }
};

AdamCanvas.prototype.onSelectElementHandler = function (element) {
    this.hideAllFocusedLabels();
    this.project.onCanvasClick();
    this.RemoveCurrentMenu();
};


/**
 * @event rightclick
 * Handler for the custom event rightclick, this event fires when an element
 * has been right clicked. It executes the hook #onRightClickHandler
 * @param {Canvas} canvas
 */
AdamCanvas.prototype.onRightClick = function (canvas) {
    return function (event, e, element) {
        if (e) {
            var x = e.pageX - canvas.x + canvas.leftScroll,
                y = e.pageY - canvas.y + canvas.topScroll;
            canvas.updatedElement = element;
            canvas.hideAllFocusedLabels();
            if (element.family !== 'Canvas') {
                canvas.emptyCurrentSelection();
                canvas.addToSelection(element);
            }

            canvas.onRightClickHandler(canvas.updatedElement, x, y);
        }

    };
};

AdamCanvas.prototype.onClickHandler = function (canvas, x, y) {
    this.RemoveCurrentMenu();
    this.project.onCanvasClick();
};

/**
 * Obtain the corresponding icon
 * @param {String} shape
 * @returns {String}
 */
AdamCanvas.prototype.getTreeItem = function (shape) {
    var cls  = '',
        name = '',
        item = {};

    switch (shape.getType()) {

    case 'AdamActivity':
        switch (shape.getActivityType()) {
        case 'TASK':
            cls = 'adam-tree-icon-user-task';
            name = (shape.getName() && shape.getName() !== '') ?
                    shape.getName() : 'Task';
            break;
        default:
            cls = 'adam-tree-icon-user-task';
            name = (shape.getName() && shape.getName() !== '') ?
                    shape.getName() : 'Task';
            break;
        }
        break;
    case 'AdamEvent':
        switch (shape.getEventType()) {
        case 'START':
            cls = 'adam-tree-icon-start';
            if (shape.getEventMessage() !== null
                        && shape.getEventMessage() !== '') {
                if (shape.getEventMessage() === 'Opportunities') {
                    cls = 'adam-tree-icon-start-opportunities';
                } else if (shape.getEventMessage() === 'Leads') {
                    cls = 'adam-tree-icon-start-leads';
                } else if (shape.getEventMessage() === 'Documents') {
                    cls = 'adam-tree-icon-start-documents';
                }
            }
            name = (shape.getName() && shape.getName() !== '') ?
                    shape.getName() : 'Start';
            break;
        case 'INTERMEDIATE':
            if (shape.getEventMarker() !== null
                    && shape.getEventMarker() !== '') {
                if (shape.getEventMarker() === 'TIMER') {
                    cls = 'adam-tree-icon-intermediate-timer';
                } else {
                    cls = 'adam-tree-icon-intermediate-message';
                }
            }
            name = (shape.getName() && shape.getName() !== '') ?
                    shape.getName() : 'Intermediate';
            break;
        case 'BOUNDARY':
            cls = 'adam-tree-icon-intermediate-boundary';
            name = (shape.getName() && shape.getName() !== '') ?
                    shape.getName() : 'Boundary';
            break;
        case 'END':
            cls = 'adam-tree-icon-end';
            name = (shape.getName() && shape.getName() !== '') ?
                    shape.getName() : 'End';
            break;
        }
        break;
    case 'AdamGateway':
        if (shape.getGatewayType() === 'PARALLEL') {
            cls = 'adam-tree-icon-gateway-parallel';
        } else {
            cls = 'adam-tree-icon-gateway-exclusive';
        }
        name = (shape.getName() && shape.getName() !== '') ?
                shape.getName() : 'Gateway';
        break;
    case 'AdamData':
        if (shape.getDataType() === 'DATAOBJECT') {
            cls = 'bpmn_icon_dataobject';
            name = (shape.getName() && shape.getName() !== '') ?
                    shape.getName() : 'Data Object';
        } else {
            cls = 'bpmn_icon_datastore';
            name = (shape.getName() && shape.getName() !== '') ?
                    shape.getName() : 'Data Store';
        }
        break;
    case 'AdamArtifact':
        if (shape.getArtifactType() === 'TEXTANNOTATION') {
            cls = 'adam-tree-icon-textannotation';
            name = (shape.getName() && shape.getName() !== '') ?
                    shape.getName() : 'Text Annotation';
        } else {
            cls = 'bpmn_icon_group';
            name = (shape.getName() && shape.getName() !== '') ?
                    shape.getName() : 'Group';
        }
        break;
    }
    item = {
        name: name,
        icon: cls,
        id:   shape.getID()
    };
    return item;
};

AdamCanvas.prototype.buildRecursiveNode = function (root, canvas) {
    var i,
        items = [],
        item,
        elem;
    //sorting childrens by x or y depends of orientation
    canvas.children.sort(function (a, b) {
//        if ((canvas.getType() === 'bpmnPool'
//            || canvas.getType() === 'bpmnLane')
//            && canvas.getOrientation() === 'VERTICAL'){
//            return a.y-b.y
//        }
        return a.x - b.x;
    });

    for (i = 0; i < canvas.children.getSize(); i += 1) {
        elem = canvas.children.get(i);
        if (elem.type !== 'MultipleSelectionContainer') {
            item = this.getTreeItem(elem);
            if (elem.children.getSize() > 0) {
                this.buildRecursiveNode(item, elem);
            }
            items.push(item);
        }
    }
    $.extend(root, {'items': items});
};
/**
 * Get the diagram Tree
 * @returns {Object}
 */
AdamCanvas.prototype.getDiagramTree = function () {
    var diaTree = [],
        tree = {
            //name: this.getName()
            name:  this.name
            //icon:'bpmn_icon_pool',
            //selected:true;
        };
    this.buildRecursiveNode(tree, this);
    diaTree.push(tree);
    return diaTree;
};

AdamCanvas.prototype.addConnection = function (conn) {
    jCore.Canvas.prototype.addConnection.call(this, conn);
    if (conn.flo_state) {
        conn.disconnect(true).connect({
            algorithm: 'user',
            points: conn.flo_state
        });
        conn.setSegmentMoveHandlers();
    }

};

AdamCanvas.prototype.hideAllFocusedLabels =  function () {
    var size = this.customShapes.getSize(),
        i,
        shape;
    for (i = 0; i < size; i += 1) {
        shape = this.customShapes.get(i);
        shape.labels.get(0).loseFocus();
    }
    return true;
};

AdamCanvas.prototype.validateName = function (element, newText) {
    var shape = element.parent, shape_aux,
        limit = this.getCustomShapes().getSize(),
        i, msg = '', rt = true, nText = newText.trim(), str;
//    if (shape.type === 'AdamActivity') {
    if (nText === '') {
        if (shape.type === 'AdamActivity') {
            msg = translate('LBL_PMSE_MESSAGE_TASKNAMEEMPTY');
            rt = false;
        }
    } else {
        for (i = 0; i < limit; i += 1) {
            shape_aux = this.getCustomShapes().get(i);
            if ((shape_aux.getID() !== shape.getID()) && (shape_aux.type === shape.type)) {
//                    if (shape_aux.getType() === 'AdamActivity') {
                if (shape_aux.getName().toUpperCase() === nText.toUpperCase()) {
//                            t += 1;
                    str = translate('LBL_PMSE_MESSAGE_TASKNAMEALREADYEXISTS');
                    msg = str.replace('%s', nText);
                    rt = false;
                    break;
                }
//                    }
            }
        }
//            if (t > 1) {
//                msg = sprintf(translate('LBL_PMSE_MESSAGE_TASKNAMEALREADYEXISTS'), newText);
//                rt = false;
//            }
    }
//    }

    return {
        valid : rt,
        message : msg
    };
};
AdamCanvas.prototype.validatePositions = function (shape, coordinates) {
    var result = true;
    if (coordinates.y < shape.getZoomHeight() / 2) {
        result = false;
    }

    if (coordinates.y > (this.getHeight() - (shape.getZoomHeight() / 2) - 30)) {
        result = false;
    }

    if (coordinates.x < shape.getZoomWidth() / 2) {
        result = false;
    }
    if (coordinates.x > (this.getWidth() - (shape.getZoomWidth() / 2) - 50)) {
        result = false;
    }

    return result;
};

AdamCanvas.prototype.cleanAllFlowConditions = function () {
    var cleaned = 0,
        flow = this.connections.asArray(),
        i;
    for (i = 0; i < flow.length; i += 1) {
        if (flow[i].flo_condition !== '') {
            flow[i].flo_condition = '';
            cleaned += 1;
        }
    }
    return cleaned;
};

/**
 * Validate diagram respect BPMN 2.0 rules
 * @returns {BPMNCanvas}
 */
AdamCanvas.prototype.bpmnValidation = function () {
    var i, j,
        shape,
        rulesObject = this.bpmnRules,
        family,
        rules,
        message,
        testCount,
        objArray,
        sw;
    for (i = 0; i < this.getCustomShapes().getSize(); i += 1) {
        objArray = [];
        shape = this.getCustomShapes().get(i);
        family = shape.getFamilyNumber(shape);
        switch (family) {
            case 5:
                if (shape.getActivityType() === 'TASK'
                    || shape.getActivityType() === 'SUBPROCESS') {
                    objArray = rulesObject[shape.getType()]
                        ['task'];
                }
                break;
            case 6:
                if (rulesObject[shape.getType()] &&
                    rulesObject[shape.getType()]
                        [shape.getEventType().toLowerCase()]){
                    objArray = rulesObject[shape.getType()]
                        [shape.getEventType().toLowerCase()];
                }
                break;
            case 7:
                switch (shape.getDirection()) {
                    case 'CONVERGING':
                        objArray = rulesObject[shape.getType()]
                            ['converging'];
                        break;
                    case 'DIVERGING':
                        objArray = rulesObject[shape.getType()]
                            ['diverging'];
                        break;
                    case 'MIXED':
                        objArray = rulesObject[shape.getType()]
                            ['mixed'];
                        break;
                }

                break;
            case 9:
                if (rulesObject[shape.getType()] &&
                    rulesObject[shape.getType()]
                        [shape.getArtifactType().toLowerCase()]){
                    objArray = rulesObject[shape.getType()]
                        [shape.getArtifactType().toLowerCase()];
                }
                break;
        }
        shape.attachErrorToShape(objArray);
    }
    return this;
};

/*global jCore, $ */
/**
 * @class AdamMarker
 * Handle Activity Markers
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 */
var AdamMarker = function (options) {
    jCore.Shape.call(this, options);
    /**
     * Defines the positions of the markers
     * @type {Array}
     * @private
     */
    this.positions = ['left top', 'center top', 'right top',
        'left bottom', 'center bottom', 'right bottom'];
    /**
     * Defines the offset of the markers
     * @type {Array}
     * @private
     */
    this.offset =  ['5 5', '0 5', '0 0', '5 -1', '0 -1', '-5 -1'];
    /**
     * Define the marker type property
     * @type {null}
     */
    this.markerType = null;
    AdamMarker.prototype.initObject.call(this, options);
};
AdamMarker.prototype = new jCore.Shape();
/**
 * Defines the object type
 * @type {String}
 */
AdamMarker.prototype.type = 'AdamMarker';

/**
 * Initialize the object with the default values
 * @param {Object} options
 */
AdamMarker.prototype.initObject = function (options) {
    var defaults = {
        canvas: null,
        parent: null,
        position: 0,
        width: 19,
        height: 19,
        markerZoomClasses: [],
        markerType: null
    };
    $.extend(true, defaults, options);
    this.setParent(defaults.parent)
        .setPosition(defaults.position)
        .setHeight(defaults.height)
        .setWidth(defaults.width)
        .setMarkerZoomClasses(defaults.markerZoomClasses)
        .setMarkerType(defaults.markerType);
};

/**
 * Applies zoom to the Marker
 * @return {*}
 */
AdamMarker.prototype.applyZoom = function () {
    var newSprite;
    this.removeAllClasses();
    this.setProperties();
    newSprite = this.markerZoomClasses[this.parent.canvas.zoomPropertiesIndex];
    this.html.className = newSprite;
    this.currentZoomClass = newSprite;
    return this;
};

/**
 * Create the HTML for the marker
 * @return {*}
 */
AdamMarker.prototype.createHTML = function () {
    jCore.Shape.prototype.createHTML.call(this);

    this.html.id = this.id;
    this.setProperties();
    this.html.className = this.markerZoomClasses[
        this.parent.canvas.getZoomPropertiesIndex()
    ];
    this.currentZoomClass = this.html.className;
    this.parent.html.appendChild(this.html);
    return this.html;
};

/**
 * Updates the painting of the marker
 * @param update
 */
AdamMarker.prototype.paint = function (update) {
    if (this.getHTML() === null || update) {
        this.createHTML();
    }
    $(this.html).position({
        of: $(this.parent.html),
        my: this.positions[this.position],
        at: this.positions[this.position],
        offset: this.offset[this.position],
        collision: 'none'
    });
};

/**
 * Sets the marker type property
 * @param {String} newType
 * @return {*}
 */
AdamMarker.prototype.setMarkerType = function (newType) {
    this.markerType = newType;
    return this;
};

/**
 * Sets the position of the marker
 * @param {Number} newPosition
 * @return {*}
 */
AdamMarker.prototype.setPosition = function (newPosition) {
    if (newPosition !== null && typeof newPosition === 'number') {
        this.position = newPosition;
    }
    return this;
};

/**
 * Sets the parent of the marker
 * @param {AdamActivity} newParent
 * @return {*}
 */
AdamMarker.prototype.setParent = function (newParent) {
    this.parent = newParent;
    return this;
};

/**
 * Sets the elements class
 * @param eClass
 * @return {*}
 */
AdamMarker.prototype.setEClass = function (eClass) {
    this.currentZoomClass = eClass;
    return this;
};

/**
 * Sets the array of zoom classes
 * @param {Object} classes
 * @return {*}
 */
AdamMarker.prototype.setMarkerZoomClasses = function (classes) {
    this.markerZoomClasses = classes;
    return this;
};

/**
 * Sets the marker HTML properties
 * @return {*}
 */
AdamMarker.prototype.setProperties = function () {
    this.html.style.width = this.width * this.parent.getCanvas().getZoomFactor() + 'px';
    this.html.style.height = this.height * this.parent.getCanvas().getZoomFactor() + 'px';
    return this;
};

/**
 * Remove all classes of HTML
 * @return {*}
 */
AdamMarker.prototype.removeAllClasses = function () {
    this.html.className = '';
    return this;
};

AdamMarker.prototype.setElementClass = function (newClassArray) {
    var newSprite;
    this.setEClass(newClassArray);
    this.removeAllClasses();
    this.applyZoom();
    return this;
};
/*global AdamShape, $, Action, translate, AdamShapeLayerCommand, RestProxy,
 SUGAR_URL, CriteriaField, PMSE_DECIMAL_SEPARATOR, ComboboxField, HiddenField,
 TextField, PROJECT_MODULE, CheckboxField, DateField, RadiobuttonField, Form,
 PMSE_DESIGNER_FORM_TRANSLATIONS, Window, MessagePanel
*/
/**
 * @class AdamEvent
 * Handle BPMN Events
 * @extend AdamShape
 *
 * @constructor
 * Create a new event
 * @param {Object} options
 */
var AdamEvent = function (options) {
    AdamShape.call(this, options);
    /**
     * Defines the alphanumeric unique code
     * @type {String}
     */
    this.evn_uid = null;
    /**
     * Defines the event type
     * @type {String}
     */
    this.evn_type = null;
    /**
     * Defines the event marker supported
     * @type {String}
     */
    this.evn_marker = null;
    /**
     * Defines id the event interrups or not the execution
     * @type {Boolean}
     */
    this.evn_is_interrupting = true;
    /**
     * Defines the activity attachec when the event is a boundary element
     * @type {String}
     */
    this.evn_attached_to = null;
    /**
     * Defines if the event can cancel the activity attached to
     * @type {Boolean}
     */
    this.evn_cancel_activity = false;
    /**
     * Define the activity related when event is playing as transactional event
     * @type {String}
     */
    this.evn_activity_ref = null;
    /**
     * Defines if the event needs to wait for completation status
     * @type {Boolean}
     */
    this.evn_wait_for_completion = false;
    /**
     * Defines the error name when event is playing like an error event
     * @type {String}
     */
    this.evn_error_name = null;
    /**
     * Defines the error code when event is playing like an error event
     * @type {String}
     */
    this.evn_error_code = null;
    /**
     * Defines the escalation name when event is playing like an escalation event
     * @type {String}
     */
    this.evn_escalation_name = null;
    /**
     * Defines the escalation name when event is playing like an escalation event
     * @type {String}
     */
    this.evn_escalation_code = null;
    /**
     * Defines the condition on the event
     * @type {String}
     */
    this.evn_condition = null;
    /**
     * Defines the message association
     * @type {String}
     */
    this.evn_message = null;
    /**
     * Defines the operation tom be executed when event is used like a transactional event
     * @type {String}
     */
    this.evn_operation_name = null;
    /**
     * XXXX
     * @type {String}
     */
    this.evn_operation_implementation = null;
    /**
     * Defines the date to be executed a timer event
     * @type {String}
     */
    this.evn_time_date = null;
    /**
     * Defines the time cycle to be executed a timer event
     * @type {String}
     */
    this.evn_time_cycle = null;
    /**
     * Defines the duration of the timer event
     * @type {String}
     */
    this.evn_time_duration = null;
    /**
     * Define the behavior of the event. Valid values are: CATCH, THROW
     * @type {String}
     */
    this.evn_behavior = null;

    /**
     * Defines the order of the boundary event when is attached to an activity
     * @type {Number}
     */
    this.numberRelativeToActivity = 0;

    /**
     * Array of markers added to this activity
     * @type {Array}
     */
    this.markersArray = new jCore.ArrayList();

    AdamEvent.prototype.initObject.call(this, options);
};

/**
 * Point the prototype to the AdamShaoe object
 * @type {AdamShape}
 */
AdamEvent.prototype = new AdamShape();

/**
 * Defines the object type
 * @type {String}
 */
AdamEvent.prototype.type = "AdamEvent";

/**
 * Initialize the object with default values
 * @param {Object} options
 */
AdamEvent.prototype.initObject = function (options) {
    var defaults = {
        evn_is_interrupting: true,
        evn_message: '',
        evn_marker: 'EMPTY',
        evn_type: 'start',
        evn_behavior: 'catch'
    };
    $.extend(true, defaults, options);
    this.setEventUid(defaults.evn_uid)
        .setEventType(defaults.evn_type)
        .setEventMarker(defaults.evn_marker)
        .setEventMessage(defaults.evn_message)
        .setBehavior(defaults.evn_behavior)
        .setCondition(defaults.evn_condition)
        .setAttachedTo(defaults.evn_attached_to)
        .setIsInterrupting(defaults.evn_is_interrupting);
    if (defaults.evn_name) {
        this.setName(defaults.evn_name);
    }
};

/**
 * Sets the event uid property
 * @param {String} id
 * @return {*}
 */
AdamEvent.prototype.setEventUid = function (id) {
    this.evn_uid = id;
    return this;
};

/**
 * Sets the event type property
 * @param {String} type
 * @return {*}
 */
AdamEvent.prototype.setEventType = function (type) {
    var defaultTypes = {
        start: 'START',
        end: 'END',
        intermediate: 'INTERMEDIATE',
        boundary: 'BOUNDARY'
    };
    if (defaultTypes[type]) {
        this.evn_type = defaultTypes[type];
    }
    return this;
};

/**
 * Sets the event marker property
 * @param {String} marker
 * @return {*}
 */
AdamEvent.prototype.setEventMarker = function (marker) {
    this.evn_marker = marker;
    return this;
};

/**
 * Sets if the event interrups the execution or not
 * @param {Boolean} value
 * @return {*}
 */
AdamEvent.prototype.setIsInterrupting = function (value) {
    //if (_.isBoolean(value)) {
    if (value instanceof Boolean) {
        this.evn_is_interrupting = value;
    }
    return this;
};

/**
 * Sets the event behavior property
 * @param {String} behavior
 * @return {*}
 */
AdamEvent.prototype.setBehavior = function (behavior) {
    var defaultBehaviors = {
        "catch": 'CATCH',
        "throw": 'THROW'
    };
    if (defaultBehaviors[behavior]) {
        this.evn_behavior = defaultBehaviors[behavior];
    }
    return this;
};

/**
 * Sets the activity id where the event is attached to
 * @param {String} value
 * @param {Boolean} [cancel]
 * @return {*}
 */
AdamEvent.prototype.setAttachedTo = function (value, cancel) {
    //if (typeof cancel !== 'undefined') {
    if (cancel !== undefined) {
        //if (_.isBoolean(cancel)) {
        if (cancel instanceof Boolean) {
            this.evn_cancel_activity = cancel;
        }
    } else {
        this.evn_cancel_activity = this.evn_cancel_activity || false;
    }
    this.evn_attached_to = value;
    return this;
};

/**
 * Destroy a event
 * @returns {AdamEvent}
 */
AdamEvent.prototype.destroy = function () {
    if (this.getType() === 'AdamEvent' && this.getEventType() === 'BOUNDARY') {
        if (this.parent.boundaryPlaces && this.numberRelativeToActivity !==  null) {
            this.parent.boundaryPlaces
                .get(this.numberRelativeToActivity)
                .available = true;
            this.parent.boundaryArray.remove(this);

        }
    }
    return this;
};

/**
 * Sets the event message
 * @param {String} msg
 * @return {*}
 */
AdamEvent.prototype.setEventMessage = function (msg) {
    this.evn_message = msg;
    return this;
};

/**
 * Sets the event condition property
 * @param {String} value
 * @return {*}
 */
AdamEvent.prototype.setCondition = function (value) {
    this.evn_condition = value;
    return this;
};

/**
 * Set the compensation properties
 * @param {String} activity
 * @param {Boolean} wait
 * @return {*}
 */
AdamEvent.prototype.setCompensationActivity = function (activity, wait) {
    //if (typeof wait !== 'undefined') {
    if (wait) {
        //if (_.isBoolean(wait)) {
        if (wait instanceof Boolean) {
            this.evn_wait_for_completion = wait;
        }
    } else {
        this.evn_wait_for_completion = this.evn_wait_for_completion || false;
    }
    this.evn_activity_ref = activity;
    return this;
};

/**
 * Sets the error properties
 * @param {String} name  Error Name
 * @param {String} code  Error Code
 * @return {*}
 */
AdamEvent.prototype.setEventError = function (name, code) {
    this.evn_error_name = name;
    this.evn_error_code = code;
    return this;
};

/**
 * Sets the escalation properties
 * @param {String} name Escalation Name
 * @param {String} code Escalation Code
 * @return {*}
 */
AdamEvent.prototype.setEventEscalation = function (name, code) {
    this.evn_escalation_name = name;
    this.evn_escalation_code = code;
    return this;
};

/**
 * Sets the event operation properties
 * @param {String} name
 * @param {String} implementation
 * @return {*}
 */
AdamEvent.prototype.setEventOperation = function (name, implementation) {
    this.evn_operation_name = name;
    this.evn_operation_implementation = implementation;
    return this;
};

/**
 * Sets the event timer properties
 * @param {String} date
 * @param {String} cycle
 * @param {String} duration
 * @return {*}
 */
AdamEvent.prototype.setEventTimer = function (date, cycle, duration) {
    this.evn_time_date = date;
    this.evn_time_cycle = cycle;
    this.evn_time_duration = duration;
    return this;
};

/**
 * Sets te default_flow property
 * @param value
 * @return {*}
 */
AdamEvent.prototype.setDefaultFlow = function (value) {
    AdamShape.prototype.setDefaultFlow.call(this, value);
    this.evn_default_flow = value;
    return this;
};

/**
 * Returns the clean object to be sent to the backend
 * @return {Object}
 */
AdamEvent.prototype.getDBObject = function () {
    var container,
        element_id,
        name = this.getName();
    if (this.evn_type === 'BOUNDARY') {
        container = 'bpmnActivity';
        element_id = this.evn_attached_to;
    } else {
        container = 'bpmnDiagram';
        element_id = this.canvas.dia_id;
    }
    return {
        evn_uid: this.evn_uid,
        evn_name: name,
        evn_type: this.evn_type,
        evn_marker: this.evn_marker,
        evn_is_interrupting: this.evn_is_interrupting,
        evn_attached_to: this.evn_attached_to,
        evn_cancel_activity: this.evn_cancel_activity,
        evn_activity_ref: this.evn_activity_ref,
        evn_wait_for_completion: this.evn_wait_for_completion,
        evn_error_name: this.evn_error_name,
        evn_error_code: this.evn_error_code,
        evn_escalation_name: this.evn_escalation_name,
        evn_escalation_code: this.evn_escalation_code,
        evn_condition: this.evn_condition,
        evn_message: this.evn_message,
        evn_operation_name: this.evn_operation_name,
        evn_operation_implementation: this.evn_operation_implementation,
        evn_time_date: this.evn_time_date,
        evn_time_cycle: this.evn_time_cycle,
        evn_time_duration: this.evn_time_duration,
        evn_behavior: this.evn_behavior,
        bou_x: this.x,
        bou_y: this.y,
        bou_width: this.width,
        bou_height: this.height,
        bou_container: container,
        element_id: element_id
    };
};

/**
 * Attach the event to an activity
 * @return {*}
 */
AdamEvent.prototype.attachToActivity = function () {
    var numBou = this.parent.getAvailableBoundaryPlace();
    if (numBou !== false) {
        this.parent.setBoundary(this, numBou);
        this.setNumber(numBou);
    } else {
        this.destroy();
        this.saveAndDestroy();
    }
    return this;
};

/**
 * Sets the number/order of the current event when is attached to an activity
 * @param {Number} num
 * @return {*}
 */
AdamEvent.prototype.setNumber = function (num) {
    this.numberRelativeToActivity = num;
    return this;
};

/**
 * Create HTML css classes to identify events
 */
AdamEvent.prototype.createHTML = function () {
    AdamShape.prototype.createHTML.call(this);
    if (this.evn_type === "BOUNDARY") {
        this.style.addClasses(['adam_boundary_event']);
    } else {
        this.style.addClasses(['adam_event', 'adam_droppable']);
    }
    return this.html;
};

AdamEvent.prototype.getEventType = function () {
    return this.evn_type;
};

AdamEvent.prototype.getEventMarker = function () {
    return this.evn_marker;
};

AdamEvent.prototype.getEventMessage = function () {
    return this.evn_message;
};

AdamEvent.prototype.getContextMenu = function () {
    var deleteAction, leadAction, opportunityAction, documentAction, otherAction,
        msgCatchAction, msgThrowAction, timerAction, endEmptyAction, endMessageAction, endTerminateAction,
        boundaryMessageAction, boundaryTimerAction,
        startAction, intermediateAction, endAction,
        modulesMenu, typeMenu,
        self = this,
        configureAction,
        mitems = [];

    configureAction = this.createConfigureAction();

    startAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_START_EVENT'),
        handler: function () {
            self.updateEventType('START');
        },
        disabled: (this.evn_type === 'START')
    });

    intermediateAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_INTERMEDIATE_EVENT'),
        handler: function () {
            self.updateEventType('INTERMEDIATE');
        },
        disabled: (this.evn_type === 'INTERMEDIATE')
    });

    endAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_END_EVENT'),
        handler: function () {
            self.updateEventType('END');
        },
        disabled: (this.evn_type === 'END')
    });

    typeMenu = {
        items: [
            startAction,
            intermediateAction,
            endAction
        ]
    };

    deleteAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_DELETE'),
        cssStyle: 'adam-menu-icon-delete',
        handler: function () {
            var shape;
            shape = self.canvas.customShapes.find('id', self.id);
            if (shape) {
                shape.canvas.emptyCurrentSelection();
                shape.canvas.addToSelection(shape);
                shape.canvas.removeElements();
            }
        }
    });

    leadAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_LEADS'),
        cssStyle : 'adam-menu-icon-event-leads',
        handler: function () {
            self.updateEventMarker({
                evn_marker: 'MESSAGE',
                evn_message: 'Leads',
                evn_behavior: 'CATCH'
            });
        },
        disabled: (this.evn_marker === 'MESSAGE') &&
                  (this.evn_behavior === 'CATCH') &&
                  (this.evn_type === 'START') &&
                  (this.evn_message === 'Leads')
    });

    opportunityAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_OPPORTUNITIES'),
        cssStyle : 'adam-menu-icon-event-opportunities',
        handler: function () {
            self.updateEventMarker({
                evn_marker: 'MESSAGE',
                evn_message: 'Opportunities',
                evn_behavior: 'CATCH'
            });
        },
        disabled: (this.evn_marker === 'MESSAGE') &&
                  (this.evn_behavior === 'CATCH') &&
                  (this.evn_type === 'START') &&
                  (this.evn_message === 'Opportunities')
    });

    documentAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_DOCUMENTS'),
        cssStyle : 'adam-menu-icon-event-documents',
        handler: function () {
            self.updateEventMarker({
                evn_marker: 'MESSAGE',
                evn_message: 'Documents',
                evn_behavior: 'CATCH'
            });
        },
        disabled: (this.evn_marker === 'MESSAGE') &&
                  (this.evn_behavior === 'CATCH') &&
                  (this.evn_type === 'START') &&
                  (this.evn_message === 'Documents')
    });

    otherAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_OTHER_MODULES'),
        handler: function () {
            self.updateEventMarker({
                evn_marker: 'MESSAGE',
                evn_message: '',
                evn_behavior: 'CATCH'
            });
        },
        disabled: (this.evn_marker === 'MESSAGE') &&
                  (this.evn_behavior === 'CATCH') &&
                  (this.evn_type === 'START') &&
                  (this.evn_message === '' || this.evn_message === null)
    });

    msgCatchAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_RECEIVE_MESSAGE'),
        cssStyle: 'adam-menu-icon-event-recive-message',
        handler: function () {
            self.updateEventMarker({
                evn_marker: 'MESSAGE',
                evn_behavior: 'CATCH'
            });
        },
        disabled: (this.evn_marker === 'MESSAGE') &&
                  (this.evn_behavior === 'CATCH') &&
                  (this.evn_type === 'INTERMEDIATE')
    });
    msgThrowAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_SEND_MESSAGE'),
        cssStyle: 'adam-menu-icon-event-send-message',
        handler: function () {
            self.updateEventMarker({
                evn_marker: 'MESSAGE',
                evn_behavior: 'THROW'
            });
        },
        disabled: (this.evn_marker === 'MESSAGE') &&
                  (this.evn_behavior === 'THROW') &&
                  (this.evn_type === 'INTERMEDIATE')
    });

    timerAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_TIMER'),
        cssStyle: 'adam-menu-icon-event-timer',
        handler: function () {
            self.updateEventMarker({
                evn_marker: 'TIMER',
                evn_behavior: 'CATCH'
            });
        },
        disabled: (this.evn_marker === 'TIMER') &&
                  (this.evn_behavior === 'CATCH') &&
                  (this.evn_type === 'INTERMEDIATE')
    });

    endEmptyAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_DO_NOTHING'),
        handler: function () {
            self.updateEventMarker({
                evn_marker: 'EMPTY',
                evn_behavior: 'THROW'
            });
        },
        disabled: (this.evn_marker === 'EMPTY') &&
                  (this.evn_behavior === 'THROW') &&
                  (this.evn_type === 'END')
    });

    endMessageAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_SEND_MESSAGE'),
        cssStyle: 'adam-menu-icon-event-send-message',
        handler: function () {
            self.updateEventMarker({
                evn_marker: 'MESSAGE',
                evn_behavior: 'THROW'
            });
        },
        disabled: (this.evn_marker === 'MESSAGE') &&
                  (this.evn_behavior === 'THROW') &&
                  (this.evn_type === 'END')
    });

    endTerminateAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_TERMINATE_PROCESS'),
        cssStyle: 'adam-menu-icon-event-terminate-process',
        handler: function () {
            self.updateEventMarker({
                evn_marker: 'TERMINATE',
                evn_behavior: 'THROW'
            });
        },
        disabled: (this.evn_marker === 'TERMINATE') &&
                  (this.evn_behavior === 'THROW') &&
                  (this.evn_type === 'END')
    });

    boundaryMessageAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_RECEIVE_MESSAGE'),
        cssStyle: 'adam-menu-icon-event-recive-message',
        handler: function () {
            self.updateEventMarker({
                evn_marker: 'MESSAGE',
                evn_behavior: 'CATCH'
            });
        },
        disabled: (this.evn_marker === 'MESSAGE') &&
                  (this.evn_behavior === 'CATCH') &&
                  (this.evn_type === 'BOUNDARY')
    });

    boundaryTimerAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_TIMER'),
        cssStyle: 'adam-menu-icon-event-timer',
        handler: function () {
            self.updateEventMarker({
                evn_marker: 'TIMER',
                evn_behavior: 'CATCH'
            });
        },
        disabled: (this.evn_marker === 'TIMER') &&
                  (this.evn_behavior === 'CATCH') &&
                  (this.evn_type === 'BOUNDARY')
    });

    modulesMenu = {
        label: '',
        menu: {
            items: []
        }
    };
    switch (this.evn_type) {
    case 'START':
        modulesMenu.label = translate('LBL_PMSE_CONTEXT_MENU_LISTEN');
        modulesMenu.menu.items.push(leadAction);
        modulesMenu.menu.items.push(opportunityAction);
        modulesMenu.menu.items.push(documentAction);
        modulesMenu.menu.items.push(otherAction);
        break;
    case 'INTERMEDIATE':
        modulesMenu.label = translate('LBL_PMSE_CONTEXT_MENU_ACTION');
        modulesMenu.menu.items.push(msgCatchAction);
        modulesMenu.menu.items.push(msgThrowAction);
        modulesMenu.menu.items.push(timerAction);
        break;
    case 'END':
        modulesMenu.label = translate('LBL_PMSE_CONTEXT_MENU_RESULT');
        modulesMenu.menu.items.push(endEmptyAction);
        modulesMenu.menu.items.push(endMessageAction);
        modulesMenu.menu.items.push(endTerminateAction);
        break;
    case 'BOUNDARY':
        modulesMenu.label = translate('LBL_PMSE_CONTEXT_MENU_EVENT');
        modulesMenu.menu.items.push(boundaryMessageAction);
        modulesMenu.menu.items.push(boundaryTimerAction);
        break;
    }
    modulesMenu.icon = 'adam-menu-icon-convert';

    // if ((this.evn_marker === 'MESSAGE') &&
    //               (this.evn_behavior === 'CATCH') &&
    //               (this.evn_type === 'INTERMEDIATE')){
    //     configureAction.setDisabled(true);
    // }
    mitems.push(
        configureAction,
        {
            jtype: 'separator'
        }
    );
    if (this.evn_type !== 'START') {
        mitems.push(
            modulesMenu,
            {
                jtype: 'separator'
            }
        );
    }
    mitems.push(deleteAction);

    return {
        items: mitems
    };
};

AdamEvent.prototype.updateEventType = function (newType) {
    var layer1, layer2, updateCommand, newChanges;

    layer1 = this.getLayers().get(0);
    layer2 = this.getLayers().get(1);

    switch (newType) {
    case 'START':
        newChanges = {
            evn_type: newType,
            evn_behavior: 'CATCH',
            evn_marker: 'MESSAGE',
            evn_message: ''
        };
        break;
    case 'INTERMEDIATE':
        newChanges = {
            evn_type: newType,
            evn_behavior: 'CATCH',
            evn_marker: 'TIMER',
            evn_message: ''
        };
        break;
    case 'END':
        newChanges = {
            evn_type: newType,
            evn_behavior: 'THROW',
            evn_marker: 'TERMINATE',
            evn_message: ''
        };
        break;
    }

    updateCommand = new AdamShapeLayerCommand(
        this,
        {
            layers: [layer1, layer2],
            type: 'changeeventtype',
            changes: newChanges
        }
    );
    updateCommand.execute();

    this.canvas.commandStack.add(updateCommand);
    return this;
};

AdamEvent.prototype.updateEventMarker = function (options) {
    var layer,
        updateCommand;

    layer = this.getLayers().get(1);
    updateCommand = new AdamShapeLayerCommand(
        this,
        {
            layers: [layer],
            type: 'changeeventmarker',
            changes: options
        }
    );
    updateCommand.execute();

    this.canvas.commandStack.add(updateCommand);
    return this;
};

AdamEvent.prototype.createConfigureAction = function () {
    var action, w, f, proxy, items, wWidth, wHeight, changeModule, initialValue = null, disabled = false,
        startCriteria = null, oldModule, newModule, mp, cancelInformation, actiontimerType, durationRadio, i,
        repeatEveryCombo, everyOptions, repeatEveryNumberCombo, cyclicDate, fixedRadio, cyclicRadio, incrementWasClicked = false,
        durationTextField, unitComboBox, fixedDate, incrementCkeck, durationTextField2, unitComboBox2, operationCombo, criteria,
        root = this, hiddenParams, hiddenFn, callback = {}, ddlModules, ddlEmailTemplate, aTemplate, criteriaField, emailTemplates, datecriteria;

    //Event Form Proxy
    proxy = new SugarProxy({
        url:'pmse_Project/EventDefinition/' + this.id,
        uid: this.id,
        callback: null
    });

    if (this.evn_type === 'START' && (this.evn_message === '' || this.evn_message === null)) {
        changeModule = true;
    } else {
        changeModule = false;
        switch (this.evn_message) {
        case 'Leads':
            initialValue = 'Leads';
            break;
        case 'Opportunities':
            initialValue = 'Opportunities';
            break;
        case 'Documents':
            initialValue = 'Documents';
            break;
        }
    }

    switch (this.evn_type) {
    case 'START':
        criteriaField = new CriteriaField({
            name: 'evn_criteria',
            label: translate('LBL_PMSE_FORM_LABEL_CRITERIA'),
            required: false,
            fieldWidth: 250,
            fieldHeight: 80,
            decimalSeparator: SUGAR.App.config.defaultDecimalSeparator,
            numberGroupingSeparator: SUGAR.App.config.defaultNumberGroupingSeparator,
            operators: {
                logic: true,
                group: true
            },
            constant: false
        });

        ddlModules = new ComboboxField({
            jtype: 'combobox',
            name: 'evn_module',
            label: translate('LBL_PMSE_FORM_LABEL_MODULE'),
            required: true,
            readOnly: !changeModule,
            initialValue: initialValue,
            helpTooltip: {
                message: translate('LBL_PMSE_FORM_TOOLTIP_EVENT_MODULE')
            },
            change: function (combo, newValue, oldValue) {
                if (criteriaField.getItems().length > 0) {
                    mp.show(newValue, oldValue);
                } else {
                    //criteriaField.setRelatedModulesDataURL('pmse_Project/CrmData/related/' + this.value);//criteriaField.setBaseModule(this.value);
                    criteriaField.setModuleEvaluation({
                        dataURL: "pmse_Project/CrmData/related/" + this.value,
                        dataRoot: 'result',
                        textField: "text",
                        valueField: "value",
                        fieldDataURL: 'pmse_Project/CrmData/fields/{{MODULE}}',
                        fieldDataRoot: 'result',
                        fieldTextField: "text",
                        fieldValueField: "value",
                        fieldTypeField: "type"
                    })
                }
            },
            related: 'modules',
            //deprecated
            proxy: new SugarProxy({
                url: 'pmse_Project/CrmData/related_modules/' + PROJECT_MODULE,
                //restClient: this.canvas.project.restClient,
                uid: PROJECT_MODULE,
                callback: null
            })
        });
        items = [
            ddlModules,
            {
                jtype: 'combobox',
                name: 'evn_params',
                label: translate('LBL_PMSE_FORM_LABEL_APPLIES_TO'),
                options: [
                    {
                        text: translate('LBL_PMSE_FORM_OPTION_SELECT'),
                        value: ''
                    },
                    {
                        text: translate('LBL_PMSE_FORM_OPTION_NEW_RECORDS_ONLY'),
                        value: 'new'
                    },
                    {
                        text: translate('LBL_PMSE_FORM_OPTION_UPDATED_RECORDS_ONLY'),
                        value: 'updated'
                    }
                ],
                required: true,
                helpTooltip: {
                    message: translate('LBL_PMSE_FORM_TOOLTIP_WHEN_START_EVENT')
                }
            },
            criteriaField
        ];
        wHeight = 280;
        wWidth = 500;
        callback = {
            loaded: function (data) {
                //console.log('Event "loaded" for ' + this.id + " triggered", data);
                root.canvas.emptyCurrentSelection();
                ddlModules.proxy.getData(null,{
                    success: function(modules) {
                        //console.log(modules.result);
                        //console.log(data);
                        //ddlModules.setOptions(modules.result);
                        //ddlModules.setValue(data.evn_module || (modules.result[0].value || null));
                        ddlModules.setValue(root.evn_message || (modules.result[0].value || null));
                        oldModule = data.evn_module;
                        //console.log(oldModule, ddlModules.value);
                        //criteriaField.setRelatedModulesDataURL("pmse_Project/CrmData/related/" + ddlModules.value); //criteriaField.setBaseModule(ddlModules.value);
                        criteriaField.setModuleEvaluation({
                            dataURL: "pmse_Project/CrmData/related/" + ddlModules.value,
                            dataRoot: 'result',
                            textField: "text",
                            valueField: "value",
                            fieldDataURL: 'pmse_Project/CrmData/fields/{{MODULE}}',
                            fieldDataRoot: 'result',
                            fieldTextField: "text",
                            fieldValueField: "value",
                            fieldTypeField: "type"
                        }).setUserEvaluation({
                            defaultUsersDataURL: 'pmse_Project/CrmData/defaultUsersList',
                            defaultUsersDataRoot: 'result',
                            defaultUsersValueField: "value",
                            userRolesDataURL: 'pmse_Project/CrmData/rolesList',
                            userRolesDataRoot: 'result',
                            usersDataURL: 'pmse_Project/CrmData/users',
                            usersDataRoot: 'result',
                            usersValueField: "value"
                        });
                        App.alert.dismiss('upload');
                        w.html.style.display = 'inline';
                    }
                });
            }
        };
        mp = {
            _messagePanel: null,
            show: function(comboNewValue, comboOldValue) {
                this._messagePanel = new MessagePanel({
                    title: "Module change warning",
                    wtype: 'Confirm',
                    message: translate('LBL_PMSE_MESSAGE_REMOVE_ALL_START_CRITERIA'),
                    buttons: [
                        {
                            jtype: 'normal',
                            caption: translate('LBL_PMSE_BUTTON_OK'),
                            handler: function () {
                                //criteriaField.clear().setRelatedModulesDataURL("pmse_Project/CrmData/related/" + comboNewValue); //criteriaField.clear().setBaseModule(ddlModules.value);
                                criteriaField.clear().setModuleEvaluation({
                                    dataURL: "pmse_Project/CrmData/related/" + comboNewValue,
                                    dataRoot: 'result',
                                    textField: "text",
                                    valueField: "value",
                                    fieldDataURL: 'pmse_Project/CrmData/fields/{{MODULE}}',
                                    fieldDataRoot: 'result',
                                    fieldTextField: "text",
                                    fieldValueField: "value",
                                    fieldTypeField: "type"
                                });
                                mp.hide();
                            }
                        },
                        {
                            jtype: 'normal',
                            caption: translate('LBL_PMSE_BUTTON_CANCEL'),
                            handler: function () {
                                ddlModules.setValue(comboOldValue);
                                mp.hide();
                            }
                        }
                    ]
                });
                this._messagePanel.show();
            },
            hide: function() {
                if (this._messagePanel) {
                    this._messagePanel.hide();
                }
            }
        };
        /*mp = new MessagePanel({
            title: "Module change warning",
            wtype: 'Confirm',
            message: translate('LBL_PMSE_MESSAGE_REMOVEALLSTARTCRITERIA'),
            buttons: [
                {
                    jtype: 'normal',
                    caption: translate('LBL_PMSE_BUTTON_OK'),
                    handler: function () {
                        criteriaField.clear().setRelatedModulesDataURL("pmse_Project/CrmData/related/" + ddlModules.value); //criteriaField.clear().setBaseModule(ddlModules.value);
                        mp.hide();
                    }
                },
                {
                    jtype: 'normal',
                    caption: translate('LBL_PMSE_BUTTON_CANCEL'),
                    handler: function () {
                        ddlModules.setValue(criteriaField.base_module);
                        mp.hide();
                    }
                }
            ]
        });*/
        break;
    case 'INTERMEDIATE':
        if (this.evn_marker === 'MESSAGE') {
            if (this.evn_behavior === 'THROW') {
                ddlEmailTemplate = new ComboboxField({
                    jtype: 'combobox',
                    required: true,
                    //related: 'templates',
                    name: 'evn_criteria',
                    label: translate('LBL_PMSE_FORM_LABEL_EMAIL_TEMPLATE'),
                    proxy: new SugarProxy({
                        url: 'pmse_Project/CrmData/emailtemplates',
                        uid: "",
                        callback: null
                    })
                });
                ddlModules = new ComboboxField({
                    jtype: 'combobox',
                    required: true,
                    //related: 'beans',
                    name: 'evn_module',
                    label: translate('LBL_PMSE_FORM_LABEL_MODULE'),
                    proxy: new SugarProxy({
                        url: 'pmse_Project/CrmData/modules',
                        uid: "",
                        callback: null
                    }),
                    change: function () {
                        ddlEmailTemplate.proxy.uid = this.value;
                        ddlEmailTemplate.proxy.url = 'pmse_Project/CrmData/emailtemplates/' + this.value;
                        ddlEmailTemplate.removeOptions();
                        aTemplate = [{'text': translate('LBL_PMSE_FORM_OPTION_SELECT'), 'value': ''}];
                        ddlEmailTemplate.proxy.getData(null,{
                            success: function(emailTemplates){
                                aTemplate = aTemplate.concat(emailTemplates.result);

                                //if(emailTemplates && emailTemplates.success) {
                                ddlEmailTemplate.setOptions(aTemplate);
                            }
                        });

                        //}
                    }
                });
                hiddenParams = new HiddenField({name: 'evn_params'});
                hiddenFn = function () {
                    var parentForm = this.parent, address = {};

                    //address['to'] = parentForm.items[2].getObject();
                    //address['cc'] = parentForm.items[3].getObject();
                    //address['bcc'] = parentForm.items[4].getObject();
                    address.to = parentForm.items[2].getObject();
                    address.cc = parentForm.items[3].getObject();
                    address.bcc = parentForm.items[4].getObject();
                    hiddenParams.setValue(JSON.stringify(address));
                };
                items = [
                    ddlModules,
                    ddlEmailTemplate,
                    {
                        jtype: 'emailpicker',
                        label: translate('LBL_PMSE_FORM_LABEL_EMAIL_TO'),
                        name: 'address_to',
                        required: true,
                        submit: false,
                        fieldWidth: 250,
                        fieldHeight: 50,
                        change: hiddenFn,
                        suggestionItemName: 'fullName',
                        suggestionItemAddress: 'emailAddress',
                        suggestionDataURL: "pmse_Project/CrmData/emails/{$0}",
                        suggestionDataRoot: "result",
                        teams: project.getMetadata('teams') || []
                    },
                    {
                        jtype: 'emailpicker',
                        label: translate('LBL_PMSE_FORM_LABEL_EMAIL_CC'),
                        name: 'address_cc',
                        required: false,
                        submit: false,
                        fieldWidth: 250,
                        fieldHeight: 50,
                        change: hiddenFn,
                        suggestionItemName: 'fullName',
                        suggestionItemAddress: 'emailAddress',
                        suggestionDataURL: "pmse_Project/CrmData/emails/{$0}",
                        suggestionDataRoot: "result",
                        teams: project.getMetadata('teams') || []
                    },
                    {
                        jtype: 'emailpicker',
                        label: translate('LBL_PMSE_FORM_LABEL_EMAIL_BCC'),
                        name: 'address_bcc',
                        required: false,
                        submit: false,
                        fieldWidth: 250,
                        fieldHeight: 50,
                        change: hiddenFn,
                        suggestionItemName: 'fullName',
                        suggestionItemAddress: 'emailAddress',
                        suggestionDataURL: "pmse_Project/CrmData/emails/{$0}",
                        suggestionDataRoot: "result",
                        teams: project.getMetadata('teams') || []
                    },
                    hiddenParams
                ];
                wHeight = 380;
                wWidth = 500;
                callback = {
                    loaded: function (data) {
                        var params = null, i, emailPickerFields = [], dataSource;
                        root.canvas.emptyCurrentSelection();
                        if (data && data.evn_params) {
                            try {
                                params = JSON.parse(data.evn_params);
                            } catch (e) {}
                            if (params) {
                                hiddenParams.setValue(data.evn_params);
                                for (i = 0; i < f.items.length; i += 1) {
                                    switch (f.items[i].name) {
                                    case 'address_to':
                                        f.items[i].setValue(params.to);
                                        emailPickerFields.push(i);
                                        break;
                                    case 'address_cc':
                                        f.items[i].setValue(params.cc);
                                        emailPickerFields.push(i);
                                        break;
                                    case 'address_bcc':
                                        f.items[i].setValue(params.bcc);
                                        emailPickerFields.push(i);
                                        break;
                                    }
                                }
                            }
                        }

                        /*dataSource = project.getMetadata('targetModuleFieldsDataSource');
                        dataSource.url = dataSource.url.replace("{MODULE}", PROJECT_MODULE);
                        dataSource = project.addMetadata("targetModuleFields", {
                          dataURL: dataSource.url,
                          dataRoot: dataSource.root,
                          success: function (data) {
                            f.items[2].setVariables({
                              data: data
                            });
                            f.items[3].setVariables({
                              data: data
                            });
                            f.items[4].setVariables({
                              data: data
                            });
                          }
                        });*/

                        ddlModules.proxy.getData(null, {
                            success: function(params) {
                                if (params && params.result) {
                                    //console.log(params.result);
                                    ddlModules.setOptions(params.result);
                                    ddlModules.setValue(data.evn_module || ((params.result[0] && params.result[0].value) || null));
                                }

                                ddlEmailTemplate.proxy.uid = ddlModules.value;
                                ddlEmailTemplate.proxy.url = 'pmse_Project/CrmData/emailtemplates/' + ddlModules.value;
                                aTemplate = [{'text': translate('LBL_PMSE_FORM_OPTION_SELECT'), 'value': ''}];
                                ddlEmailTemplate.proxy.getData(null, {
                                    success: function(params2) {
                                        aTemplate = aTemplate.concat(params2.result);
                                        ddlEmailTemplate.setOptions(aTemplate);
                                        if (params2 && params2.result) {
                                            ddlEmailTemplate.setValue(data.evn_criteria || ((params2.result[0] && params2.result[0].value) || null));
                                        }
                                        App.alert.dismiss('upload');
                                        w.html.style.display = 'inline';
                                    }
                                });
                            }
                        });

                        //We load the teams
                        project.addMetadata("teams", {
                            dataURL: project.getMetadata("teamsDataSource").url, 
                            dataRoot: project.getMetadata("teamsDataSource").root,  
                            success: function(data) { 
                                var i;
                                if(emailPickerFields.length) {
                                    for (i = 0; i < emailPickerFields.length; i += 1) {
                                        f.items[emailPickerFields[i]].setTeamNameField("text");
                                        f.items[emailPickerFields[i]].setTeams(data);
                                    }    
                                } else {
                                    for (i = 0; i < f.items.length; i += 1) {
                                        switch (f.items[i].name) {
                                        case 'address_to':
                                        case 'address_cc':
                                        case 'address_bcc':
                                            f.items[i].setTeamNameField("text");
                                            f.items[i].setTeams(data);
                                            break;
                                        }
                                    }
                                }
                                
                            }
                        });
                    },
                    submit: function (data) {
                        //console.log(data);
                    }
                };
            } else {
                items = [
                    {
                        jtype: 'criteria',
                        name: 'evn_criteria',
                        label: translate('LBL_PMSE_FORM_LABEL_CRITERIA'),
                        required: false,
                        operators: {
                          logic: true,
                          group: true
                        },
                        constant: false,
                        evaluation: {
                          module: {
                            dataURL: "pmse_Project/CrmData/related/" + PROJECT_MODULE,
                            dataRoot: 'result',
                            fieldDataURL: 'pmse_Project/CrmData/fields/{{MODULE}}',
                            fieldDataRoot: 'result'
                          },
                          user: {
                              defaultUsersDataURL: "pmse_Project/CrmData/defaultUsersList",
                              defaultUsersDataRoot: "result",
                              userRolesDataURL: "pmse_Project/CrmData/rolesList",
                              userRolesDataRoot: "result",
                              usersDataURL: "pmse_Project/CrmData/users",
                              usersDataRoot: "result"
                          }
                        },
                        decimalSeparator: SUGAR.App.config.defaultDecimalSeparator,
                        numberGroupingSeparator: SUGAR.App.config.defaultNumberGroupingSeparator
                    }
                ];
                wHeight = 185;
                wWidth = 500;
                callback = {
                    loaded: function (data) {
                        root.canvas.emptyCurrentSelection();
                        App.alert.dismiss('upload');
                        w.html.style.display = 'inline';
                    }
                };
            }
        }
        if (this.evn_marker === 'TIMER') {
            actiontimerType = new HiddenField({name: 'evn_timer_type'});

            durationTextField  = new TextField(
                {
                    jtype: 'text',
                    //validators: [
                    //    {
                    //        jtype: 'integer',
                    //        errorMessage: translate('LBL_PMSE_ADAM_UI_ERROR_INVALID_INTEGER')
                    //    }
                    //],
                    name: 'evn_duration_criteria',
                    label: translate('LBL_PMSE_FORM_LABEL_DURATION'),
                    required: true,
                    helpTooltip: {
                        message: translate('LBL_PMSE_FORM_TOOLTIP_DURATION')
                    },
                    fieldWidth: '50px'
                    //readOnly: true
                }
            );

            unitComboBox = new ComboboxField(
                {
                    //jtype: 'combobox',
                    name: 'evn_duration_params',
                    label: translate('LBL_PMSE_FORM_LABEL_UNIT'),
                    options: [
                        { text: translate('LBL_PMSE_FORM_OPTION_DAYS'), value: 'day'},
                        { text: translate('LBL_PMSE_FORM_OPTION_HOURS'), value: 'hour'},
                        { text: translate('LBL_PMSE_FORM_OPTION_MINUTES'), value: 'minute'}
                    ],
                    initialValue: 'hour'
                    //required: true
                    //readOnly: true
                }
            );



            //repeatEveryCombo = new ComboboxField(
            //    {
            //        //jtype: 'combobox',
            //        name: 'evn_cyclic_repeat',
            //        label: translate('LBL_PMSE_LABEL_REPEATS'),
            //        options: [
            //            { text: translate('Every Day'), value: 'Every Day'},
            //            { text: translate('Every working days (Monday to Friday)'), value: 'Every working days (Monday to Friday)'},
            //            { text: translate('Every Monday, Wednesday and Friday'), value: 'Every Monday, Wednesday and Friday'},
            //            { text: translate('Every Tuesday and Thursday'), value: 'Every Tuesday and Thursday'},
            //            { text: translate('Every week'), value: 'Every week'},
            //            { text: translate('Every month'), value: 'Every month'},
            //            { text: translate('Every year'), value: 'Every year'}
            //        ],
            //        initialValue: 'Every Day',
            //        required: true
            //        //readOnly: true
            //    }
            //);
            everyOptions = [];
            for (i = 1; i <= 30; i += 1) {
                everyOptions.push({text: translate(i), value: i});
            }

            repeatEveryNumberCombo = new ComboboxField(
                {
                    //jtype: 'combobox',
                    name: 'evn_cyclic_repeat_every',
                    label: translate('LBL_PMSE_LABEL_REPEATSEVERY'),
                    options: everyOptions,
                    initialValue: 1,
                    required: true
                    //readOnly: true
                }
            );

            cyclicDate  = new DateField(
                {
                    name: 'evn_cyclic_date',
                    label: translate('LBL_PMSE_LABEL_BEGINS'),
                    required: true,
                    fieldWidth: '100px',
                    readOnly: true
                }
            );

            durationRadio = new RadiobuttonField({
                jtype: 'radio',
                name: 'evn_timer_type',
                label: translate('LBL_PMSE_FORM_LABEL_DURATION'),
                value : true,
                labelAlign: 'right',
                onClick: function (e, ui) {
                    actiontimerType.setValue('duration');

                    durationTextField.enable();
                    unitComboBox.enable();
                    datecriteria.disable();
                    datecriteria.clear();
                    datecriteria.isValid();
                    //fixedDate.disable();
                    //incrementCkeck.disable();
                    //durationTextField2.disable();
                    //unitComboBox2.disable();
                    //operationCombo.disable();
                    //repeatEveryCombo.disable();
                    //repeatEveryNumberCombo.disable();
                    //cyclicDate.disable();

                }
            });
            fixedRadio = new RadiobuttonField({
                jtype: 'radio',
                name: 'evn_timer_type',
                label: translate('LBL_PMSE_FORM_LABEL_FIXED_DATE'),
                reverse : true,
                labelAlign: 'right',
                onClick: function (e, ui) {
                    actiontimerType.setValue('fixed date');

                    durationTextField.disable();
                    unitComboBox.disable();

                    datecriteria.enable();
                    //fixedDate.enable();
                    //incrementCkeck.enable();
                    //if (!incrementWasClicked) {
                    //    durationTextField2.disable();
                    //    unitComboBox2.disable();
                    //    operationCombo.disable();
                    //} else {
                    //    durationTextField2.enable();
                    //    unitComboBox2.enable();
                    //    operationCombo.enable();
                    //}


                    //repeatEveryCombo.disable();
                    //repeatEveryNumberCombo.disable();
                    //cyclicDate.disable();

                }
            });
            /*datecriteria = new CriteriaField({
                name: 'evn_criteria',
                label: translate('LBL_PMSE_FORM_LABEL_CRITERIA'),
                required: false,
                fieldWidth: 300,
                fieldHeight: 80,
                timerCriteria: true,
                restClient: this.parent.project.restClient,
                panels: {
                    businessRulesEvaluation: {
                        enabled: false
                    },
                    formResponseEvaluation: {
                        enabled: false
                    },
                    logic: {
                        enabled: false
                    },
                    group: {
                        enabled: false
                    },
                    userEvaluation: {
                        enabled: false
                    },
                    fieldEvaluation: {
                        enabled: false
                    }

                },
                decimalSeparator: PMSE_DECIMAL_SEPARATOR
            });*/
            /*datecriteria = new ExpressionField({
                name: 'evn_criteria',
                label: translate('LBL_PMSE_LABEL_CRITERIA'),
                required: false,
                fieldWidth: 300,
                fieldHeight: 80,
                dateFormat: project.getMetadata("datePickerFormat"),
                variablesDataFormat: 'hierarchical',
                variablesChildRoot: 'fields',
                variablesGroupNameField: 'text',
                variablesGroupValueField: 'value',
                variableNameField: 'text',
                variableValueField: 'value',
                variableTypeField: "type",
                variableTypeFilter: ["Date", "Datetime"]
            });*/

            datecriteria = new CriteriaField({
                name: 'evn_criteria',
                label: translate('LBL_PMSE_LABEL_CRITERIA'),
                required: false,
                fieldWidth: 300,
                fieldHeight: 80,
                decimalSeparator: SUGAR.App.config.defaultDecimalSeparator,
                numberGroupingSeparator: SUGAR.App.config.defaultNumberGroupingSeparator,
                operators: {
                    arithmetic: ['+', '-']
                },
                constant: {
                    date: true,
                    timespan: true
                },
                variable: {
                    dataURL: project.getMetadata("fieldsDataSource").url.replace("{MODULE}", project.process_definition.pro_module),
                    dataRoot: project.getMetadata("fieldsDataSource").root,
                    dataFormat: "hierarchical",
                    dataChildRoot: "fields",
                    textField: "text",
                    valueField: "value",
                    typeField: "type",
                    typeFilter: ['Date', 'Datetime'],
                    moduleTextField: "text",
                    moduleValueField: "value"
                },
                dateFormat: project.getMetadata("datePickerFormat")
            });

            cyclicRadio = new RadiobuttonField({
                jtype: 'radio',
                name: 'evn_timer_type',
                label: translate('LBL_PMSE_LABEL_CYCLIC'),
                reverse : true,
                labelAlign: 'right',
                onClick: function (e, ui) {
                    actiontimerType.setValue('cyclic');
                    durationTextField.disable();
                    unitComboBox.disable();

                    //                    fixedDate.disable();
                    //                    incrementCkeck.disable();
                    //
                    //                    durationTextField2.disable();
                    //                    unitComboBox2.disable();
                    //                    operationCombo.disable();

                    //                    repeatEveryCombo.enable();
                    //                    repeatEveryNumberCombo.enable();
                    //                    cyclicDate.enable();

                }

            });

            items = [
                actiontimerType,
                durationRadio,
                durationTextField,
                unitComboBox,

                fixedRadio,
                datecriteria
                //                fixedDate,
                //                incrementCkeck,
                //                operationCombo,
                //                durationTextField2,
                //                unitComboBox2

                //                cyclicRadio,
                //                repeatEveryCombo,
                //                repeatEveryNumberCombo,
                //                cyclicDate
            ];
            wHeight = 420;
            wWidth = 500;
            callback = {
                loaded: function (data) {

                    /*project.addMetadata("fields", {
                        dataURL: project.getMetadata("fieldsDataSource").url.replace("{MODULE}", project.process_definition.pro_module),
                        dataRoot: project.getMetadata("fieldsDataSource").root,
                        success: function (data) {
                            //datecriteria.setVariablesData(data);
                        }
                    });*/

                    //datecriteria.setBaseModule(PROJECT_MODULE);
                    root.canvas.emptyCurrentSelection();
                    switch (data.evn_params) {
                    case 'fixed date':
                        durationRadio.setValue(false);
                        fixedRadio.setValue(true);
                        actiontimerType.setValue('fixed date');

                        durationTextField.disable();
                        unitComboBox.disable();
                        //  datecriteria.setBaseModule(PROJECT_MODULE);
                        datecriteria.enable();

                        break;
                    case 'cyclic':
                        actiontimerType.setValue('cyclic');

                        durationTextField.disable();
                        unitComboBox.disable();


                        break;
                    default:
                        actiontimerType.setValue('duration');
                        durationRadio.setValue(true);
                        fixedRadio.setValue(false);
                        durationTextField.enable();

                        durationTextField.setValue(data.evn_criteria || '');
                        unitComboBox.enable();
                        unitComboBox.setValue(data.evn_params || 'minute');
                        datecriteria.disable();

                        break;
                    }
                    App.alert.dismiss('upload');
                    w.html.style.display = 'inline';
                },
                submit: function (data) {

                }
            };
        }
        break;
    case 'END':
        if (this.evn_marker === 'MESSAGE') {
            ddlEmailTemplate = new ComboboxField({
                jtype: 'combobox',
                //required: true,
                //related: 'templates',
                name: 'evn_criteria',
                label: translate('LBL_PMSE_FORM_LABEL_EMAIL_TEMPLATE'),
                proxy: new SugarProxy({
                    url: 'pmse_Project/CrmData/emailtemplates/',
                    //restClient: this.canvas.project.restClient,
                    uid: "",
                    callback: null
                })
            });
            ddlModules = new ComboboxField({
                jtype: 'combobox',
                required: true,
                //related: 'beans',
                name: 'evn_module',
                label: translate('LBL_PMSE_FORM_LABEL_MODULE'),
                proxy: new SugarProxy({
                    url: 'pmse_Project/CrmData/modules/',
                    //restClient: this.canvas.project.restClient,
                    uid: "",
                    callback: null
                }),
                change: function () {
                    ddlEmailTemplate.proxy.uid = this.value;
                    ddlEmailTemplate.proxy.url = 'pmse_Project/CrmData/emailtemplates/' + this.value;
                    ddlEmailTemplate.removeOptions();
                    aTemplate = [{'text': translate('LBL_PMSE_FORM_OPTION_SELECT'), 'value': ''}];
                    emailTemplates = ddlEmailTemplate.proxy.getData(null,{
                        success: function(emailTemplates) {
                            aTemplate = aTemplate.concat(emailTemplates.result);
                            //if(emailTemplates && emailTemplates.success) {
                            ddlEmailTemplate.setOptions(aTemplate);
                        }
                    });

                    //}
                }
            });
            hiddenParams = new HiddenField({name: 'evn_params'});
            hiddenFn = function () {
                var parentForm = this.parent, address = {};

                //address['to'] = parentForm.items[2].getObject();
                //address['cc'] = parentForm.items[3].getObject();
                //address['bcc'] = parentForm.items[4].getObject();
                address.to = parentForm.items[2].getObject();
                address.cc = parentForm.items[3].getObject();
                address.bcc = parentForm.items[4].getObject();

                hiddenParams.setValue(JSON.stringify(address));
            };
            items = [
                ddlModules,
                ddlEmailTemplate,
                {
                    jtype: 'emailpicker',
                    label: translate('LBL_PMSE_FORM_LABEL_EMAIL_TO'),
                    name: 'address_to',
                    required: true,
                    submit: false,
                    fieldWidth: 250,
                    fieldHeight: 50,
                    change: hiddenFn,
                    suggestionItemName: 'fullName',
                    suggestionItemAddress: 'emailAddress',
                    suggestionDataURL: "pmse_Project/CrmData/emails/{$0}",
                    suggestionDataRoot: "result",
                    teams: project.getMetadata('teams') || []
                },

                {
                    jtype: 'emailpicker',
                    label: translate('LBL_PMSE_FORM_LABEL_EMAIL_CC'),
                    name: 'address_cc',
                    required: false,
                    submit: false,
                    fieldWidth: 250,
                    fieldHeight: 50,
                    change: hiddenFn,
                    suggestionItemName: 'fullName',
                    suggestionItemAddress: 'emailAddress',
                    suggestionDataURL: "pmse_Project/CrmData/emails/{$0}",
                    suggestionDataRoot: "result",
                    teams: project.getMetadata('teams') || []
                },
                {
                    jtype: 'emailpicker',
                    label: translate('LBL_PMSE_FORM_LABEL_EMAIL_BCC'),
                    name: 'address_bcc',
                    required: false,
                    submit: false,
                    fieldWidth: 250,
                    fieldHeight: 50,
                    change: hiddenFn,
                    suggestionItemName: 'fullName',
                    suggestionItemAddress: 'emailAddress',
                    suggestionDataURL: "pmse_Project/CrmData/emails/{$0}",
                    suggestionDataRoot: "result",
                    teams: project.getMetadata('teams') || []
                },
                hiddenParams
            ];
            wHeight = 380;
            wWidth = 500;
            callback = {
                loaded: function (data) {
                    var params = null, i;
                    root.canvas.emptyCurrentSelection();
                    if (data && data.evn_params) {
                        try {
                            params = JSON.parse(data.evn_params);
                        } catch (e) {}
                        if (params) {
                            hiddenParams.setValue(data.evn_params);
                            for (i = 0; i < f.items.length; i += 1) {
                                switch (f.items[i].name) {
                                case 'address_to':
                                    //f.items[i].setValue(params.to.join(', '));
                                    f.items[i].setValue(params.to);
                                    break;
                                case 'address_cc':
                                    //f.items[i].setValue(params.cc.join(', '));
                                    f.items[i].setValue(params.cc);
                                    break;
                                case 'address_bcc':
                                    //f.items[i].setValue(params.bcc.join(', '));
                                    f.items[i].setValue(params.bcc);
                                    break;
                                }
                            }
                        }
                    }

                    ddlModules.proxy.getData(null, {
                        success: function(params)
                        {
                            if (params && params.result) {
                                ddlModules.setOptions(params.result);
                                ddlModules.setValue(data.evn_module || ((params.result[0] && params.result[0].value) || null));
                            }

                            ddlEmailTemplate.proxy.uid = ddlModules.value;
                            ddlEmailTemplate.proxy.url = 'pmse_Project/CrmData/emailtemplates/' + ddlModules.value;
                            aTemplate = [{'text': translate('LBL_PMSE_FORM_OPTION_SELECT'), 'value': ''}];
                            ddlEmailTemplate.proxy.getData( null, {
                                success: function(params) {
                                    aTemplate = aTemplate.concat(params.result);
                                    ddlEmailTemplate.setOptions(aTemplate);
            //if (params && params.result) {
            //    ddlEmailTemplate.setValue(data.evn_criteria || ((params.result[0] && params.result[0].value) || null));
            //}
                                    App.alert.dismiss('upload');
                                    w.html.style.display = 'inline';
                                }
                            });

                        }
                    });


                }
            };
        }
        break;
    }

    f = new Form({
        proxy: proxy,
        closeContainerOnSubmit: true,
        items: items,
        buttons: [
            { jtype: 'submit', caption: translate('LBL_PMSE_BUTTON_SAVE') },
            { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_CANCEL'), handler: function () {
                $('.hasDatepicker').datepicker('hide');
                if (f.isDirty()) {
                    cancelInformation =  new MessagePanel({
                        title: "Confirm",
                        wtype: 'Confirm',
                        message: translate('LBL_PMSE_MESSAGE_CANCEL_CONFIRM'),
                        buttons: [
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_YES'),
                                handler: function () {
                                    cancelInformation.close();
                                    w.close();
                                }
                            },
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_NO'),
                                handler: function () {
                                    cancelInformation.close();
                                }
                            }

                        ]
                    });
                    cancelInformation.show();
                } else {

                    w.close();
                }
            }}
        ],
        callback: callback,
        language: PMSE_DESIGNER_FORM_TRANSLATIONS
    });

    w = new Window({
        width: wWidth,
        height: wHeight,
        modal: true,
        title: translate('LBL_PMSE_FORM_TITLE_LABEL_EVENT') + ': ' + this.getName()
    });
    w.addPanel(f);

    if (this.evn_type === 'BOUNDARY') {
        disabled = true;
    }

    if (this.evn_type === 'END' && this.evn_marker !== 'MESSAGE') {
        disabled = true;
    }

    action = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_SETTINGS'),
        cssStyle : 'adam-menu-icon-configure',
        handler: function () {
            root.canvas.project.save();
            w.show();
            w.html.style.display = 'none';
            App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
        },
        disabled: disabled
    });

    return action;
};

/**
 * Stringifies the AdamEvent object
 * @return {Object}
 */
AdamEvent.prototype.stringify = function () {
    var inheritedJSON = AdamShape.prototype.stringify.call(this),
        thisJSON = {
            //evn_type: this.getType(),
            evn_marker: this.getEventMarker(),
            evn_message: this.getEventMessage(),
            //evn_behavior: this.evn_behavior,
            evn_condition: this.evn_condition,
            evn_attached_to: this.evn_attached_to,
            evn_is_interrupting: this.evn_is_interrupting,
            evn_behavior: this.evn_behavior
        };
    $.extend(true, inheritedJSON, thisJSON);
    return inheritedJSON;
};

/*global AdamShape, $, CommandDefaultFlow, Action, translate, Window, PMSE_DECIMAL_SEPARATOR,
 PROJECT_MODULE, Form, MessagePanel, CommandSingleProperty, PMSE_DESIGNER_FORM_TRANSLATIONS,AdamShapeLayerCommand
*/
/**
 * @class AdamGateway
 * Class to handle gateways
 * @extend AdamShape
 *
 * @constructor
 * Create a new gateway object
 * @param options
 */
var AdamGateway;
AdamGateway = function (options) {
    AdamShape.call(this, options);
    /**
     * Gateway id
     * @type {String}
     */
    this.gat_uid = null;
    /**
     * Gateway type, accept only: 'exclusive' and 'parallel' values
     * @type {String}
     */
    this.gat_type = null;
    /**
     * Gateway Direction, accept only 'unspecified', 'converging', 'diverging', 'mixed'
     * @type {String}
     */
    this.gat_direction = null;
    /**
     * Instantiate property
     * @type {String}
     */
    this.gat_instantiate = null;
    /**
     * Event Gatewat Type property
     * @type {String}
     */
    this.gat_event_gateway_type = null;
    /**
     * Activation Count property
     * @type {Number}
     */
    this.gat_activation_count = null;
    /**
     * WaitingForStart property
     * @type {Boolean}
     */
    this.gat_waiting_for_start = null;
    /**
     * Default Flow property
     * @type {null}
     */
    this.gat_default_flow = null;

    /**
     * Array of markers added to this activity
     * @type {Array}
     */
    this.markersArray = new jCore.ArrayList();

    AdamGateway.prototype.initObject.call(this, options);
};

/**
 * Point the prototype to the AdamShape Object
 * @type {AdamShape}
 */
AdamGateway.prototype = new AdamShape();
/**
 * Defines the object type
 * @type {String}
 */
AdamGateway.prototype.type = 'AdamGateway';
/**
 * Initialize the AdamGateway object
 * @param options
 */
AdamGateway.prototype.initObject = function (options) {
    var defaults = {
        gat_direction: 'UNSPECIFIED',
        gat_instantiate: false,
        gat_event_gateway_type: 'NONE',
        gat_activation_count: 0,
        gat_waiting_for_start: true,
        gat_type: 'EXCLUSIVE',
        gat_default_flow: 0
    };
    $.extend(true, defaults, options);
    this.setGatewayUid(defaults.gat_uid)
        .setGatewayType(defaults.gat_type)
        .setDirection(defaults.gat_direction)
        .setInstantiate(defaults.gat_instantiate)
        .setEventGatewayType(defaults.gat_event_gateway_type)
        .setActivationCount(defaults.gat_activation_count)
        .setWaitingForStart(defaults.gat_waiting_for_start)
        .setDefaultFlow(defaults.gat_default_flow);
    if (defaults.gat_name) {
        this.setName(defaults.gat_name);
    }
};

/**
 * Sets the Gateway ID
 * @param id
 * @return {*}
 */

AdamGateway.prototype.setGatewayUid = function (id) {
    this.gat_uid = id;
    return this;
};
/**
 * Sets the gateway type
 * @param type
 * @return {*}
 */
AdamGateway.prototype.setGatewayType = function (type) {
    var defaultTypes = {
        exclusive: 'EXCLUSIVE',
        parallel: 'PARALLEL',
        inclusive: 'INCLUSIVE',
        eventbased: 'EVENTBASED'
    };
    if (defaultTypes[type]) {
        this.gat_type = defaultTypes[type];

    }
    return this;
};
/**
 * Sets the Gateway direction
 * @param direction
 * @return {*}
 */
AdamGateway.prototype.setDirection = function (direction) {
    var defaultDir = {
        unspecified: 'UNSPECIFIED',
        diverging: 'DIVERGING',
        converging: 'CONVERGING',
        mixed: 'MIXED'
    };
    if (defaultDir[direction]) {
        this.gat_direction = defaultDir[direction];
    }
    return this;
};
/**
 * Sets the instantiate property
 * @param value
 * @return {*}
 */
AdamGateway.prototype.setInstantiate = function (value) {
    this.gat_instantiate = value;
    return this;
};
/**
 * Sets the event_gateway_type property
 * @param value
 * @return {*}
 */
AdamGateway.prototype.setEventGatewayType = function (value) {
    this.gat_event_gateway_type = value;
    return this;
};
/**
 * Sets the activation_count property
 * @param value
 * @return {*}
 */
AdamGateway.prototype.setActivationCount = function (value) {
    this.gat_activation_count = value;
    return this;
};
/**
 * Sets the waiting_for_start property
 * @param value
 * @return {*}
 */
AdamGateway.prototype.setWaitingForStart = function (value) {
    this.gat_waiting_for_start = value;
    return this;
};
/**
 * Sets te default_flow property
 * @param value
 * @return {*}
 */
AdamGateway.prototype.setDefaultFlow = function (value) {
    if (this.html) {
        AdamShape.prototype.setDefaultFlow.call(this, value);
        this.canvas.triggerCommandAdam(this, ['gat_default_flow'], [this.gat_default_flow], [value]);
    }
    this.gat_default_flow = value;
    return this;
};
/**
 * Returns an object ready to save to DB
 * @return {Object}
 */
AdamGateway.prototype.getDBObject = function () {
    var name = this.getName();
    return {
        gat_uid: this.gat_uid,
        gat_name: name,
        gat_type: this.gat_type,
        gat_direction: this.gat_direction,
        gat_instantiate: this.gat_instantiate,
        gat_event_gateway_type: this.gat_event_gateway_type,
        gat_activation_count: this.gat_activation_count,
        gat_waiting_for_start: this.gat_waiting_for_start,
        gat_default_flow: this.gat_default_flow,
        bou_x: this.x,
        bou_y: this.y,
        bou_width: this.width,
        bou_height: this.height,
        bou_container: 'bpmnDiagram',
        element_id: this.canvas.dia_id
    };
};

AdamGateway.prototype.getDirection = function () {
    return this.gat_direction;
};

AdamGateway.prototype.getGatewayType = function () {
    return this.gat_type;
};

AdamGateway.prototype.getContextMenu = function () {
    var configurateAction,
        deleteAction,
        exclusiveAction,
        parallelAction,
        inclusiveAction,
        eventbasedAction,
        exclusiveActive = this.gat_type === 'EXCLUSIVE',
        parallelActive = this.gat_type === 'PARALLEL',
        inclusiveActive = this.gat_type === 'INCLUSIVE',
        eventbasedActive = this.gat_type === 'EVENTBASED',
        defaultflowAction,
        elements = this.getDestElements(),
        defaultflowActive = (elements.length > 1) ? false : true,
        defaultflownoneAction,
        defaultflowItems = [],
        name,
        convert,
        items = [],
        self = this,
        i,
        shape,
        port,
        connection,
        direction,
        noneDirectionAction,
        convergingDirectionAction,
        divergingDirectionAction,
        mixedDirectionAction,
        directionActive,
        unspecifiedDirectionActive = this.gat_direction === 'UNSPECIFIED',
        divergingDirectionActive = this.gat_direction === 'DIVERGING',
        convergingDirectionActive = this.gat_direction === 'CONVERGING',
        mixedDirectionActive = this.gat_direction === 'MIXED',
        handle = function (id) {
            return function () {
                var cmd = new CommandDefaultFlow(self, id);
                cmd.execute();
                self.canvas.commandStack.add(cmd);
            };
        };

    deleteAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_DELETE'),
        cssStyle: 'adam-menu-icon-delete',
        handler: function () {
            var shape;
            shape = self.canvas.customShapes.find('id', self.id);
            if (shape) {
                shape.canvas.emptyCurrentSelection();
                shape.canvas.addToSelection(shape);
                shape.canvas.removeElements();
            }
        }
    });

    configurateAction  = this.createConfigureAction();

    exclusiveAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_EXCLUSIVE_GATEWAY'),
        cssStyle : 'adam-menu-icon-gateway-exclusive',
        handler: function () {
            self.updateGatewayType('EXCLUSIVE');
        },
        disabled: exclusiveActive
    });

    parallelAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_PARELLEL_GATEWAY'),
        cssStyle : 'adam-menu-icon-gateway-parallel',
        handler: function () {
            self.updateGatewayType('PARALLEL');
        },
        disabled: parallelActive
    });

    inclusiveAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_INCLUSIVE_GATEWAY'),
        cssStyle : 'adam-menu-icon-gateway-inclusive',
        handler: function () {
            self.updateGatewayType('INCLUSIVE');
        },
        disabled: inclusiveActive
    });

    eventbasedAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_EVENT_BASED_GATEWAY'),
        cssStyle : 'adam-menu-icon-gateway-eventbase',
        handler: function () {
            self.updateGatewayType('EVENTBASED');
        },
        disabled: eventbasedActive
    });
    if (elements.length > 1) {
        defaultflownoneAction = new Action({
            text: translate('LBL_PMSE_CONTEXT_MENU_NONE'),
            cssStyle : 'adam-menu-icon-none',
            handler: handle(''),
            disabled: (self.gat_default_flow !== 0) ? false : true
        });

        defaultflowItems.push(defaultflownoneAction);

        //for (i = 0; i < elements.length; i += 1) {
        for (i = 0; i < this.getPorts().getSize(); i += 1) {
            port = this.getPorts().get(i);
            connection = port.connection;
            if (connection.srcPort.parent.getID() === this.getID()) {
                shape = connection.destPort.parent;
                switch (shape.getType()) {
                case 'AdamActivity':
                    name = (shape.getName() !== '') ? shape.getName() : translate('LBL_PMSE_CONTEXT_MENU_DEFAULT_TASK');
                    break;
                case 'AdamEvent':
                    name = (shape.getName() !== '') ? shape.getName() : translate('LBL_PMSE_CONTEXT_MENU_DEFAULT_EVENT');
                    break;
                case 'AdamGateway':
                    name = (shape.getName() !== '') ? shape.getName() : translate('LBL_PMSE_CONTEXT_MENU_DEFAULT_GATEWAY');
                    break;
                }
                defaultflowItems.push(
                    new Action({
                        text: name,
                        cssStyle: self.getCanvas().getTreeItem(shape).icon,
                        handler: handle(connection.getID()),
                        disabled: (self.gat_default_flow === connection.getID()) ? true : false
                    })
                );
            }
            //shape = elements[i];
        }
        defaultflowActive = (this.gat_type === 'PARALLEL'
            || this.gat_type === 'EVENTBASED') ? true : false;

        defaultflowAction = {
            label: translate('LBL_PMSE_CONTEXT_MENU_DEFAULT_FLOW'),
            icon: 'adam-menu-icon-default-flow',
            disabled: defaultflowActive,
            menu: {
                items: defaultflowItems
            }
        };
    }

    items.push(exclusiveAction);
    items.push(parallelAction);
    if (this.gat_direction !== 'CONVERGING') {
        items.push(inclusiveAction);
        items.push(eventbasedAction);
    }


    convert = {
        label: translate('LBL_PMSE_CONTEXT_MENU_CONVERT'),
        icon: 'adam-menu-icon-convert',
        menu: {
            items: items
        }
    };
    items = [];
    directionActive = (this.gat_direction);
    noneDirectionAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_UNSPECIFIED'),
        cssStyle : 'adam-menu-icon-none',
        handler: function () {
            self.updateDirection('UNSPECIFIED');
        },
        disabled: unspecifiedDirectionActive
    });
    convergingDirectionAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_CONVERGING'),
        cssStyle : 'adam-menu-icon-gateway-converging',
        handler: function () {
            self.updateDirection('CONVERGING');
           // console.log(self);
            self.cleanFlowConditions();

        },
        disabled: convergingDirectionActive
    });
    divergingDirectionAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_DIVERGING'),
        cssStyle : 'adam-menu-icon-gateway-diverging',
        handler: function () {
            self.updateDirection('DIVERGING');
        },
        disabled: divergingDirectionActive
    });
    mixedDirectionAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_MIXED'),
        cssStyle : 'adam-menu-icon-gateway-mixed',
        handler: function () {
            self.updateDirection('MIXED');
        },
        disabled: mixedDirectionActive
    });
    direction = {
        label: translate('LBL_PMSE_CONTEXT_MENU_DIRECTION'),
        icon: 'adam-menu-icon-gateway-direction',
        menu: {
            items: [
                //noneDirectionAction,
                convergingDirectionAction,
                divergingDirectionAction
                //mixedDirectionAction
            ]
        }

    };

    if (elements.length > 1) {
        if (configurateAction.size > 0) {
            items.push(
                configurateAction.action,
                {
                    jtype: 'separator'
                }
            );
        }
        if (this.getGatewayType() !== 'EVENTBASED' &&  this.getGatewayType() !== 'INCLUSIVE') {
            items.push(
                direction,
                {
                    jtype: 'separator'
                }
            );
        }
        items.push(
            convert,
            {
                jtype: 'separator'
            },
            defaultflowAction,
            {
                jtype: 'separator'
            },
            deleteAction
        );
    } else {
        if (configurateAction.size > 0) {
            items.push(
                configurateAction.action,
                {
                    jtype: 'separator'
                }
            );
        }
        if (this.getGatewayType() !== 'EVENTBASED' &&  this.getGatewayType() !== 'INCLUSIVE') {
            items.push(
                direction,
                {
                    jtype: 'separator'
                }
            );
        }
        items.push(
            convert,
            {
                jtype: 'separator'
            },
            deleteAction
        );
    }

    return {
        items: items
    };
};

AdamGateway.prototype.createConfigureAction = function () {
    var action,
        w,
        wHeight = 500,
        wWidth = 750,
        f,
        i,
        connection,
        criteriaItems = [],
        canvas = this.canvas,
        oldCondition,
        oldValues,
        numFlowCriteria = 0,
        criteriaName,
        criteriaLabel,
        disabled,
        cancelInformation,
        root = this,
        proxy,
        flows;
    proxy = new SugarProxy({
        url: 'pmse_Project/GatewayDefinition/' + this.id,
        //restClient: this.canvas.project.restClient,
        uid: this.id,
        callback: null
    });
    w = new Window({
        width: wWidth,
        height: wHeight,
        modal: true,
        title: translate('LBL_PMSE_FORM_TITLE_GATEWAY') + ': ' + this.getName()
    });
    for (i = 0; i < this.getPorts().getSize(); i += 1) {
        connection = this.getPorts().get(i).connection;
        if (this.gat_default_flow !== connection.getID()
                && connection.flo_element_origin === this.getID()
                && connection.flo_type !== 'DEFAULT') {
            numFlowCriteria += 1;

        }
    }

    f = new Form({
        //items: criteriaItems,
        proxy: proxy,
        buttons: [
            { jtype: 'submit', caption: translate('LBL_PMSE_BUTTON_SAVE') },
            { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_CANCEL'), handler: function () {
                if (f.isDirty()) {
                    cancelInformation =  new MessagePanel({
                        title: "Confirm",
                        wtype: 'Confirm',
                        message: translate('LBL_PMSE_MESSAGE_CANCEL_CONFIRM'),
                        buttons: [
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_YES'),
                                handler: function () {
                                    cancelInformation.close();
                                    w.close();
                                }
                            },
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_NO'),
                                handler: function () {
                                    cancelInformation.close();
                                }
                            }
                        ]
                    });
                    cancelInformation.show();
                } else {
                    w.close();
                }
            }}
        ],
        callback: {
            submit: function (data, other) {
                var array = [];

                //TODO UPDATE FLOW CONDITION
                $.each(data, function (key, val) {
                    connection = canvas.connections.find('id', key.split("-")[1]);
//                    oldCondition =  connection.getFlowCondition();

//                        oldValues = {
//                            condition: connection.getFlowCondition(),
//                            type: connection.getFlowType(),
//                            priority: 0
//                        };
                    val =  (val !== '[]') ? val : '';
                    connection.setFlowCondition(val);
//                        connection.canvas.triggerFlowConditionChangeEvent(connection, oldValues);
                    array.push({flo_uid: connection.id, flo_condition: connection.getFlowCondition()});
                });

                proxy.sendData(array);
                w.close();
            },
            loaded: function (data) {
                root.canvas.emptyCurrentSelection();

                //make criteria fields sortable
                $(f.body).sortable({
                    connectWith: ".adam-field",
                    stop: function (event, ui) {

                        root.reorderItem(f, ui.item.attr('id'));
                    },
                    start: function (event, ui) {
                        var fields, i;
                        //console.log('was changed');
                        fields = f.items;
                        for (i = 0; i < fields.length; i += 1) {
                            fields[i].closePanel();
                        }
                        $('.multiple-item-panel').hide();
                        $(f.body).css('cursor', 'move');
                    }
                });
                $(f.body).on("mouseover", '.adam-field', function (e) {
                    $(f.body).sortable("enable");
                    $(f.body).css('cursor', 'row-resize');
                    e.stopPropagation();
                });
                $(f.body).on("mouseover", '.multiple-item-container', function (e) {
                    $(f.body).sortable("disable");
                    $(f.body).css('cursor', 'default');
                    e.stopPropagation();
                });

                //console.log(data);
                flows = data.data;
                if (data && data.data) {
                for (i = 0; i < flows.length; i += 1) {
                    connection = root.canvas.getConnections().find('id', flows[i].flo_uid);
                    criteriaName = (connection.getName()
                        && connection.getName() !== '')
                        ? connection.getName() : connection.getDestPort().parent.getName();
                    criteriaLabel = translate('LBL_PMSE_FORM_LABEL_CRITERIA') + ' (' + criteriaName + ')';
//                    console.log(connection.getFlowCondition());
                    criteriaItems.push(
                        {
                            jtype: 'criteria',
                            name: 'condition-' + connection.getID(),
                            label: criteriaLabel,
                            required: false,
                            value: connection.getFlowCondition(),
                            fieldWidth: 288,
                            fieldHeight: 128,
                            decimalSeparator: SUGAR.App.config.defaultDecimalSeparator,
                            numberGroupingSeparator: SUGAR.App.config.defaultNumberGroupingSeparator,
                            operators: {
                                logic: true, 
                                group: true,
                                aritmetic: false,
                                comparison: false   
                            },
                            evaluation: {
                                module: {
                                    dataURL: 'pmse_Project/CrmData/related/' + project.process_definition.pro_module,
                                    dataRoot: 'result',
                                    fieldDataURL: 'pmse_Project/CrmData/fields/{{MODULE}}',
                                    fieldDataRoot: "result",
                                    fieldTypeField: "type"
                                },
                                form: {
                                    dataURL: "pmse_Project/CrmData/activities/" + project.uid,
                                    dataRoot: 'result'
                                },
                                business_rule: {
                                    dataURL: 'pmse_Project/CrmData/businessrules/' + project.uid,
                                    dataRoot: 'result'
                                },
                                user: {
                                    defaultUsersDataURL: "pmse_Project/CrmData/defaultUsersList",
                                    defaultUsersDataRoot: "result",
                                    userRolesDataURL: "pmse_Project/CrmData/rolesList",
                                    userRolesDataRoot: "result",
                                    usersDataURL: "pmse_Project/CrmData/users",
                                    usersDataRoot: "result"
                                }
                            },
                            constant: false
                        }
                    );
                }
                }
                f.setItems(criteriaItems);
                for (i = 0; i < f.items.length; i += 1) {
                    html = f.items[i].getHTML();
                    $(html).find("select, input, textarea").focus(f.onEnterFieldHandler(f.items[i]));
                    f.body.appendChild(html);
                }
                ///end sortable field implementation

                f.proxy = null;
                App.alert.dismiss('upload');
                w.html.style.display = 'inline';

            }
        },
        language: PMSE_DESIGNER_FORM_TRANSLATIONS
    });

    w.addPanel(f);
    disabled = (this.gat_type === 'PARALLEL'
        || this.gat_type === 'EVENTBASED' || this.gat_direction === 'CONVERGING') ? true : false;

    action = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_SETTINGS'),
        cssStyle : 'adam-menu-icon-configure',
        handler: function () {
            w.show();
            w.html.style.display = 'none';
            App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});

        },
        disabled: disabled
    });

    return {size: numFlowCriteria, action: action};
};

AdamGateway.prototype.reorderItem = function (form, itemId) {
    var i,
        item,
        array,
        oldPos,
        newPos,
        aux;
    for (i = 0;  i < form.items.length; i += 1) {
        item = form.items[i];
        if (itemId === item.id) {
            oldPos = i;
            break;
        }
    }
    array = $('.adam-panel-body > div').map(function () {
        return this.id;
    }).get();
    for (i = 0;  i < array.length; i += 1) {
        if (itemId === array[i]) {
            newPos = i;
            break;
        }
    }
    aux = form.items[newPos];
    form.items[newPos] = form.items[oldPos];
    form.items[oldPos] = aux;
};

AdamGateway.prototype.cleanFlowConditions = function () {
    var i, port, connection, oldValues;
    for (i = 0; i < this.getPorts().getSize(); i += 1) {
        port = this.getPorts().get(i);
        connection = port.connection;
        if (connection.srcPort.parent.getID() === this.getID()) {
            //console.log(connection);
            oldValues = {
                condition: connection.getFlowCondition(),
                type: connection.getFlowType()
            };
            connection.setFlowCondition('');
            connection.canvas.triggerFlowConditionChangeEvent(connection, oldValues);
        }
    }
};

AdamGateway.prototype.updateGatewayType = function (newType) {
    var layer,
        updateCommand;

    layer = this.getLayers().get(0);
    updateCommand = new AdamShapeLayerCommand(
        this,
        {
            layers: [layer],
            type: 'changetypegateway',
            changes: newType
        }
    );
    updateCommand.execute();

    this.canvas.commandStack.add(updateCommand);
    return this;
};

AdamGateway.prototype.updateDirection = function (newDirection) {
    var command = new CommandSingleProperty(this, {
        propertyName: 'gat_direction',
        before: this.gat_direction,
        after: newDirection
    });
    command.execute();
    //this.getCanvas().commandStack.add(command);
};

AdamGateway.prototype.updateDefaultFlow = function (destID) {
    this.gat_default_flow = destID;
};


/*global AdamShape, jCore, $, AdamActivityContainerBehavior, AdamActivityResizeBehavior,
 Action, translate, AdamMarker, CommandDefaultFlow,
 AdamShapeMarkerCommand, AdamShapeLayerCommand, RestProxy, SUGAR_URL, Window, PMSE_DESIGNER_FORM_TRANSLATIONS,
 Form, ItemMatrixField, HiddenField, LabelField, TextField, ComboboxField, adamUID,
 CheckboxField, CommandAdam, ItemUpdaterField, PROJECT_MODULE, FieldOption, MessagePanel, RestClient,
 NumberField, CheckboxGroup
 */

/**
 * @class AdamActivity
 * Handle BPMN Activities (Tasks)
 * @extend AdamShape
 *
 * @constructor
 * Create a new Activity Object
 * @param {Object} options
 */
var AdamActivity = function (options) {
    AdamShape.call(this, options);
    /**
     * Activity Alphanumeric unique identifier
     * @type {String}
     */
    this.act_uid = null;
    /**
     * Activity Type
     * Values accepted for SugarCRM Module: TASK
     * @type {String}
     */
    this.act_type = null;
    /**
     * Define if the task is for compensation (BPMN)
     * @type {Boolean}
     */
    this.act_is_for_compensation = null;
    /**
     * Define the quantity needed to start the activity
     * @type {Number}
     */
    this.act_start_quantity = null;
    /**
     * Define the quantity needed to complete the activity
     * @type {Number}
     */
    this.act_completion_quantity = null;
    /**
     * Define the task type.
     * For SugarCRM module only support: USERTASK
     * @type {String}
     */
    this.act_task_type = null;
    /**
     * Define the activity implementation
     * @type {String}
     */
    this.act_implementation = null;
    /**
     * Define the instatiation status
     * @type {Boolean}
     */
    this.act_instantiate = null;
    /**
     * Define the script type supported
     * @type {String}
     */
    this.act_script_type = null;
    /**
     * Define the script
     * @type {String}
     */
    this.act_script = null;
    /**
     * Defines the loop type accepted
     * @type {String}
     */
    this.act_loop_type = null;
    /**
     * Define if the test to complete the loop would be executed before o later
     * @type {Boolean}
     */
    this.act_test_before = null;
    /**
     * Defines the maximum value of loops allowed
     * @type {Number}
     */
    this.act_loop_maximum = null;
    /**
     * Defines the loop condition
     * @type {String}
     */
    this.act_loop_condition = null;
    /**
     * Defines the loop cardinality
     * @type {String}
     */
    this.act_loop_cardinality = null;
    /**
     * Defines the loop behavior
     * @type {String}
     */
    this.act_loop_behavior = null;
    /**
     * Define if the activity has an adhoc behavior
     * @type {Boolean}
     */
    this.act_is_adhoc = null;
    /**
     * Defines if the activity is collapsed
     * @type {Boolean}
     */
    this.act_is_collapsed = null;
    /**
     * Defines the condition needed to complete the activity
     * @type {String}
     */
    this.act_completion_condition = null;
    /**
     * Define the order to be executed when exists several task in parallel mode
     * @type {Number}
     */
    this.act_ordering = null;
    /**
     * Defines if into a loop all instances would be cancelled
     * @type {Boolean}
     */
    this.act_cancel_remaining_instances = null;
    /**
     * Defines the protocol used for the transaction activities
     * @type {String}
     */
    this.act_protocol = null;
    /**
     * Define the method to be used when activity consume/execute a web service
     * @type {String}
     */
    this.act_method = null;
    /**
     * Define the scope of the activity
     * @type {Boolean}
     */
    this.act_is_global = null;
    /**
     * Define the referer to another object (Process, Participant or Another Activity)
     * @type {String}
     */
    this.act_referer = null;
    /**
     * Defines the default flow when activity is related to two or more flows
     * @type {String}
     */
    this.act_default_flow = null;
    /**
     * Defines the diagram related when activity plays as subprocess
     * @type {String}
     */
    this.act_master_diagram = null;
    /**
     * Array of Boundary places created to receive boundary events
     * @type {Array}
     */
    this.boundaryPlaces = new jCore.ArrayList();
    /**
     * Array of Boundary events attached to this activity
     * @type {Array}
     */
    this.boundaryArray = new jCore.ArrayList();
    /**
     * Array of markers added to this activity
     * @type {Array}
     */
    this.markersArray = new jCore.ArrayList();

    AdamActivity.prototype.initObject.call(this, options);
};

/**
 * Point the prototype to the AdamShape Object
 * @type {AdamShape}
 */
AdamActivity.prototype = new AdamShape();

/**
 * Define the Object Type
 * @type {String}
 */
AdamActivity.prototype.type = 'AdamActivity';
/**
 * Points to container behavior object
 * @type {Object}
 */
AdamActivity.prototype.activityContainerBehavior = null;
/**
 * Points to the resize behavior object
 * @type {Object}
 */
AdamActivity.prototype.activityResizeBehavior = null;

/**
 * Initialize object with default values
 * @param options
 */
AdamActivity.prototype.initObject = function (options) {
    var defaults = {
        act_type: 'TASK',
        act_loop_type: 'NONE',
        act_is_for_compensation: false,
        act_task_type: 'EMPTY',
        act_is_collapsed: false,
        act_is_global: false,
        act_loop_cardinality: 0,
        act_loop_maximum: 0,
        act_start_quantity: 1,
        act_is_adhoc: false,
        act_cancel_remaining_instances: true,
        act_instantiate: false,
        act_completion_quantity: 0,
        act_implementation: '',
        act_script: '',
        act_script_type: '',
        act_default_flow: 0,
        minHeight: 50,
        minWidth: 100,
        maxHeight: 500,
        maxWidth: 600
    };
    $.extend(true, defaults, options);
    this.setActivityUid(defaults.act_uid)
        .setActivityType(defaults.act_type)
        .setLoopType(defaults.act_loop_type)
        .setIsForCompensation(defaults.act_is_for_compensation)
        .setTaskType(defaults.act_task_type)
        .setIsCollapsed(defaults.act_is_collapsed)
        .setIsGlobal(defaults.act_is_global)
        .setLoopCardinality(defaults.act_loop_cardinality)
        .setLoopMaximun(defaults.act_loop_maximum)
        .setStartQuantity(defaults.act_start_quantity)
        .setIsAdhoc(defaults.act_is_adhoc)
        .setCancelRemainingInstances(defaults.act_cancel_remaining_instances)
        .setInstantiate(defaults.act_instantiate)
        .setImplementation(defaults.act_implementation)
        .setCompletionQuantity(defaults.act_completion_quantity)
        .setScript(defaults.act_script)
        .setScriptType(defaults.act_script_type)
        .setDefaultFlow(defaults.act_default_flow)
        .setMinHeight(defaults.minHeight)
        .setMinWidth(defaults.minWidth)
        .setMaxHeight(defaults.maxHeight)
        .setMaxWidth(defaults.maxWidth);
    if (defaults.act_name) {
        this.setName(defaults.act_name);
    }
    if (defaults.markers) {
        this.addMarkers(defaults.markers, this);
    }
};

/**
 * Returns the activity type property
 * @return {String}
 */
AdamActivity.prototype.getActivityType = function () {
    return this.act_type;
};

/**
 * Returns the is for compensation property
 * @return {Boolean}
 */
AdamActivity.prototype.getIsForCompensation = function () {
    return this.act_is_for_compensation;
};

/**
 * Returns if the activity cancel remaining instances when is cancelled
 * @return {Boolean}
 */
AdamActivity.prototype.getCancelRemainingInstances = function () {
    return this.act_cancel_remaining_instances;
};

/**
 * Returns the quantity needed to complete an activity
 * @return {Number}
 */
AdamActivity.prototype.getCompletionQuantity = function () {
    return this.act_completion_quantity;
};

/**
 * Set is the activity is global (scope)
 * @param {Boolean} value
 * @return {*}
 */
AdamActivity.prototype.getIsGlobal = function () {
    return this.act_is_global;
};

/**
 * Returns the start quantity needed to start an activity
 * @return  {Number}
 */
AdamActivity.prototype.getStartQuantity = function () {
    return this.act_start_quantity;
};

/**
 * Returns if the instance is active
 * @return {Boolean}
 */
AdamActivity.prototype.getInstantiate = function () {
    return this.act_instantiate;
};

/**
 * Returns the implementation property
 * @return {String}
 */
AdamActivity.prototype.getImplementation = function () {
    return this.act_implementation;
};

/**
 * Return the Script property
 * @param {Number} value
 * @return {*}
 */
AdamActivity.prototype.getScript = function () {
    return this.act_script;
};

/**
 * Return the Script Type property
 * @param {Number} value
 * @return {*}
 */
AdamActivity.prototype.getScriptType = function () {
    return this.act_script_type;
};

/**
 * Return the minimun height of an activity
 * @return {*}
 */
AdamActivity.prototype.getMinHeight = function () {
    return this.minHeight;
};

/**
 * Return the minimun width of an activity
 * @return {*}
 */
AdamActivity.prototype.getMinWidth = function () {
    return this.minWidth;
};
/**
 * Return the maximun height of an activity
 * @return {*}
 */
AdamActivity.prototype.getMaxHeight = function () {
    return this.maxHeight;
};

/**
 * Return the maximun width of an activity
 * @return {*}
 */
AdamActivity.prototype.getMaxWidth = function () {
    return this.maxWidth;
};
/**
 * Sets the act_uid property
 * @param {String} value
 * @return {*}
 */
AdamActivity.prototype.setActivityUid = function (value) {
    this.act_uid = value;
    return this;
};

/**
 * Sets the activity type property
 * @param {String} type
 * @return {*}
 */
AdamActivity.prototype.setActivityType = function (type) {
    this.act_type = type;
    return this;
};

/**
 * Sets the implementation property
 * @param {String} type
 * @return {*}
 */
AdamActivity.prototype.setImplementation = function (type) {
    this.act_implementation = type;
    return this;
};

/**
 * Set the loop type property
 * @param {String} type
 * @return {*}
 */
AdamActivity.prototype.setLoopType = function (type) {
    this.act_loop_type = type;
    return this;
};

/**
 * Sets the collapsed property
 * @param {Boolean} value
 * @return {*}
 */
AdamActivity.prototype.setIsCollapsed = function (value) {
    if (_.isBoolean(value)) {
        this.act_is_collapsed = value;
    }
    return this;
};

/**
 * Sets the is for compensation property
 * @param {Boolean} value
 * @return {*}
 */
AdamActivity.prototype.setIsForCompensation = function (value) {
    if (_.isBoolean(value)) {
        this.act_is_for_compensation = value;
    }
    return this;
};

/**
 * Sets the activity task type
 * @param {String} type
 * @return {*}
 */
AdamActivity.prototype.setTaskType = function (type) {
    this.act_task_type = type;
    return this;
};

/**
 * Set is the activity is global (scope)
 * @param {Boolean} value
 * @return {*}
 */
AdamActivity.prototype.setIsGlobal = function (value) {
    if (_.isBoolean(value)) {
        this.act_is_global = value;
    }
    return this;
};

/**
 * Set the loop cardinality of the activity
 * @param {String} value
 * @return {*}
 */
AdamActivity.prototype.setLoopCardinality = function (value) {
    this.act_loop_cardinality = value;
    return this;
};

/**
 * Sets the loop maximun value
 * @param {Number} value
 * @return {*}
 */
AdamActivity.prototype.setLoopMaximun = function (value) {
    this.act_loop_maximum = value;
    return this;
};

/**
 * Sets the start quantity needed to start an activity
 * @param  {Number} value
 * @return {*}
 */
AdamActivity.prototype.setStartQuantity = function (value) {
    this.act_start_quantity = value;
    return this;
};

/**
 * Sets if the activity has an adhoc behavior
 * @param {Boolean} value
 * @return {*}
 */
AdamActivity.prototype.setIsAdhoc = function (value) {
    if (_.isBoolean(value)) {
        this.act_is_adhoc = value;
    }
    return this;
};

/**
 * Sets if the activity cancel remaining instances when is cancelled
 * @param {Boolean} value
 * @return {*}
 */
AdamActivity.prototype.setCancelRemainingInstances = function (value) {
    if (_.isBoolean(value)) {
        this.act_cancel_remaining_instances = value;
    }
    return this;
};

/**
 * Sets if the instance is active
 * @param {Boolean} value
 * @return {*}
 */
AdamActivity.prototype.setInstantiate = function (value) {
    if (_.isBoolean(value)) {
        this.act_instantiate = value;
    }
    return this;
};

/**
 * Sets the quantity needed to complete an activity
 * @param {Number} value
 * @return {*}
 */
AdamActivity.prototype.setCompletionQuantity = function (value) {
    this.act_completion_quantity = value;
    return this;
};

/**
 * Sets the Script property
 * @param {Number} value
 * @return {*}
 */
AdamActivity.prototype.setScript = function (value) {
    this.act_script = value;
    return this;
};

/**
 * Sets the Script Type property
 * @param {Number} value
 * @return {*}
 */
AdamActivity.prototype.setScriptType = function (value) {
    this.act_script_type = value;

    return this;
};

/**
 * Sets te default_flow property
 * @param value
 * @return {*}
 */
AdamActivity.prototype.setDefaultFlow = function (value) {
    if (this.html) {
        AdamShape.prototype.setDefaultFlow.call(this, value);
        this.canvas.triggerCommandAdam(this, ['act_default_flow'], [this.act_default_flow], [value]);
    }
    this.act_default_flow = value;
    return this;
};
/**
 * Sets the minimun height
 * @param {Number} value
 * @return {*}
 */
AdamActivity.prototype.setMinHeight = function (value) {
    this.minHeight = value;
    return this;
};

/**
 * Sets the minimun with
 * @param {Number} value
 * @return {*}
 */
AdamActivity.prototype.setMinWidth = function (value) {
    this.minWidth = value;

    return this;
};
/**
 * Sets the maximun height
 * @param {Number} value
 * @return {*}
 */
AdamActivity.prototype.setMaxHeight = function (value) {
    this.maxHeight = value;
    return this;
};

/**
 * Sets the maximun with
 * @param {Number} value
 * @return {*}
 */
AdamActivity.prototype.setMaxWidth = function (value) {
    this.maxWidth = value;

    return this;
};
/**
 * Returns the clean object to be sent to the backend
 * @return {Object}
 */
AdamActivity.prototype.getDBObject = function () {
    var name = this.getName();
    return {
        act_uid: this.act_uid,
        act_name: name,
        act_type: this.act_type,
        act_task_type: this.act_task_type,
        act_is_for_compensation: this.act_is_for_compensation,
        act_start_quantity: this.act_start_quantity,
        act_completion_quantity: this.act_completion_quantity,
        act_implementation: this.act_implementation,
        act_instantiate: this.act_instantiate,
        act_script_type: this.act_script_type,
        act_script: this.act_script,
        act_loop_type: this.act_loop_type,
        act_test_before: this.act_test_before,
        act_loop_maximum: this.act_loop_maximum,
        act_loop_condition: this.act_loop_condition,
        act_loop_cardinality: this.act_loop_cardinality,
        act_loop_behavior: this.act_loop_behavior,
        act_is_adhoc: this.act_is_adhoc,
        act_is_collapsed: this.act_is_collapsed,
        act_completion_condition: this.act_completion_condition,
        act_ordering: this.act_ordering,
        act_cancel_remaining_instances: this.act_cancel_remaining_instances,
        act_protocol: this.act_protocol,
        act_method: this.act_method,
        act_is_global: this.act_is_global,
        act_referer: this.act_referer,
        act_default_flow: this.act_default_flow,
        act_master_diagram: this.act_master_diagram,
        bou_x: this.x,
        bou_y: this.y,
        bou_width: this.width,
        bou_height: this.height,
        bou_container: 'bpmnDiagram',
        element_id: this.canvas.dia_id
    };
};

AdamActivity.prototype.getMarkers = function () {
    return this.markersArray;
};

/**
 * Factory function to handle several container behavior elements
 * @param {String} type
 * @return {*}
 */
AdamActivity.prototype.containerBehaviorFactory = function (type) {
    var out;
    if (type === 'activity') {
        if (!this.activityContainerBehavior) {
            this.activityContainerBehavior = new AdamActivityContainerBehavior();
        }
        out = this.activityContainerBehavior;
    } else {
        out = AdamShape.prototype.containerBehaviorFactory.call(this, type);
    }
    return out;
};

/**
 * Factory function to handle several resize behavior elements
 * @param {String} type
 * @return {*}
 */
AdamActivity.prototype.resizeBehaviorFactory = function (type) {
    var out;
    if (type === 'activityResize') {
        if (!this.activityResizeBehavior) {
            this.activityResizeBehavior = new AdamActivityResizeBehavior();
        }
        out = this.activityResizeBehavior;
    } else {
        out = AdamShape.prototype.resizeBehaviorFactory.call(this, type);
    }
    return out;
};

/**
 * Add adam custom css classes to the HTML
 * @return {*}
 */
AdamActivity.prototype.createHTML = function () {
    jCore.CustomShape.prototype.createHTML.call(this);
    this.style.addClasses(['adam_activity', "adam_droppable"]);
    return this.html;
};

/**
 * Create/Initialize the boundary places array
 * @return {*}
 */
AdamActivity.prototype.makeBoundaryPlaces = function () {
    var bouX,
        bouY,
        factor = 3,
        space,
        number = 0,
        shape = this.boundaryArray.getFirst(),
        numBottom = 0,
        numLeft = 0,
        numTop = 0,
        numRight = 0;

    //BOTTON
    bouY = shape.parent.getHeight() - shape.getHeight() / 2; // Y is Constant
    bouX = shape.parent.getWidth() - (numBottom + 1) * (shape.getWidth() + factor);
    while (bouX + shape.getWidth() / 2 > 0) {
        space = {};
        space.x = bouX;
        space.y = bouY;
        space.available = true;
        space.number = number;
        space.location = 'BOTTOM';
        shape.parent.boundaryPlaces.insert(space);
        number += 1;
        numBottom += 1;
        bouX = shape.parent.getWidth() - (numBottom + 1) * (shape.getWidth() + factor);
    }

    //LEFT
    bouY = shape.parent.getHeight() - (numLeft + 1) * (shape.getHeight() + factor);
    bouX = -shape.getHeight() / 2;   // X is Constant
    while (bouY + shape.getHeight() / 2 > 0) {
        space = {};
        space.x = bouX;
        space.y = bouY;
        space.available = true;
        space.number = number;
        space.location = 'LEFT';
        shape.parent.boundaryPlaces.insert(space);
        number += 1;
        numLeft += 1;
        bouY = shape.parent.getHeight() - (numLeft + 1) * (shape.getHeight() + factor);
    }

    //TOP
    bouY = -shape.getWidth() / 2; // X is Constant
    bouX = numTop * (shape.getWidth() + factor);
    while (bouX + shape.getWidth() / 2 < shape.parent.getWidth()) {
        space = {};
        space.x = bouX;
        space.y = bouY;
        space.available = true;
        space.number = number;
        space.location = 'TOP';
        shape.parent.boundaryPlaces.insert(space);
        number += 1;
        numTop += 1;
        bouX = numTop * (shape.getWidth() + factor);
    }

    //RIGHT
    bouY = numRight * (shape.getHeight() + factor);
    bouX = shape.parent.getWidth() - shape.getWidth() / 2; // Y is Constant
    while (bouY + shape.getHeight() / 2 < shape.parent.getHeight()) {
        space = {};
        space.x = bouX;
        space.y = bouY;
        space.available = true;
        space.number = number;
        space.location = 'RIGHT';
        shape.parent.boundaryPlaces.insert(space);
        number += 1;
        numRight += 1;
        bouY = numRight * (shape.getHeight() + factor);
    }
    return this;
};

/**
 * Sets the boundary element to a selected boundary place
 * @param {AdamEvent} shape
 * @param {Number} number
 * @return {*}
 */
AdamActivity.prototype.setBoundary = function (shape, number) {
    var bouPlace = this.boundaryPlaces.get(number);
    bouPlace.available = false;
    shape.setPosition(bouPlace.x, bouPlace.y);
    return this;
};

/**
 * Returns the current place available to attach boundary events.
 * Retuns false if there's not place available
 * @return {Number/Boolean}
 */
AdamActivity.prototype.getAvailableBoundaryPlace = function () {
    var place = 0,
        bouPlace,
        sw = true,
        i;
    for (i = 0; i < this.boundaryPlaces.getSize(); i += 1) {
        bouPlace = this.boundaryPlaces.get(i);
        if (bouPlace.available && sw) {
            place = bouPlace.number;
            sw = false;
        }
    }
    if (sw) {
        place = false;
    }
    return place;
};

/**
 * Update Boundary Places Array
 * @return {*}
 */
AdamActivity.prototype.updateBoundaryPlaces = function () {
    var i,
        aux,
        k = 0;
    aux =  new jCore.ArrayList();
    for (i = 0; i < this.boundaryPlaces.getSize(); i += 1) {
        aux.insert(this.boundaryPlaces.get(i));
    }

    this.boundaryPlaces.clear();
    this.makeBoundaryPlaces();

    for (i = 0; i < this.boundaryPlaces.getSize(); i += 1) {
        if (k < aux.getSize()) {
            this.boundaryPlaces.get(i).available = aux.get(k).available;
            k += 1;
        }
    }
    return this;
};

/**
 * Returns the number of boundary events attached to this activity
 * @return {Number}
 */
AdamActivity.prototype.getNumberOfBoundaries = function () {
    var child,
        i,
        bouNum = 0;

    for (i = 0; i < this.getChildren().getSize(); i += 1) {
        child = this.getChildren().get(i);
        if (child.getType() === 'AdamEvent' && child.evn_type === 'BOUNDARY') {
            bouNum = bouNum + 1;
        }
    }
    return bouNum;
};

/**
 * Update boundary positions when exists a change into the boundary array
 * @param {Boolean} createIntersections
 */
AdamActivity.prototype.updateBoundaryPositions = function (createIntersections) {
    var child,
        port,
        i,
        j;

    if (this.getNumberOfBoundaries() > 0) {

        this.updateBoundaryPlaces();
        for (i = 0; i < this.getChildren().getSize(); i += 1) {
            child = this.getChildren().get(i);
            if (child.getType() === 'AdamEvent'
                && child.evn_type === 'BOUNDARY') {
                child.setPosition(this.boundaryPlaces.get(child.numberRelativeToActivity).x,
                    this.boundaryPlaces.get(child.numberRelativeToActivity).y
                );
                for (j = 0; j < child.ports.getSize(); j += 1) {
                    port = child.ports.get(j);
                    port.setPosition(port.x, port.y);
                    port.connection.disconnect().connect();
                    if (createIntersections) {
                        port.connection.setSegmentMoveHandlers();
                        port.connection.checkAndCreateIntersectionsWithAll();
                    }
                }
            }
        }
    }
};

/**
 * Adds markers to the arrayMarker property
 * @param {Array} markers
 * @param {AdamShape} parent
 * @return {*}
 */
AdamActivity.prototype.addMarkers = function (markers, parent) {
    var newMarker, i, factoryMarker;
    if (_.isArray(markers)) {
        for (i = 0; i < markers.length; i += 1) {
            factoryMarker = markers[i];
            factoryMarker.parent = parent;
            factoryMarker.canvas = parent.canvas;
            newMarker = new AdamMarker(factoryMarker);
            this.markersArray.insert(newMarker);
        }
    }
    return this;
};

/**
 * Paint the shape
 */
AdamActivity.prototype.paint = function () {
    var m, marker;
    AdamShape.prototype.paint.call(this);
    for (m = 0; m < this.markersArray.getSize(); m += 1) {
        marker = this.markersArray.get(m);
        marker.paint();
    }
};


AdamActivity.prototype.getActivityType = function () {
    return this.act_type;
};

AdamActivity.prototype.getContextMenu = function () {
    var self = this,
        deleteAction,
        usertaskAction,
        scriptAction,
        configureAction,
        assignUsersAction,
        elements = this.getDestElements(),
        defaultflowActive = (elements.length > 1) ? false : true,
        defaultflownoneAction,
        defaultflowItems = [],
        name,
        items,
        i,
        shape,
        handle,
        port,
        connection,
        actionItems = [],
        noneAction,
        assignUserAction,
        assignTeamAction,
        changeFieldAction,
        addRelatedRecordAction,
        businessRuleAction,
        defaultflowAction;
    deleteAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_DELETE'),
        cssStyle: 'adam-menu-icon-delete',
        handler: function () {
            var shape;
            shape = self.canvas.customShapes.find('id', self.id);
            if (shape) {
                shape.canvas.emptyCurrentSelection();
                shape.canvas.addToSelection(shape);
                shape.canvas.removeElements();
            }
        }
    });

    noneAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_UNASSIGNED'),
        cssStyle: 'adam-menu-script-none',
        handler: function () {
            self.updateScriptType('NONE');
        },
        disabled: (this.act_script_type === 'NONE')
    });

    assignUserAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_ASSIGN_USER'),
        cssStyle: 'adam-menu-script-assign_user',
        handler: function () {
            self.updateScriptType('ASSIGN_USER');
        },
        disabled: (this.act_script_type === 'ASSIGN_USER')
    });

    assignTeamAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_ASSIGN_TEAM'),
        cssStyle: 'adam-menu-script-assign_team',
        handler: function () {
            self.updateScriptType('ASSIGN_TEAM');
        },
        disabled: (this.act_script_type === 'ASSIGN_TEAM')
    });

    changeFieldAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_CHANGE_FIELD'),
        cssStyle: 'adam-menu-script-change_field',
        handler: function () {
            self.updateScriptType('CHANGE_FIELD');
        },
        disabled: (this.act_script_type === 'CHANGE_FIELD')
    });

    addRelatedRecordAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_ADD_RELATED_RECORD'),
        cssStyle: 'adam-menu-script-add_related_record',
        handler: function () {
            self.updateScriptType('ADD_RELATED_RECORD');
        },
        disabled: (this.act_script_type === 'ADD_RELATED_RECORD')
    });

    businessRuleAction = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_BUSINESS_RULE'),
        cssStyle: 'adam-menu-script-business_rule',
        handler: function () {
            self.updateScriptType('BUSINESS_RULE');
        },
        disabled: (this.act_script_type === 'BUSINESS_RULE')
    });

    if (this.act_task_type === 'USERTASK') {
        configureAction = this.createConfigurateAction();
    } else {
        configureAction = this.actionFactory(this.act_script_type);
    }
    assignUsersAction = this.createAssignUsersAction();

    if (elements.length > 1) {
        handle  = function (id) {
            return function () {
                var cmd = new CommandDefaultFlow(self, id);
                cmd.execute();
                self.canvas.commandStack.add(cmd);
            };
        };
        defaultflownoneAction = new Action({
            text: translate('LBL_PMSE_CONTEXT_MENU_NONE'),
            cssStyle : 'adam-menu-icon-none',
            handler: handle(""),
            disabled: (self.act_default_flow !== 0) ? false : true
        });

        defaultflowItems.push(defaultflownoneAction);

        for (i = 0; i < this.getPorts().getSize(); i += 1) {
            port = this.getPorts().get(i);
            connection = port.connection;
            if (connection.srcPort.parent.getID() === this.getID()) {
                shape = connection.destPort.parent;

                switch (shape.getType()) {
                    case 'AdamActivity':
                        name = (shape.getName() !== '') ? shape.getName() : translate('LBL_PMSE_CONTEXT_MENU_DEFAULT_TASK');
                        break;
                    case 'AdamEvent':
                        name = (shape.getName() !== '') ? shape.getName() : translate('LBL_PMSE_CONTEXT_MENU_DEFAULT_EVENT');
                        break;
                    case 'AdamGateway':
                        name = (shape.getName() !== '') ? shape.getName() : translate('LBL_PMSE_CONTEXT_MENU_DEFAULT_GATEWAY');
                        break;
                }
                defaultflowItems.push(
                    new Action({
                        text: name,
                        cssStyle : self.getCanvas().getTreeItem(shape).icon,
                        handler: handle(connection.getID()),
                        disabled: (self.act_default_flow === connection.getID()) ? true : false
                    })
                );

            }
        }

        defaultflowAction = {
            label: translate('LBL_PMSE_CONTEXT_MENU_DEFAULT_FLOW'),
            icon: 'adam-menu-icon-default-flow',
            disabled: defaultflowActive,
            menu: {
                items: defaultflowItems
            }
        };
    }

    items = [configureAction];
    if (this.act_task_type === 'USERTASK') {
        items.push({jtype: 'separator'}, assignUsersAction);
    }
    items.push({jtype: 'separator'});
    if (this.act_task_type === 'SCRIPTTASK') {
        actionItems.push(noneAction);
        actionItems.push(businessRuleAction);
        actionItems.push(assignUserAction, assignTeamAction, changeFieldAction, addRelatedRecordAction);
        items.push(
            {
                label: translate('LBL_PMSE_CONTEXT_MENU_ACTION_TYPE'),
                icon : 'adam-menu-icon-convert',
                menu: {
                    items: actionItems
                }
            },
            {
                jtype: 'separator'
            }
        );
    }

    if (elements.length > 1  && this.act_task_type === 'USERTASK') {
        items.push(
            defaultflowAction,
            {
                jtype: 'separator'
            },
            deleteAction
        );
    } else {
        items.push(
            deleteAction
        );
    }

    return {
        items: items
    };
};

AdamActivity.prototype.updateDefaultFlow = function (destID) {
    this.act_default_flow = destID;
    return this;
};

AdamActivity.prototype.updateTaskType = function (newType) {
    var updateCommand, marker;

    marker = this.getMarkers().get(0);

    updateCommand = new AdamShapeMarkerCommand(
        this,
        {
            markers: [marker],
            type: 'changeactivitymarker',
            changes: {
                taskType: newType
            }
        }
    );

    updateCommand.execute();

    this.canvas.commandStack.add(updateCommand);
    return this;
};

AdamActivity.prototype.updateScriptType = function (newType) {

    var layer,
        updateCommand;

    layer = this.getLayers().get(1);
    updateCommand = new AdamShapeLayerCommand(
        this,
        {
            layers: [layer],
            type: 'changescripttypeactivity',
            changes: newType
        }
    );
    updateCommand.execute();

    this.canvas.commandStack.add(updateCommand);
    return this;
};

/**
 *  Extend applyZoom of CustomShape for apply Zoom into Markers
 *  @return {*}
 */
AdamActivity.prototype.applyZoom = function () {
    var i, marker;
    AdamShape.prototype.applyZoom.call(this);
    for (i = 0; i < this.markersArray.getSize(); i += 1) {
        marker = this.markersArray.get(i);
        marker.applyZoom();
    }
    return this;
};

AdamActivity.prototype.createConfigurateAction = function () {
    var action, disabled = false, w, f, f2, root = this, proxy, wWidth = 510, wHeight = 150, items,
        callback, self = this, actionName = translate('LBL_PMSE_CONTEXT_MENU_FORMS'), formsField, actionCSS, responseButtons,
        assignTypeField, assignTeamField, labelAssigment, radioNone, radioReassigment, radioAdhoc,
        combo_teams, combo_teams_1, combo_type, reassignCheck, adhocCheck, itemMatrix, requiredFields, requiredForm, relatedForm,
        updateExpectedTime,
        expectedTimeField,
        expTimeDuration,
        expTimeCombo,
        itemsF3,
        f3,
        reassignmentFn,
        forms,
        teams,
        cancelInformation;
    cancelInformation =  new MessagePanel({
        title: "Confirm",
        wtype: 'Confirm',
        message: translate('LBL_PMSE_MESSAGE_CANCEL_CONFIRM')
    });
    w = new Window({
        width: wWidth,
        height: this.act_task_type === 'USERTASK' ? 340 : wHeight,
        modal: true,
        title: translate('LBL_PMSE_FORM_TITLE_ACTIVITY') + ': ' + this.getName()
    });

    if (this.act_task_type === 'USERTASK') {
        actionCSS = 'adam-menu-icon-form';
        proxy = new SugarProxy({
            url: 'pmse_Project/ActivityDefinition/' + this.id,
            uid: this.id,
            callback: null,
            data2: {'hola':'hola'}
        });

        itemMatrix = new ItemMatrixField({
            jtype: 'itemmatrix',
            label: translate('LBL_PMSE_FORM_LABEL_READ_ONLY_FIELDS'),
            name: 'act_readonly_fields',
            submit: true,
            fieldWidth: 350,
            fieldHeight: 200,
            visualStyle : 'table',
            nColumns: 2
        });

        f2 = new Form({
            items: [ itemMatrix ],
            closeContainerOnSubmit: true,
            labelWidth: '16%',
            buttons: [
                { jtype: 'submit', caption: translate('LBL_PMSE_BUTTON_SAVE')},
                { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_CANCEL'), handler: function () {
                    if (f2.isDirty()) {
                        cancelInformation.setButtons([
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_YES'),
                                handler: function () {
                                    cancelInformation.hide();
                                    w.close();
                                }
                            },
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_NO'),
                                handler: function () {
                                    cancelInformation.hide();
                                }
                            }
                        ]);
                        cancelInformation.show();
                    } else {
                        w.close();
                    }
                }}
            ],
            language: PMSE_DESIGNER_FORM_TRANSLATIONS
        });

        requiredFields = new ItemMatrixField({
            jtype: 'itemmatrix',
            label: translate('LBL_PMSE_FORM_LABEL_REQUIRED_FIELDS'),
            name: 'act_required_fields',
            submit: true,
            fieldWidth: 350,
            fieldHeight: 200,
            visualStyle : 'table',
            nColumns: 2
        });
        requiredForm = new Form({
            items: [ requiredFields ],
            closeContainerOnSubmit: true,
            labelWidth: '16%',
            buttons: [
                { jtype: 'submit', caption: translate('LBL_PMSE_BUTTON_SAVE')},
                { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_CANCEL'), handler: function () {
                    if (f2.isDirty()) {
                        cancelInformation.setButtons([
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_YES'),
                                handler: function () {
                                    cancelInformation.hide();
                                    w.close();
                                }
                            },
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_NO'),
                                handler: function () {
                                    cancelInformation.hide();
                                }
                            }
                        ]);
                        cancelInformation.show();
                    } else {
                        w.close();
                    }
                }}
            ]
        });

        relatedForm = new Form({
            closeContainerOnSubmit: true,
            labelWidth: '100%',
            buttons: [
                { jtype: 'submit', caption: translate('LBL_PMSE_BUTTON_SAVE')},
                { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_CANCEL'), handler: function () {
                    if (f2.isDirty()) {
                        cancelInformation.setButtons([
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_YES'),
                                handler: function () {
                                    cancelInformation.hide();
                                    w.close();
                                }
                            },
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_NO'),
                                handler: function () {
                                    cancelInformation.hide();
                                }
                            }
                        ]);
                        cancelInformation.show();
                    } else {
                        w.close();
                    }
                }}
            ]
        });

        expectedTimeField = new HiddenField({
            name: 'act_expected_time'
        });

        updateExpectedTime = function () {
            var out = {
                time: '',
                unit: ''
            };
            out.time = expTimeDuration.value;
            out.unit = expTimeCombo.value;
            expectedTimeField.setValue(out);
        };

        expTimeDuration = new NumberField(
            {
                name: 'evn_criteria',
                label: translate('LBL_PMSE_FORM_LABEL_DURATION'),
                helpTooltip: {
                    message: translate('LBL_PMSE_FORM_TOOLTIP_DURATION')
                },
                fieldWidth: '50px',
                submit: false,
                change: updateExpectedTime
            }
        );

        expTimeCombo = new ComboboxField({
            name: 'evn_params',
            label: translate('LBL_PMSE_FORM_LABEL_UNIT'),
            options: [
                { text: translate('LBL_PMSE_FORM_OPTION_DAYS'), value: 'day'},
                { text: translate('LBL_PMSE_FORM_OPTION_HOURS'), value: 'hour'},
                { text: translate('LBL_PMSE_FORM_OPTION_MINUTES'), value: 'minute'}
            ],
            initialValue: 'hour',
            submit: false,
            change: updateExpectedTime
        });

        itemsF3 = [
            expectedTimeField,
            expTimeDuration,
            expTimeCombo
        ];

        f3 = new Form({
            items: itemsF3,
            closeContainerOnSubmit: true,
            buttons: [
                { jtype: 'submit', caption: translate('LBL_PMSE_BUTTON_SAVE')},
                { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_CANCEL'), handler: function () {
                    if (f3.isDirty()) {
                        cancelInformation.setButtons([
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_YES'),
                                handler: function () {
                                    cancelInformation.hide();
                                    w.close();
                                }
                            },
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_NO'),
                                handler: function () {
                                    cancelInformation.hide();
                                }
                            }
                        ]);
                        cancelInformation.show();
                    } else {
                        w.close();
                    }
                }}
            ],
            language: PMSE_DESIGNER_FORM_TRANSLATIONS
        });

        reassignmentFn = function () {
            switch (this.name) {
                case 'combo_teams':
                    assignTeamField.setValue(combo_teams.value);
                    break;
                case 'combo_teams_1':
                    assignTeamField.setValue(combo_teams_1.value);
                    break;
            }
        };

        formsField = new ComboboxField({
            name: 'act_type',
            label: translate('LBL_PMSE_FORM_LABEL_FORM_TYPE'),
            required: false,
            proxy: new SugarProxy({
                url: 'pmse_Project/CrmData/dynaforms/' + adamUID,
                uid: adamUID,
                callback: null
            })
        });

        responseButtons = new ComboboxField({
            name: 'act_response_buttons',
            label: translate('LBL_PMSE_FORM_LABEL_RESPONSE_BUTTONS'),
            required : false
        });

        labelAssigment  = new LabelField({
            name: 'lblAssigment',
            label: translate('LBL_PMSE_FORM_LABEL_OTHER_DERIVATION_OPTIONS'),
            options: {
                marginLeft : 35
            }
        });

        reassignCheck = new CheckboxField({
            name: 'act_reassign',
            label: translate('LBL_PMSE_FORM_LABEL_RECORD_OWNERSHIP'),
            required: false,
            value: false,
            options: {
                labelAlign: 'right',
                marginLeft: 80
            },
            change : function () {
                if ($(reassignCheck.html).children('input').is(':checked')) {
                    combo_teams.setReadOnly(false);
                } else {
                    combo_teams.setReadOnly(true);
                }
            }
        });

        combo_teams = new ComboboxField({
            name: 'act_reassign_team',
            label: translate('LBL_PMSE_FORM_LABEL_TEAM'),
            required: false,
            readOnly: true,
            change: reassignmentFn,
            proxy: new SugarProxy({
                url: 'pmse_Project/CrmData/teams/reassign',
                uid: 'reassign',
                callback: null
            })
        });

        adhocCheck = new CheckboxField({
            name: 'act_adhoc',
            label: translate('LBL_PMSE_FORM_LABEL_REASSIGN'),
            required: false,
            value: false,
            options: {
                labelAlign: 'right',
                marginLeft: 80
            },
            change : function () {
                if ($(adhocCheck.html).children('input').is(':checked')) {
                    combo_teams_1.setReadOnly(false);
                } else {
                    combo_teams_1.setReadOnly(true);
                }
            }
        });

        combo_teams_1 = new ComboboxField({
            name: 'act_adhoc_team',
            label: translate('LBL_PMSE_FORM_LABEL_TEAM'),
            required: false,
            readOnly: true,
            change: reassignmentFn
        });

        combo_type = new ComboboxField({
            name: 'act_adhoc_behavior',
            label: translate('LBL_PMSE_FORM_LABEL_TYPE'),
            required: false,
            readOnly: true
        });

        assignTeamField = new HiddenField({
            name: 'act_adhoc_reassign_team'
        });

        assignTypeField = new HiddenField({
            name: 'act_reassignment_type'
        });

        actTypeField = new HiddenField({
            name: 'act_type'
        });

        items = [/*formsField,*/ responseButtons,
            labelAssigment,
            reassignCheck, combo_teams,
            adhocCheck, combo_teams_1,
            actTypeField
        ];

        callback = {
            'submit': function (data) {
                var f2Data = f2.getData(), f1Data = f.getData(), f3Data = f3.getData(), requiredData = requiredForm.getData(), relatedData = relatedForm.getData();
                f2Data.act_readonly_fields = JSON.parse(f2Data.act_readonly_fields);
                requiredData.act_required_fields = JSON.parse(requiredData.act_required_fields);

                $.extend(true, f1Data, f2Data);
                $.extend(true, f1Data, f3Data);
                $.extend(true, f1Data, requiredData);

                proxy.sendData(f1Data);
            },
            'loaded': function (data) {


                var aForms = [/*{text: translate('LBL_PMSE_FORM_OPTION_MODULE_ORIGINAL_DETAIL_VIEW'), value: 'DetailView'}, {text: translate('LBL_PMSE_FORM_OPTION_MODULE_ORIGINAL_EDIT_VIEW'), value: 'EditView'}*/],
                    rButtons = [{text: translate('LBL_PMSE_FORM_OPTION_APPROVE_REJECT'), value: 'APPROVE'}, {text: translate('LBL_PMSE_FORM_OPTION_ROUTE'), value: 'ROUTE'}],
                    aType = [{text: translate('LBL_PMSE_FORM_OPTION_ONE_WAY'), value: 'ONE_WAY'}, {text: translate('LBL_PMSE_FORM_OPTION_ROUND_TRIP'), value: 'ROUND_TRIP'}],
                    readOnlyFieldsMatrix = f2.items[0],
                    requiredFieldsMatrix = requiredForm.items[0],
                    i,
                    readOnlyFields = [],
                    requiredFields = [],
                    allTheFields = [],
                    allTheReqFields = [],
                    related,
                    item,
                    relatedItems;

                proxy.getData({'module': PROJECT_MODULE}, {
                    success: function(data) {
                        root.canvas.emptyCurrentSelection();
                        for (i = 0; i < data.act_readonly_fields.length; i += 1) {
                            allTheFields.push({
                                text: data.act_readonly_fields[i].label,
                                value: data.act_readonly_fields[i].name
                            });
                            if (data.act_readonly_fields[i].readonly) {
                                readOnlyFields.push(data.act_readonly_fields[i].name);
                            }
                        }
                        readOnlyFieldsMatrix.getHTML();
                        readOnlyFieldsMatrix.setList(allTheFields, readOnlyFields);
                        // set required fields to form as a list
                        for (i = 0; i < data.act_required_fields.length; i += 1) {
                            allTheReqFields.push({
                                text: data.act_required_fields[i].label,
                                value: data.act_required_fields[i].name
                            });
                            if (data.act_required_fields[i].required) {
                                //readOnlyFields.push(data.act_readonly_fields[i].name);
                                requiredFields.push(data.act_required_fields[i].name);
                            }
                        }
                        requiredFieldsMatrix.getHTML();
                        requiredFieldsMatrix.setList(allTheReqFields, requiredFields);

                        formsField.proxy.getData(null, {
                            success: function(forms) {
                                aForms = aForms.concat(forms.result);
                                formsField.setOptions(aForms);
                            }
                        });

                        combo_teams.proxy.getData(null, {
                            success: function(teams) {
                                combo_teams.setOptions(teams.result);
                                combo_teams_1.setOptions(teams.result);
                                App.alert.dismiss('upload');
                                w.html.style.display = 'inline';
                            }
                        });

                        responseButtons.setOptions(rButtons);
                        combo_type.setOptions(aType);

                        reassignCheck.setValue(false);
                        adhocCheck.setValue(false);
                        if (data) {
                            if (data.act_expected_time) {
                                expTimeDuration.setValue(data.act_expected_time.time);
                                expTimeCombo.setValue(data.act_expected_time.unit);
                                updateExpectedTime();
                            }

                            if (data.act_type) {
                                formsField.setValue(data.act_type);
                                actTypeField.setValue(data.act_type);
                            }
                            if (data.act_response_buttons) {
                                responseButtons.setValue(data.act_response_buttons);
                            }
                            if (data.act_reassign) {
                                if (data.act_reassign === '1') {
                                    reassignCheck.setValue(true);
                                    $(reassignCheck.html).children('input').prop('checked', true);
                                    combo_teams.setReadOnly(false);
                                    if (data.act_reassign_team) {
                                        combo_teams.setValue(data.act_reassign_team);
                                        $(combo_teams.html).children('select').val(data.act_reassign_team);
                                    }

                                }
                            }
                            if (data.act_adhoc) {
                                if (data.act_adhoc === '1') {
                                    adhocCheck.setValue(true);
                                    $(adhocCheck.html).children('input').prop('checked', true);
                                    combo_teams_1.setReadOnly(false);
                                    if (data.act_adhoc_team) {
                                        combo_teams_1.setValue(data.act_adhoc_team);
                                        $(combo_teams_1.html).children('select').val(data.act_adhoc_team);
                                    }
                                }
                            }

                            f.proxy = null;
                        }
                    }
                })
            }
        };

        f2.setCallback({submit: callback.submit});
        f3.setCallback({submit: callback.submit});
        requiredForm.setCallback({submit: callback.submit});
        relatedForm.setCallback({submit: callback.submit});

    } else {
        //TODO REVIEW THIS ELSE
        actionCSS = 'adam-menu-icon-configure';
        proxy = null;
        actionName = 'Configuration...';

        items = [
            {
                jtype: 'textarea',
                required: false,
                fieldWidth: '250px',
                fieldHeight: '100px',
                label: translate('LBL_PMSE_FORM_LABEL_SCRIPT'),
                name: 'act_script',
                helpTooltip: {
                    message: 'Enter the PHP code script'
                }
            }
        ];

        callback = {
            submit: function (data) {
                if (self.act_script !== data.act_script) {
                    self.updateScript(data.act_script);
                }
            },
            loaded: function () {
                root.canvas.emptyCurrentSelection();
                var data = {};
                data.act_script = self.act_script;
                f.data = data;
                f.applyData(true);
            }
        };

    }

    f = new Form({
        items: items,
        closeContainerOnSubmit: true,
        buttons: [
            { jtype: 'submit', caption: translate('LBL_PMSE_BUTTON_SAVE') },
            { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_CANCEL'), handler: function () {
                if (f.isDirty()) {
                    cancelInformation.setButtons([
                        {
                            jtype: 'normal',
                            caption: translate('LBL_PMSE_BUTTON_YES'),
                            handler: function () {
                                cancelInformation.hide();
                                w.close();
                            }
                        },
                        {
                            jtype: 'normal',
                            caption: translate('LBL_PMSE_BUTTON_NO'),
                            handler: function () {
                                cancelInformation.hide();
                            }
                        }
                    ]);
                    cancelInformation.show();
                } else {
                    w.close();
                }
            }}
        ],
        callback: callback,
        language: PMSE_DESIGNER_FORM_TRANSLATIONS
    });

    w.addPanel({
        title: translate('LBL_PMSE_FORM_LABEL_GENERAL_SETTINGS'),
        panel: f
    });

    if (f2) {
        w.addPanel({
            title: translate('LBL_PMSE_FORM_LABEL_READ_ONLY_FIELDS'),
            panel: f2
        });
    }
    if (requiredForm) {
        w.addPanel({
            title: translate('LBL_PMSE_FORM_LABEL_REQUIRED_FIELDS'),
            panel: requiredForm
        });
    }

    if (f3) {
        w.addPanel({
            title: translate('LBL_PMSE_FORM_LABEL_EXPECTED_TIME'),
            panel: f3
        });
    }

    action = new Action({
        text: actionName,
        cssStyle : actionCSS,
        handler: function () {
            root.canvas.project.save();
            w.show();
            w.html.style.display = 'none';
            App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
        },
        disabled: disabled
    });
    return action;
};

AdamActivity.prototype.updateScript = function (script) {
    var updateCommand;

    updateCommand = new CommandAdam(this, ['act_script'], [script]);
    updateCommand.execute();

    this.canvas.commandStack.add(updateCommand);
    return this;
};

AdamActivity.prototype.createAssignUsersAction = function () {
    var action,
        root = this,
        w,
        f,
        items,
        assignUserField,
        assignTeamField,
        combo_users,
        combo_teams,
        combo_method,
        hiddenFn,
        hiddenUpdateFn,
        callback,
        proxy,
        teams,
        users,
        cancelInformation,
        self = this;
    cancelInformation =  new MessagePanel({
        title: "Confirm",
        wtype: 'Confirm',
        message: translate('LBL_PMSE_MESSAGE_CANCEL_CONFIRM')
    });
    proxy = new SugarProxy({
        url: 'pmse_Project/ActivityDefinition/' + this.id,
        uid: this.id,
        callback: null
    });



    hiddenFn = function () {
        if (combo_method.value === 'static') {
            combo_users.setReadOnly(false);
            combo_teams.setReadOnly(true);
        } else {
            combo_users.setReadOnly(true);
            combo_teams.setReadOnly(false);
        }
    };

    hiddenUpdateFn = function () {
        switch (this.name) {
            case 'combo_teams':
                assignTeamField.setValue(combo_teams.value);
                assignUserField.setValue(null);
                break;
            case 'combo_users':
                assignTeamField.setValue(null);
                assignUserField.setValue(combo_users.value);
                break;
        }
    };

    assignTeamField = new HiddenField({
        name: 'act_assign_team'
    });

    assignUserField = new HiddenField({
        name: 'act_assign_user'
    });

    combo_users = new ComboboxField({
        jtype: 'combobox',
        label: translate('LBL_PMSE_FORM_LABEL_ASSIGN_TO_USER'),
        name: 'combo_users',
        submit: false,
        change: hiddenUpdateFn,
        proxy: new SugarProxy({
            url: 'pmse_Project/CrmData/users',
            uid: null,
            callback: null
        })
    });

    combo_teams = new ComboboxField({
        jtype: 'combobox',
        label: translate('LBL_PMSE_FORM_LABEL_ASSIGN_TO_TEAM'),
        name: 'combo_teams',
        submit: false,
        change: hiddenUpdateFn,
        proxy: new SugarProxy({
            url: 'pmse_Project/CrmData/teams/public',
            uid: 'public',
            callback: null
        })
    });

    combo_method = new ComboboxField({
        jtype: 'combobox',
        name: 'act_assignment_method',
        label: translate('LBL_PMSE_FORM_LABEL_ASSIGNMENT_METHOD'),
        change: hiddenFn,
        options: [
            {text: translate('LBL_PMSE_FORM_OPTION_ROUND_ROBIN'), value: 'balanced'},
            {text: translate('LBL_PMSE_FORM_OPTION_SELF_SERVICE'), value: 'selfservice'},
            {text: translate('LBL_PMSE_FORM_OPTION_STATIC_ASSIGNMENT'), value: 'static'}

        ],
        initialValue: 'balanced',
        required: true
    });

    callback = {

        'submit': function (data) {
            fData = f.getData();
            proxy.sendData(fData);
        },
        'loaded' : function (data) {

            proxy.getData({'module': PROJECT_MODULE}, {
                success: function(data) {
                    var aUsers = [
                        {'text': translate('LBL_PMSE_FORM_OPTION_CURRENT_USER'), 'value': 'currentuser'},
                        {'text': translate('LBL_PMSE_FORM_OPTION_RECORD_OWNER'), 'value': 'owner'},
                        {'text': translate('LBL_PMSE_FORM_OPTION_SUPERVISOR'), 'value': 'supervisor'}
                    ];
                    root.canvas.emptyCurrentSelection();
                    combo_teams.proxy.getData(null,{
                        success: function(teams) {
                            combo_teams.setOptions(teams.result);
                            assignTeamField.setValue(data.act_assign_team || teams.result[0].value);
                        }
                    });
                    combo_users.proxy.getData(null, {
                        success: function(users) {
                            aUsers = aUsers.concat(users.result);
                            combo_users.setOptions(aUsers);
                            App.alert.dismiss('upload');
                            w.html.style.display = 'inline';
                            assignUserField.setValue(data.act_assign_user || aUsers[0].value);
                        }
                    });
                    if (data) {
                        combo_method.setValue(data.act_assignment_method);

                        if (data.act_assignment_method === 'static') {
                            combo_users.setValue(data.act_assign_user);
                            combo_users.setReadOnly(false);
                            combo_teams.setReadOnly(true);
                        } else {
                            combo_teams.setValue(data.act_assign_team);
                            combo_users.setReadOnly(true);
                            combo_teams.setReadOnly(false);
                        }
                    }
                    f.proxy = null;

                }
            });


        }
    };

    f = new Form({
        items: [combo_method, combo_teams, combo_users, assignUserField, assignTeamField],
        closeContainerOnSubmit: true,
        buttons: [
            { jtype: 'submit', caption: translate('LBL_PMSE_BUTTON_SAVE') },
            { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_CANCEL'), handler: function () {
                if (f.isDirty()) {
                    cancelInformation.setButtons([
                        {
                            jtype: 'normal',
                            caption: translate('LBL_PMSE_BUTTON_YES'),
                            handler: function () {
                                cancelInformation.hide();
                                w.close();
                            }
                        },
                        {
                            jtype: 'normal',
                            caption: translate('LBL_PMSE_BUTTON_NO'),
                            handler: function () {
                                cancelInformation.hide();
                            }
                        }
                    ]);
                    cancelInformation.show();
                } else {
                    w.close();
                }
            }}
        ],
        callback: callback,
        language: PMSE_DESIGNER_FORM_TRANSLATIONS
    });
    w = new Window({
        width: 500,
        height: 350,
        title: translate('LBL_PMSE_FORM_TITLE_USER_DEFINITION') + ': ' + this.getName(),
        modal: true
    });
    w.addPanel(f);

    action = new Action({
        text: translate('LBL_PMSE_CONTEXT_MENU_USERS'),
        cssStyle : 'adam-menu-icon-user',
        handler: function () {
            root.canvas.project.save();
            w.show();
            w.html.style.display = 'none';
            App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
        },
        disabled: false
    });

    return action;
};

AdamActivity.prototype.actionFactory = function (type) {
    var action, actionText, actionCSS, root = this, w, f, proxy = null, items = [], callback = {},
        disabled = false, windowTitle = '', wWidth, wHeight, labelWidth = '30%', messageMap, fields,
        combo_users,
        combo_teams,
        combo_method,
        navigableData,
        changeFieldsFn,
        combo_modules,
        data,
        restored_fields,
        fields_updater,
        initialModule,
        combo_business,
        i,
        hidden_module,
        cancelInformation,
        hidden_method,
        updater_field,
        updateRecordOwner;
    cancelInformation =  new MessagePanel({
        title: "Confirm",
        wtype: 'Confirm',
        message: translate('LBL_PMSE_MESSAGE_CANCEL_CONFIRM')
    });
    switch (type) {
        case 'NONE':
            actionText = translate('LBL_PMSE_CONTEXT_MENU_SETTINGS');
            disabled = true;
            actionCSS = 'adam-menu-icon-configure';
            break;
        case 'ASSIGN_USER':
            combo_users = new ComboboxField({
                jtype: 'combobox',
                label: translate('LBL_PMSE_FORM_LABEL_ASSIGN_TO_USER'),
                name: 'act_assign_user',
                submit: true,
                //change: hiddenUpdateFn,
                proxy: new SugarProxy({
                    url: 'pmse_Project/CrmData/users',
                    uid: null,
                    callback: null
                }),
                required: true
            });
            //here add checkbox
            updateRecordOwner = new CheckboxField({
                name: 'act_update_record_owner',
                label: translate('LBL_PMSE_FORM_LABEL_UPDATE_RECORD_OWNER'),
                required: false,
                value: false,
                options: {

                    labelAlign: 'right',
                    marginLeft: 80

                }
            });
            proxy = new SugarProxy({
                url: 'pmse_Project/ActivityDefinition/' + this.id,
                uid: this.id,
                callback: null
            });
            items = [combo_users, updateRecordOwner];
            wWidth = 500;
            wHeight = 160;
            actionText = translate('LBL_PMSE_CONTEXT_MENU_SETTINGS');
            actionCSS = 'adam-menu-icon-configure';
            callback = {
                'loaded': function (data) {
                    var users, aUsers = [{'text': 'Select...', 'value': ''}];
                    root.canvas.emptyCurrentSelection();
                    users = combo_users.proxy.getData(null, {
                        success: function(users) {
                            aUsers = aUsers.concat(users.result);
                            combo_users.setOptions(aUsers);
                            if (data) {
                                combo_users.setValue(data.act_assign_user || aUsers[0].value);
                                if (data.act_update_record_owner && data.act_update_record_owner == 1){
                                    updateRecordOwner.setValue(true);
                                    $(updateRecordOwner.html).children('input').prop('checked', true);
                                }
                            }
                            App.alert.dismiss('upload');
                            w.html.style.display = 'inline';
                        }

                    });

                }
            };
            windowTitle = translate('LBL_PMSE_FORM_TITLE_ASSIGN_USER') + ': ' + this.getName();
            break;
        case 'ASSIGN_TEAM':
            combo_teams = new ComboboxField({
                jtype: 'combobox',
                label: translate('LBL_PMSE_FORM_LABEL_ASSIGN_TO_TEAM'),
                name: 'act_assign_team',
                submit: true,
                proxy: new SugarProxy({
                    url: 'pmse_Project/CrmData/teams/public',
                    uid: 'public',
                    callback: null

                })
            });
            //here add checkbox
            updateRecordOwner = new CheckboxField({
                name: 'act_update_record_owner',
                label: translate('LBL_PMSE_FORM_LABEL_UPDATE_RECORD_OWNER'),
                required: false,
                value: false,
                options: {
                    labelAlign: 'right',
                    marginLeft: 80
                }
            });
            combo_method = new ComboboxField({
                jtype: 'combobox',
                name: 'act_assignment_method',
                label: translate('LBL_PMSE_FORM_LABEL_ASSIGNMENT_METHOD'),
                options: [
                    {text: translate('LBL_PMSE_FORM_OPTION_ROUND_ROBIN'), value: 'balanced'},
                    {text: translate('LBL_PMSE_FORM_OPTION_SELF_SERVICE'), value: 'selfservice'}
                ],
                initialValue: 'balanced',
                editable: false,
                readOnly: true
            });
            hidden_method = new HiddenField({
                name: 'act_assignment_method',
                initialValue: 'balanced'
            });
            proxy = new SugarProxy({
                url: 'pmse_Project/ActivityDefinition/' + this.id,
                uid: this.id,
                callback: null
            });

            items = [combo_teams, updateRecordOwner, hidden_method];
            wWidth = 500;
            wHeight = 160;
            actionText = translate('LBL_PMSE_CONTEXT_MENU_SETTINGS');
            actionCSS = 'adam-menu-icon-configure';
            callback = {
                'loaded': function (data) {
                    var teams;
                    root.canvas.emptyCurrentSelection();
                    teams = combo_teams.proxy.getData(null,{
                        success: function(teams) {
                            combo_teams.setOptions(teams.result);
                            if (data) {
                                combo_teams.setValue(data.act_assign_team || teams.result[0].value);
                                if (data.act_update_record_owner && data.act_update_record_owner == 1){
                                    updateRecordOwner.setValue(true);
                                    $(updateRecordOwner.html).children('input').prop('checked', true);
                                }
                            }
                            App.alert.dismiss('upload');
                            w.html.style.display = 'inline';
                        }
                    });
                }
            };
            windowTitle = translate('LBL_PMSE_FORM_TITLE_ASSIGN_TEAM') + ': ' + this.getName();
            break;
        case 'CHANGE_FIELD':
            labelWidth = '20%';
            navigableData = { 'edit': true };
            changeFieldsFn = function () {
                App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
                updater_field.proxy.url = 'pmse_Project/CrmData/fields/'+ combo_modules.value;
                data = updater_field.proxy.getData(null, {
                    success: function(data) {
                        App.alert.dismiss('upload');
                        if (data) {
                            updater_field.setOptions(data.result, true);
                        }

                    }
                });



            };
            combo_modules = new ComboboxField({
                label: translate('LBL_PMSE_FORM_LABEL_MODULE'),
                name: 'act_field_module',
                submit: true,
                change: changeFieldsFn,
                proxy: new SugarProxy({
                    url: 'pmse_Project/CrmData/related/' + PROJECT_MODULE,
                    uid: PROJECT_MODULE,
                    callback: null
                })
            });
            updater_field = new UpdaterField({
                label: translate('LBL_PMSE_FORM_LABEL_FIELDS'),
                name: 'act_fields',
                submit: true,
                decimalSeparator: SUGAR.App.config.defaultDecimalSeparator,
                numberGroupingSeparator: SUGAR.App.config.defaultNumberGroupingSeparator,
                proxy: new SugarProxy({
                    url: 'pmse_Project/CrmData/fields/' + PROJECT_MODULE,
                    uid: null,
                    callback: null
                }),
                fieldWidth: 470,
                fieldHeight: 260,
                hasCheckbox: true
            });

            wWidth = 670;
            wHeight = 400;
            actionText = translate('LBL_PMSE_CONTEXT_MENU_SETTINGS');
            actionCSS = 'adam-menu-icon-configure';
            items = [combo_modules, updater_field];
            proxy = new SugarProxy({
                url: 'pmse_Project/ActivityDefinition/' + this.id,
                //restClient: this.canvas.project.restClient,
                uid: this.id,
                callback: null
            });
            windowTitle = translate('LBL_PMSE_FORM_TITLE_CHANGE_FIELDS') + ': ' + this.getName();
            callback = {
                'loaded' : function (data) {
                    var modules, opt = [], listProxy;
                    root.canvas.emptyCurrentSelection();

                    combo_modules.proxy.getData(null, {
                        success: function(modules) {
                            if (modules && modules.success) {
                                combo_modules.setOptions(modules.result);
                                updater_field.proxy.uid = PROJECT_MODULE;
                                initialModule = PROJECT_MODULE;
                                updater_field.proxy.url = 'pmse_Project/CrmData/fields/' + initialModule
                                updater_field.proxy.getData(null, {
                                    success: function(fields) {
                                        if (fields) {

                                            updater_field.setVariables(fields.result);
                                            updater_field.setOptions(fields.result, true);
                                            updater_field.setValue(data.act_fields || null);
                                            App.alert.dismiss('upload');
                                            w.html.style.display = 'inline';
                                        }

                                    }
                                });
                            }
                        }
                    });
                }
            };
            break;
        case 'ADD_RELATED_RECORD':
            labelWidth = '20%';
            navigableData = { 'edit': true };
            changeFieldsFn = function () {
                App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
                updater_field.proxy.uid = combo_modules.value;
                updater_field.proxy.url = 'pmse_Project/CrmData/addRelatedRecord/' + combo_modules.value;
                updater_field.proxy.getData(null, {
                    success: function(data) {
                        App.alert.dismiss('upload');
                        if (data) {
                            updater_field.setOptions(data.result, true);
                        }
                    }
                });

            };
            combo_modules = new ComboboxField({
                jtype: 'combobox',
                label: translate('LBL_PMSE_FORM_LABEL_RELATED_MODULE'),
                name: 'act_field_module',
                submit: true,
                change: changeFieldsFn,
                proxy: new SugarProxy({
                    url: 'pmse_Project/CrmData/related/' + PROJECT_MODULE,
                    uid: PROJECT_MODULE,
                    callback: null
                })
            });
            updater_field = new UpdaterField({
                label: translate('LBL_PMSE_FORM_LABEL_FIELDS'),
                name: 'act_fields',
                submit: true,
                decimalSeparator: SUGAR.App.config.defaultDecimalSeparator,
                numberGroupingSeparator: SUGAR.App.config.defaultNumberGroupingSeparator,
                proxy: new SugarProxy({
                    url: 'pmse_Project/CrmData/addRelatedRecord/'+ PROJECT_MODULE,
                    uid: null,
                    callback: null
                }),
                fieldWidth: 470,
                fieldHeight: 260
            });
            wWidth = 680;
            wHeight = 420;
            actionText = translate('LBL_PMSE_CONTEXT_MENU_SETTINGS');
            actionCSS = 'adam-menu-icon-configure';
            items = [combo_modules, updater_field];
            proxy = new SugarProxy({
                url: 'pmse_Project/ActivityDefinition/' + this.id,
                uid: this.id,
                callback: null
            });
            windowTitle = translate('LBL_PMSE_FORM_TITLE_ADD_RELATED_RECORD') + ': ' + this.getName();
            callback = {
                'loaded' : function (data) {
                    var modules, opt = [], listProxy;
                    root.canvas.emptyCurrentSelection();
                    combo_modules.proxy.getData(null, {
                       success: function(modules) {
                           if (modules && modules.success) {
                               combo_modules.setOptions(modules.result);
                               initialModule = data.act_field_module || modules.result[0].value;
                               updater_field.proxy.uid = PROJECT_MODULE;
                               updater_field.proxy.url = 'pmse_Project/CrmData/addRelatedRecord/' + initialModule;
                               updater_field.proxy.getData(null,{
                                   success: function(fields) {
                                       updater_field.setOptions(fields.result);
                                       updater_field.setVariables(fields.result);
                                       updater_field.setValue(data.act_fields || null);
                                       App.alert.dismiss('upload');
                                       w.html.style.display = 'inline';
                                   }
                               });
                           }
                       }
                    });

                }
            };
            break;
        case 'BUSINESS_RULE':
            combo_business = new ComboboxField({
                label: translate('LBL_PMSE_LABEL_RULE'),
                name: 'act_fields',
                submit: true,
                proxy: new SugarProxy({
                    url: 'pmse_Project/CrmData/rulesets/' + adamUID,
                    uid: adamUID,
                    callback: null
                })
            });
            hidden_module = new HiddenField({
                name: 'act_field_module',
                initialValue: PROJECT_MODULE
            });
            wWidth = 500;
            wHeight = 140;
            actionText = translate('LBL_PMSE_CONTEXT_MENU_SETTINGS');
            actionCSS = 'adam-menu-icon-configure';
            items = [combo_business, hidden_module];
            proxy = new SugarProxy({
                url: 'pmse_Project/ActivityDefinition/' + this.id,
                uid: this.id,
                callback: null
            });
            windowTitle = translate('LBL_PMSE_FORM_TITLE_BUSINESS_RULE') + ': ' + this.getName();
            callback = {
                'loaded': function (data) {
                    var rules, aRules = [{'text': translate('LBL_PMSE_FORM_OPTION_SELECT'), 'value': '', 'selected': true}];
                    root.canvas.emptyCurrentSelection();
                    //rules = combo_business.proxy.getData();
                    combo_business.proxy.getData(null,{
                        success: function(rules) {
                            if (rules && rules.success) {
                                aRules = aRules.concat(rules.result);
                                combo_business.setOptions(aRules);
                                if (data) {
                                    combo_business.setValue(data.act_fields || '');
                                }
                            }
                            App.alert.dismiss('upload');
                            w.html.style.display = 'inline';

                        }
                    });

                }
            };
            break;
    }

    f = new Form({
        proxy: proxy,
        items: items,
        closeContainerOnSubmit: true,
        buttons: [
            { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_SAVE'), handler: function () {
                if (fields_updater && fields_updater.multiplePanel) {
                    fields_updater.multiplePanel.close();
                }
                f.submit();
            }},
            { jtype: 'normal', caption: translate('LBL_PMSE_BUTTON_CANCEL'), handler: function () {
                if (fields_updater && fields_updater.multiplePanel) {
                    fields_updater.multiplePanel.close();
                }

                if (f.isDirty()) {
                    cancelInformation.setButtons(
                        [
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_YES'),
                                handler: function () {
                                    cancelInformation.hide();
                                    w.close();
                                }
                            },
                            {
                                jtype: 'normal',
                                caption: translate('LBL_PMSE_BUTTON_NO'),
                                handler: function () {
                                    cancelInformation.hide();
                                }
                            }
                        ]
                    );
                    cancelInformation.show();
                } else {
                    w.close();
                }
            }}
        ],
        labelWidth: labelWidth,
        callback: callback,
        language: PMSE_DESIGNER_FORM_TRANSLATIONS
    });

    w = new Window({
        width: wWidth,
        height: wHeight,
        title: windowTitle,
        modal: true
    });
    w.addPanel(f);

    action = new Action({
        text: actionText,
        cssStyle: actionCSS,
        handler: function () {
            root.canvas.project.save();
            w.show();
            w.html.style.display = 'none';
            App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoclose: false});
        },
        disabled: disabled
    });
    return action;
};
/*global jCore, AdamShape, AdamArtifactResizeBehavior, $, Action
 */
/**
 * @class AdamArtifact
 * Handle BPMN Text Annotations
 *
 *
 * @constructor
 * Creates a new instance of the class
 * @param {Object} options
 */
var AdamArtifact = function (options) {
    AdamShape.call(this, options);
    /**
     * Defines the type artifact
     * @type {String}
     */
    this.art_type = null;
    /**
     * Defines the unique identifier
     * @type {String}
     */
    this.art_uid = null;
    /**
     * Defines the atifact's category associated
     * @type {String}
     */
    this.art_category_ref = null;

    AdamArtifact.prototype.initObject.call(this, options);
};
AdamArtifact.prototype = new AdamShape();

/**
 * Defines the object type
 * @type {String}
 */
AdamArtifact.prototype.type = "AdamArtifact";
AdamArtifact.prototype.adamArtifactResizeBehavior = null;

/**
 * Add resize behavior factory for extend  the regular behavior clases
 * @param {String} type
 * @returns {TextAnnotationResizeBehavior}
 */
AdamArtifact.prototype.resizeBehaviorFactory = function (type) {
    if (type === 'adamArtifactResize') {
        if (!this.adamArtifactResizeBehavior) {
            this.adamArtifactResizeBehavior = new AdamArtifactResizeBehavior();
        }
        return this.adamArtifactResizeBehavior;
    } else {
        return AdamShape.prototype.resizeBehaviorFactory.call(this, type);
    }
};

/**
 * Initialize the object with the default values
 * @param {Object} options
 */
AdamArtifact.prototype.initObject = function (options) {
    var defaults = {
        art_type: 'TEXTANNOTATION'
    };
    $.extend(true, defaults, options);
    this.setArtifactType(defaults.art_type);
    if (defaults.art_name) {
        this.setName(defaults.art_name);
    }
    this.setArtifactUid(defaults.art_uid || null);
    this.setCategoryRef(defaults.art_category_ref || null);
};

/**
 * Sets the artifact type property
 * @param {String} type
 * @return {*}
 */
AdamArtifact.prototype.setArtifactType = function (type) {
    this.art_type = type;
    return this;
};

/**
 * Sets the artifact's category reference
 * @param {String} value
 * @return {*}
 */
AdamArtifact.prototype.setCategoryRef = function (value) {
    this.art_category_ref = value;
    return this;
};

/**
 * Sets the artifact unique identifier
 * @param {String} value
 * @return {*}
 */
AdamArtifact.prototype.setArtifactUid = function (value) {
    this.art_uid = value;
    return this;
};

/**
 * Returns the clean object to be sent to the backend
 * @return {Object}
 */
AdamArtifact.prototype.getDBObject = function () {
    var name = this.getName();
    return {
        art_uid: this.art_uid,
        art_name: name,
        art_type: this.art_type,
        art_category_ref: this.art_category_ref,
        bou_x: this.x,
        bou_y: this.y,
        bou_width: this.width,
        bou_height: this.height,
        bou_container: 'bpmnDiagram',
        element_id: this.canvas.dia_id
    };
};

/**
 * Extends the createHTML method to customize css classes
 * @return {String}
 */
AdamArtifact.prototype.createHTML = function () {
    AdamShape.prototype.createHTML.call(this);
    this.style.addClasses(['adam_artifact']);
    return this.html;
};

/**
 * Extends the paint method to draw text annotation lines
 */
AdamArtifact.prototype.paint = function () {
    //TODO Rewrite this method using Segments object
};


AdamArtifact.prototype.getArtifactType = function () {
    return this.art_type;
};

AdamArtifact.prototype.getContextMenu = function () {
    var deleteAction,
        self = this;

    deleteAction = new Action({
        text: 'Delete',
        cssStyle: 'adam-menu-icon-delete',
        handler: function () {
            var shape;
            shape = self.canvas.customShapes.find('id', self.id);
            if (shape) {
                shape.canvas.emptyCurrentSelection();
                shape.canvas.addToSelection(shape);
                shape.canvas.removeElements();
            }
        }
    });

    return {
        items: [deleteAction]
    };
};


/*global $ */
var PropertiesGrid = function (selector) {
    this.element = null;
    PropertiesGrid.prototype.init.call(this, selector);
};

PropertiesGrid.prototype.type = 'propertiesGrid';

PropertiesGrid.prototype.init = function (selector) {
    this.element = $(selector);
    return this;
};

PropertiesGrid.prototype.load = function (setup) {
    this.element.progrid(setup);
    return this;
};

PropertiesGrid.prototype.clear = function () {
    this.element.empty();
    return this;
};

PropertiesGrid.prototype.forceFocusOut = function () {
    try {
        this.element.find('input, select').trigger('focusout');
    } catch (e) {}
};

PropertiesGrid.prototype.setWidth = function (width) {
    this.element.progrid('setWidth', width);
    return this;
};

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

/*global jCore,

 */
var CommandDefaultFlow = function (receiver, destID) {
    jCore.Command.call(this, receiver);
    this.before = null;
    this.after = null;
    this.prefix = null;
    CommandDefaultFlow.prototype.initObject.call(this, destID);
};

CommandDefaultFlow.prototype = new jCore.Command();

CommandDefaultFlow.prototype.type = "CommandDefaultFlow";

CommandDefaultFlow.prototype.initObject = function (destID) {
    var i, s, p;

    this.prefix = {
        "AdamActivity": "act",
        "AdamGateway": "gat"
    };

    this.before = {
        defaultDestID: this.receiver[this.prefix[this.receiver.type] + "_default_flow"] || "",
        connections: []
    };
    this.after = {
        defaultDestID: destID,
        connections: null
    };
    p = this.receiver.getPorts();
    s = p.getSize();
    for (i = 0; i < s; i += 1) {
        this.before.connections.push({
            id: p.get(i).connection.getID(),
            condition: p.get(i).connection.getFlowCondition(),
            type: p.get(i).connection.getFlowType()
        });
    }
};

CommandDefaultFlow.prototype.fireTrigger = function (undo) {
    var i, p, s, c, tmp, v, updatedElement = [{
        id: this.receiver.getID(),
        type: this.receiver.type,
        relatedObject: this.receiver,
        fields: [{
            field: "default_flow",
            //newVal: this.receiver[this.prefix[this.receiver.type] + "_default_flow"] || null,
            newVal: this.receiver[this.prefix[this.receiver.type] + "_default_flow"] || 0,
            oldVal: undo ? this.after.defaultDestID : this.before.defaultDestID
        }]
    }];

    p = this.receiver.getPorts();
    s = p.getSize();
    for (i = 0; i < s; i += 1) {
        c = p.get(i).connection;
        tmp = {
            id: c.getID(),
            relatedObject: c,
            type: c.type,
            fields: []
        };

        v = undo ? this.after.connections[i].type : this.before.connections[i].type;
        if (c.getFlowType() !== v) {
            tmp.fields.push({
                field: "type",
                newVal: c.getFlowType(),
                oldVal: v
            });
        }

        v = undo ? this.after.connections[i].condition : this.before.connections[i].condition;
        if (c.getFlowCondition() !== v) {
            tmp.fields.push({
                field: "condition",
                newVal: c.getFlowCondition(),
                oldVal: v
            });
        }

        if (tmp.fields.length > 0) {
            updatedElement.push(tmp);
        }
    }

    this.receiver.getCanvas().triggerDefaultFlowChangeEvent(updatedElement);
};

CommandDefaultFlow.prototype.execute = function () {
    var i, p, s, c;
    this.receiver.setDefaultFlow(this.after.defaultDestID === "" ? 0 : this.after.defaultDestID);
    if (!this.after.connections) {
        this.after.connections = [];
        p = this.receiver.getPorts();
        s = p.getSize();
        for (i = 0; i < s; i += 1) {
            c = p.get(i).connection;
            this.after.connections.push({
                id: c.getID(),
                condition: c.getFlowCondition(),
                type: c.getFlowType()
            });
        }
    }
    this.fireTrigger();
};

CommandDefaultFlow.prototype.undo = function () {
    var i, c, t;

    for (i = 0; i < this.before.connections.length; i += 1) {
        c = this.receiver.canvas.getConnections().find("id", this.before.connections[i].id);
        c.setFlowCondition(this.before.connections[i].condition);
        t = this.before.connections[i].type;
        if (c.getFlowType() !== t) {
            if (t !== "DEFAULT") {
                c.setFlowType(t)
                    .changeFlowType(t.toLowerCase());
            }
        }
    }
    this.receiver.setDefaultFlow(this.before.defaultDestID);
    this.fireTrigger(true);
};

CommandDefaultFlow.prototype.redo = function () {
    this.execute();
};
/*global jCore, $

 */
var CommandConnectionCondition = function (receiver, condition) {
    jCore.Command.call(this, receiver);
    this.before = null;
    this.after = null;
    CommandConnectionCondition.prototype.initObject.call(this, condition);
};

CommandConnectionCondition.prototype = new jCore.Command();

CommandConnectionCondition.prototype.type = "CommandConnectionCondition";

CommandConnectionCondition.prototype.initObject = function (condition) {
    condition = $.trim(condition);
    this.before = {
        condition: this.receiver.getFlowCondition(),
        type: this.receiver.flo_type
    };
    this.after = {
        condition: condition,
        type: condition ? "CONDITIONAL" : "SEQUENCE"
    };
};

CommandConnectionCondition.prototype.updateConditionMarker = function () {
    if (this.receiver.getFlowCondition() && this.receiver.getSrcPort().parent.type !== 'AdamGateway') {
        this.receiver.setFlowType('CONDITIONAL');
        this.receiver.changeFlowType('conditional');
    } else {
        this.receiver.setFlowType('SEQUENCE');
        this.receiver.changeFlowType('sequence');
    }
};

CommandConnectionCondition.prototype.fireTrigger = function (undo) {
    var fields, v, n;

    fields = [{
        field: 'condition',
        oldVal: undo ? this.after.condition : this.before.condition,
        newVal: this.receiver.getFlowCondition()
    }];

    v = undo ? this.after.type : this.before.type;
    n = this.receiver.getFlowType();
    if (n !== v) {
        fields.push({
            field: 'type',
            oldVal: v,
            newVal: n
        });
    }
    this.receiver.canvas.triggerConnectionConditionChangeEvent(this.receiver, fields);
};

CommandConnectionCondition.prototype.execute = function () {
    this.receiver.setFlowCondition(this.after.condition);
    this.updateConditionMarker();
/*
    fields = [{
        field: 'condition',
        oldVal: this.before.condition,
        newVal: this.after.condition
    }];

    v = this.receiver.getFlowType();
    if(this.before.type !== v) {
        fields.push({
            field: 'type',
            oldVal: this.before.type,
            newVal: v
        });
    }

    this.receiver.canvas.triggerConnectionConditionChangeEvent(this.receiver, fields);*/
    this.fireTrigger();
};

CommandConnectionCondition.prototype.undo = function () {
    this.receiver.setFlowCondition(this.before.condition);
    this.updateConditionMarker();
    /*this.receiver.canvas.triggerConnectionConditionChangeEvent(this.receiver, [
        {
            field: 'type',
            oldVal: this.after.type,
            newVal: this.before.type
        },
        {
            field: 'condition',
            oldVal: this.after.condition,
            newVal: this.before.condition
        }
    ]);*/
    this.fireTrigger(true);
};

CommandConnectionCondition.prototype.redo = function () {
    this.execute();
};
/*global jCore

 */
var AdamCommandReconnect = function (rec, opt) {
    var NewObj = function (receiver) {
        jCore.CommandReconnect.call(this, receiver);
        NewObj.prototype.initObject.call(this, receiver, opt);
    };

    NewObj.prototype = new jCore.CommandReconnect(rec);

    NewObj.prototype.initObject = function (receiver, opt) {

        this.prefix = {
            "AdamActivity": "act",
            "AdamGateway": "gat"
        };

        this.srcShape = this.receiver.connection.getSrcPort().parent;
        this.before.type = this.receiver.connection.getFlowType();
        this.before.condition = this.receiver.connection.getFlowCondition();
        this.before.defaultFlow = this.srcShape.type === 'AdamGateway' || this.srcShape.type === 'AdamActivity' ? this.srcShape[this.prefix[this.srcShape.type] + "_default_flow"] : "";
        this.after.type = null;
        this.condition = null;
        this.after.defaultFlow = "";
    };

    NewObj.prototype.fireTrigger = function (undo) {
        var updatedElement = [], connection = this.receiver.connection, v, flowChanges, n;
        if (this.after.type === 'DEFAULT' || this.before.type === 'DEFAULT') {
            updatedElement.push({
                id: this.srcShape.getID(),
                relatedObject: this.srcShape,
                type: this.srcShape.type,
                fields: [{
                    field: "default_flow",
                    newVal: this.srcShape[this.prefix[this.srcShape.type] + "_default_flow"],
                    oldVal: undo ? this.after.defaultFlow : this.before.defaultFlow
                }]
            });
        }

        flowChanges = {
            id: connection.getID(),
            relatedObject: connection,
            type: connection.type,
            fields: []
        };

        v = undo ? this.after.type : this.before.type;
        n = connection.getFlowType();
        if (v !== n) {
            flowChanges.fields.push({
                field: "type",
                newVal: n,
                oldVal: v
            });
        }

        v = undo ? this.after.condition : this.before.condition;
        n = connection.getFlowCondition();
        if (v !== n) {
            flowChanges.fields.push({
                field: "condition",
                newVal: n,
                oldVal: v
            });
        }

        if (flowChanges.fields.length > 0) {
            updatedElement.push(flowChanges);
        }

        this.receiver.getCanvas().triggerDefaultFlowChangeEvent(updatedElement);
    };

    NewObj.prototype.execute = function () {
        var connection = this.receiver.connection;
        connection.setFlowType(this.after.type);

        jCore.CommandReconnect.prototype.execute.call(this);

        if (connection.getSrcPort().getParent().type === 'AdamGateway' || (!connection.getFlowCondition() && this.srcShape.type !== "AdamArtifact" && connection.getDestPort().parent.type !== "AdamArtifact")) {
            connection.setFlowType("SEQUENCE").changeFlowType('sequence');
        } else if ((connection.getSrcPort().getParent().type === 'AdamActivity' && connection.getFlowCondition()) && !(connection.getSrcPort().getParent().type === 'AdamArtifact' || connection.getDestPort().getParent().type === 'AdamArtifact')) {
            connection.setFlowType("CONDITIONAL").changeFlowType('conditional');
        } else {
            connection.setFlowType("ASSOCIATION").setFlowCondition("").changeFlowType('association');
            if (this.srcShape.type === "AdamActivity" || this.srcShape.type === "AdamGateway") {
                this.srcShape[this.prefix[this.srcShape.type] + "_default_flow"] = "";
                this.after.defaultFlow = "";
            }
        }


        if (!this.after.type || !this.after.condition) {
            this.after.type = connection.getFlowType();
            this.after.condition = connection.getFlowCondition();
        }
        this.fireTrigger();
    };

    NewObj.prototype.undo = function () {
        var connection = this.receiver.connection,
            prev = {
                type: connection.getFlowType(),
                condition: connection.getFlowCondition()
            };
        jCore.CommandReconnect.prototype.undo.call(this);
        connection.setFlowCondition(this.before.condition)
            .setFlowType(this.before.type)
            .changeFlowType(this.before.type.toLowerCase());
        if (this.srcShape.updateDefaultFlow) {
            this.srcShape.updateDefaultFlow(this.before.defaultFlow);
        }
        this.fireTrigger(true);
    };
    return new NewObj(rec);
};

 /**
 * @class
 * @name jQuery
 * @exports $ as jQuery
 */

(function( $ ) {
   /**
   * Creates a method object. This should be invoked as a method rather than constructed using new.
   * @class methods
   */
    var div = null;
    var methods = {
        /**
        * init method, the main methods for actions whit th tree
        * @constructs
        */
        init : function( options ) {
            
            var settings = {
                container: $(this),
                id        :'root'
            };
            return this.each(function(){
                if(options){
                    settings = $.extend(settings,options);
                }
                //HERE would be the code
                 var div =settings.container;
                //<div class="content_tree"></div>;
                div.append ($('<div>').addClass("content_tree"));
                //<ul id="root" class="menu"></ul>;
                var root = $('<ul>').attr({'class': 'tree-menu', id: settings.id});
                $('.content_tree').append(root);
                var tree=settings.items;
                if (typeof tree != 'undefined' && tree !== null)
                    createTree(root,tree);
                
                if (!options.collapsed) {
                    $('#'+settings.id+' ul').each(function() {
                        $(this).css("display", "none");
                    });
                }
                $('#'+settings.id+' .category').click(function() {
                    var childid = "#" + $(this).attr("childid");
			
                    if ($(childid).css("display") == "none") {
                        $(childid).css("display", "block");
                    } else {
                        $(childid).css("display", "none");
                    }
                    if ($(this).hasClass("tree_cat_close")) {
                        $(this).removeClass("tree_cat_close").addClass("tree_cat_open");
                    }	else {
                        $(this).removeClass("tree_cat_open").addClass("tree_cat_close");
                    }
                });
                $('.treechild').hover(
                    function(){
                        if ($(this).attr("status")=="unmarked"){
                            var id="#" + $(this).attr("id");
                            $(id).css("background","#EFF5FB");
                        }
                    },
                    function(){
                        if ($(this).attr("status")=="unmarked"){
                            var id="#" + $(this).attr("id");
                            $(id).css("background","#fff");
                        }
				
                    }
                    );

                $(".details").click(function(){
                    var id="#" + $(this).attr("desc");
                    $(".treechild").attr("status","unmarked");
                    $(".treechild").css("background","#fff");
			
                    $(id).css("background","#CEE3F6");
                    $(id).attr("status","marked");
                    var oShape = {};
                    if ($(this).attr("uid") !== undefined)
                        oShape.uid=$(this).attr("uid");
                    if ($(this).attr("name") !== undefined)
                        oShape.name=$(this).attr("name");
                    if ($(this).attr("type") !== undefined)
                        oShape.type=$(this).attr("type");
                    if (typeof settings.select !== 'undefined' && settings.select!== null) {
                        settings.select.call(this,oShape);
                    }
                });
                $('.treechild > a ').css({'text-decoration':'none'}); //clean a atribute decoratios
            });
        },
        
        
       
        /**
        * sample method
        * @param {String} a
        * @param {Object} b
        * @methodOf jQuery#
        * @name jQuery#example
        */
        example : function(a , b){}

    };
    /**
     *pmtree  method
     * @param {Object} method
    */
    $.fn.pmtree = function( method ) {
        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exsts!' );
        }
        return true;
    };
    
     /**
        * createTree method
        * @param {Object} root
        * @param {Object} tree
        * @return append the html new code added to #root div
        */
        function createTree (root, tree) {
            var html='';
            for (var i=0; i< tree.length; i++){
               var li = $('<li>');
               html = $('<div>').addClass('treechild').attr({id: i, status:'unmarked'}).css({'cursor':'pointer', 'text-decoration':'none'});
               html.append($('<a>').addClass('tree_cat_close category').attr({childid:'c_'+i}).html('&nbsp;&nbsp;&nbsp'));
               // html  =  '<li>	<div class="treechild" id="'+i+'" status="unmarked"> <a childid = "c_'+i+'" class="tree_cat_close category">&nbsp;&nbsp;&nbsp;</a>';
               
                //alert(tree[i].icon);
                if (typeof tree[i].icon !== 'undefined' && tree[i].icon !== null) {
                    //html += '<a desc="'+i+'" class="details" name="'+tree[i].name+'" uid="'+tree[i].id+'" type="'+tree[i].type+'"><i class="'+tree[i].icon+'"></i> '+tree[i].name+'</a>';
                     html.append( '<a desc="'+i+'" class="details" name="'+tree[i].name+'" uid="'+tree[i].id+'" type="'+tree[i].type+'"><i class="'+tree[i].icon+'"></i> '+tree[i].name+'</a>');
                }
                else
                    //html +=  '<a desc="'+i+'" class="details" name="'+tree[i].name+'" uid="'+tree[i].id+'" type="'+tree[i].type+'"><i class="tree-folder-open"></i> '+tree[i].name+'</a>';
                    html.append( '<a desc="'+i+'" class="details" name="'+tree[i].name+'" uid="'+tree[i].id+'" type="'+tree[i].type+'"><i class="tree-folder-open"></i> '+tree[i].name+'</a>');
               // html += '</div>'
                //html += '</li>'
                li.append(html);
               // $('#'+settings.id).append(li);
                if (typeof tree[i].items != "undefined" &&  tree[i].items !== null )	{
                   //html +=	methods.addNodes(tree[i].items,'c_'+i);
                   li.append(addNodes(tree[i].items,'c_'+i));
                }
                
                root.append(li);
                //$('#'+settings.id).append(html);
            }
        }
        /**
        * addNodes method
        * @param {object} node
        * @param {string} childid
        * @return {string} html the added nodes
        */
        function addNodes(node,childid) {
            var html = '';
            html += '<ul id="'+childid+'">' ;
            for (var i=0; i<node.length; i++){
                //alert(node[i].name)
                html += '<li>';
                if (typeof node[i].items != "undefined" &&  node[i].items !== null )	{
                    html += '<div class="treechild" id="'+childid+'_'+i+'" status="unmarked" style="cursor:pointer; text-decoration:none;"><a childid = "c_'+childid+'_'+i+'"+ class="tree_cat_close category">&nbsp;&nbsp;&nbsp;</a>'; 
                    if (node[i].icon != 'undefined' && node[i].icon !== null)
						html +='<a desc="'+childid+'_'+i+'" class="details" name="'+node[i].name+'" uid="'+node[i].id+'" type="'+node[i].type+'"><i class="'+node[i].icon+'"></i> '+node[i].name+'</a>';
                    else
                        html +='<a desc="'+childid+'_'+i+'" class="details" name="'+node[i].name+'" uid="'+node[i].id+'" type="'+node[i].type+'"><i class="tree-folder-open"></i> '+node[i].name+'</a>';
                    html += '</div>';
                    html +=	addNodes(node[i].items,'c_'+childid+'_'+i);
                } else {
                    html += '<div class="treechild" id="'+childid+'_'+i+'" status="unmarked" style="cursor:pointer; text-decoration:none;"><a class="product">&nbsp;&nbsp;&nbsp;</a>';
                    if (typeof node[i].icon != 'undefined' && node[i].icon !== null)
                        html +='<a desc="'+childid+'_'+i+'" class="details" name="'+node[i].name+'" uid="'+node[i].id+'" type="'+node[i].type+'"><i class="'+node[i].icon+'"></i> '+node[i].name+'</a>';
                    else
                        html +='<a desc="'+childid+'_'+i+'" class="details" name="'+node[i].name+'" uid="'+node[i].id+'" type="'+node[i].type+'"><i class="tree-folder-open"></i> '+node[i].name+'</a>';
                    //html +=	addNodes(node[i].items,'c_'+childid+'_'+i);
                    html += '</div>';
                }
				//html += '<a>'+node[i].name+'</a>';
                html += '</li>';
            }
            html += '</ul>';
            return html;
        }
})( jQuery );

(function( $ ) {

    var defaultPrefix = 'progrid', 
    defaultSettings = {
        id: null, 
        rows: [],
        width: '100%',
        fontSize: '11px',
        prefix: defaultPrefix,
        onValueChanged: null,
        onChangeDiscarded: null,
        onViewMode: null,
        onEditMode: null,
        onRowsInitialized: null,
        onRowSelected: null,
        onRowDeselected: null,
        onGridReady: null
    },
    instances = 0,
    createRow = function( settings, prefix ){
        if(!settings.name) {
            $.error('name setting is required!');
        }
        var defaultSettings = $.extend({
                label: '',
                type: '',
                readOnly: false,
                trueLabel: 'true',
                falseLabel: 'false',
                yesNoValueMode: 'boolean',
                validate: 'none'
            }, settings),
            row = {
                name: defaultSettings.name,
                label: defaultSettings.label,
                type: defaultSettings.type,
                readOnly: defaultSettings.readOnly
            },
            html = null,
            value = null,
            valueText = null,
            addSelectRowListener = function(flag) {
                if(typeof flag === 'undefined' || flag) {
                    $(html).find('td').on('selectRow', onSelectRow);
                    $(html).on("click", function(){
                        $(this).find("td:last").trigger("selectRow");
                    });
                } else {
                    $(html).find('td').off('selectRow', onSelectRow);
                    $(html).off("click");
                }
            },
            addDeselectRowListener = function(flag) {
                if(typeof flag === 'undefined' || flag) {
                    $(html).find('td').on('focusout', onDeselectRow);
                } else {
                    $(html).find('td').off('focusout', onDeselectRow);
                }
            },
            onSelectRow = function(){
                var row = $(this).parent(),
                    obj = row.data("rowObject");

                addSelectRowListener(false);

                if(typeof obj.onSelected === 'function'){
                    obj.onSelected.call(obj);
                }

                if(obj.readOnly) {
                    $(html).find('td:last div').focus();
                    addDeselectRowListener();
                    return;
                }
                obj.editMode();
                addDeselectRowListener();
            },
            onDeselectRow = function() {
                var row = $(this).parent(),
                    obj = row.data("rowObject");
                addDeselectRowListener(false);
                obj.editMode(false, false);
                addSelectRowListener();
                if(typeof obj.onRowDeselected === 'function') {
                    obj.onRowDeselected.call(obj);
                }
            },
            setViewModeEvents = function(){
                $(this.getHTML()).find("td:last div").on("focus", function(){
                    $(this.parentNode).trigger("selectRow");
                }).on('keydown', function(e){
                    e.stopPropagation();
                    if(e.keyCode === 13)
                        $(this.parentNode).trigger("selectRow");
                });
            },
            getCurrentSelectedValue = function(){
                var selectedValue = $(html).find('td:last > *').val();

                //we get the current selected value
                switch(this.type){
                    case 'text':
                        selectedValue = defaultSettings.validate === 'integer'?parseInt(selectedValue, 10):selectedValue;
                        break;
                    case 'yesNo':
                        switch(defaultSettings.yesNoValueMode){
                            case 'int':
                                selectedValue = parseInt(selectedValue, 10);
                                break;
                            case 'boolean':
                                selectedValue = selectedValue === "true"?true:false;
                        }
                        break;
                }

                return selectedValue;
            },
            discardChanges = function(){
                if(!this.readOnly) {
                    var valueDiscarded = getCurrentSelectedValue.call(this), 
                        currentValue = this.getValue();
                    this.editMode(false);
                    if(typeof this.onChangeDiscarded === 'function' && valueDiscarded !== currentValue) {
                        this.onChangeDiscarded.call(this, {
                            valueDiscarded: valueDiscarded,
                            currentValue: currentValue
                        });
                    }
                }
            };

        row.onValueChanged = null;

        row.onSelected = null;

        row.onEditMode = null;

        row.onViewMode = null;

        row.onChangeDiscarded = null;

        row.onRowDeselected = null;

        row.getValue = function() {
            return value;
        };

        row.updateValueText = function() {
            valueText = this.getValue();
            switch (this.type) {
                case 'text':
                    valueText = value.toString();
                    break;
                case 'yesNo':
                        valueText = value?defaultSettings.trueLabel:defaultSettings.falseLabel;
                    break;
                case 'select':
                    if(!defaultSettings.options) {
                        valueText = "";
                        break;
                    }
                    for( i = 0; i < defaultSettings.options.length; i++) {
                        if (defaultSettings.options[i].value === valueText) {
                            valueText = defaultSettings.options[i].label;
                            break;
                        }
                    }
                    break;
            }
        };

        row.getValueText = function() {
            return valueText;
        };

        row.getHTML = function() {
            if(!html) {
                var value = this.getValueText(), i, that = this;
                html = document.createElement('tr');
                
                $(html).append('<td class="'+prefix+'-first-col"><div style="width:0px">'+this.label+'</div></td>'+
                    '<td class="'+prefix+'-second-col"><div tabindex="0" style="width:0px">'+(value?value:'')+'</div></td>');
                $(html).data("rowObject", this);
                addSelectRowListener(true);//$(html).find('td').on('selectRow', onSelectRow);
                //$(html).on("focusout", onDeselectRow);
                setViewModeEvents.call(this);
            }

            return html;
        };

        row.save = function(){
            if(defaultSettings.readOnly) {
                return;
            }
            var previousValue = this.getValue(),
                data,
                newValue = getCurrentSelectedValue.call(this);
            if(defaultSettings.validate === 'integer' && this.type === 'text') {
                previousValue = parseInt(previousValue, 10);
            }

            if(newValue !== previousValue){
                value = newValue;
                this.updateValueText();
                if(typeof this.onValueChanged === 'function') {
                    this.onValueChanged({
                        row: this,
                        newValue: newValue,
                        previousValue: previousValue
                    });   
                }
            }

            this.editMode(false);
        };

        row.editMode = function(edit, focus){
            var element, val, that = this,
                width = $(html.parentNode).width(),
                fontSize = parseInt($(html).css('font-size'),10),
                availableWidth = (width/2)-(fontSize*1.82);

            if(typeof edit === 'undefined' || edit){
                switch(this.type) {
                    case 'text':
                        element = $('<input>').addClass('input-small').attr({type:"text"}).val(this.getValueText()).css({
                            "width": availableWidth
                        });
                        if(defaultSettings.validate === 'integer'){
                            element.on('keydown', function(e){
                                e.stopPropagation();
                                if ( e.keyCode == 46 || e.keyCode == 8 || e.keyCode == 9 || e.keyCode == 27 || e.keyCode == 13 || 
                                    (e.keyCode == 65 && e.ctrlKey === true) || 
                                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                                         return;
                                }
                                else {
                                    if (e.shiftKey || (e.keyCode < 48 || e.keyCode > 57) && (e.keyCode < 96 || e.keyCode > 105 )) {
                                        e.preventDefault(); 
                                    }   
                                }
                            });
                        }
                        break;
                    case 'select':
                        element = $('<select>').css({
                            width: "100%"
                        });
                        for(i=0; i<this.options.length; i++)
                        {
                            name = this.options[i].label;
                            element.append($('<option label="'+name+'" value="'+this.options[i].value+'" '+((this.options[i].value === this.getValue())?'selected="selected"':'')+' '+(this.options[i].disabled?'disabled':'')+' >'+name+'</option>'));
                        }
                        break;
                    case 'yesNo':
                        if(typeof value === 'string'){
                            val = value;
                            val = val === 'true'?true:false;
                        } else {
                            val = !!value;
                        }
                        element = $('<select>').css({
                            width: "100%"
                        });
                        element.append($('<option label="'+defaultSettings.trueLabel+'" value="'+(defaultSettings.yesNoValueMode === 'int'?1:'true')+'" '+(val?'selected="selected"':'')+'>'+defaultSettings.trueLabel+'</option>'));
                        element.append($('<option label="'+defaultSettings.falseLabel+'" value="'+(defaultSettings.yesNoValueMode === 'int'?0:'false')+'" '+(!val?'selected="selected"':'')+'>'+defaultSettings.falseLabel+'</option>'));
                }
                element.on('focusout', function(){
                    that.save();
                }).on("click", function(e){
                    e.stopPropagation();
                }).on("keydown", function(e){
                    e.stopPropagation();
                    if(e.keyCode === 13)
                    {
                        that.save();
                    }else if(e.keyCode === 27){
                        discardChanges.call(that);
                    }
                });
                try{
                    $(html).find('td:last').empty();
                }catch(e){};
                $(html).find('td:last').append(element);
                element.focus();
                if(typeof this.onEditMode === 'function') {
                    this.onEditMode.call(this);
                }
                element.select();
            } else {
                try{
                    $(html).find('td:last').empty();
                }catch(e){};
                $(html).find('td:last').append($('<div tabindex="0" style="width: '+availableWidth+'px">'+this.getValueText()+'</div>'));
                if(typeof focus === 'undefined' || focus) {
                    $(html).find('td:last div').focus();
                }
                setViewModeEvents.call(this);
                if(typeof this.onViewMode === 'function') {
                    this.onViewMode.call(this);
                }
            }

        };

        prefix = prefix || defaultPrefix;

        if(row.type === 'select') {
            row.options = settings.options || [];
        }
        switch (row.type) {
            case 'text':
                value = (typeof defaultSettings.value !== 'undefined' && defaultSettings.value !== null && (defaultSettings.validate !== 'int'?defaultSettings.value.toString():parseInt(defaultSettings.value,10))) || '';
                break;
            case 'select':
                if(!defaultSettings.options) {
                    value = null;
                    break;
                }
                for( i = 0; i < defaultSettings.options.length; i++) {
                    if ((typeof defaultSettings.options[i].selected === 'boolean' && defaultSettings.options[i].selected) ||
                        (typeof defaultSettings.options[i].selected !== 'boolean' && defaultSettings.options[i].selected === defaultSettings.options[i].value)
                    ) {
                        value = defaultSettings.options[i].value;
                        break;
                    }
                }
                if(value === null && defaultSettings.options[0]) {
                    value = (defaultSettings.options && defaultSettings.options[0].value) || null;
                }
                break;
            case 'yesNo':
                if(typeof defaultSettings.value === 'string'){
                    value = defaultSettings.value.toLowerCase();
                    value = value === 'true'?true:false;
                } else {
                    value = !!defaultSettings.value;
                }
                switch(defaultSettings.yesNoValueMode){
                    case 'int':
                        value = value?1:0;
                        break;
                    case 'literal':
                        value = value?'true':'false';
                }
        }

        row.updateValueText();
        return row;
    },
    setWidth = function(width){
        if(typeof width !== 'undefined'){
            var theWidth;
            var fontSize = parseInt($(this).find('table.progrid-table').css("font-size"));
            if(isNaN(fontSize)) {
                fontSize = 0;
            }
            theWidth = $(this).find('table').find('td div').css({
                "width": "0px"
            }).end().css({
                "width": width
            }).width();
            theWidth = (theWidth/2)-(1.82*fontSize);
            $(this).find('td div').css({"width": theWidth});
        }
    },
    appendRow = function (row) {
        var i, j, aux, flag = false,
            rows = this.find('tr'), name;
        for(i = 0; i < rows.length; i++){
            name = $(rows[i]).data('rowObject').label;
            for(j=0; j < name.length; j++) {
                if(row.label === "" || row.label.charAt(j) < name.charAt(j)) {
                    flag = true;
                }
                break;
            }
            if(flag) {
                break;
            }
        }
        if(rows[i]) {
            $(rows[i]).before(row.getHTML());
        } else {
            this.append(row.getHTML());
        }
    },
    buildTable = function(settings){
        var $this = $(this),
            table = $(document.createElement('table')),
            i, row, body;
        table
            .css({"width": settings.width, "font-size": settings.fontSize})
            .addClass('progrid-table')
            .attr({'id': settings.prefix+'-propTable-'+instances, cellpadding:0, cellspacing:0, border:0});
        table.append($('<thead>').append($('<tr>').append('<th>Name</th>').append('<th>Value</th>')));
        body = $('<tbody>');

        for(i = 0; i < settings.rows.length; i++){
            row = createRow(settings.rows[i]);
            row.onSelected = function(){
                $(this.getHTML()).addClass('row_selected');
                if(typeof settings.onRowSelected === 'function') {
                    settings.onRowSelected.call($this.get(0), {
                        id: settings.id,
                        fieldName: this.name, 
                        fieldLabel: this.label,
                        fieldType: this.type,
                        value: this.getValue()
                    });
                }
            };
            row.onViewMode = function(){
                if(typeof settings.onViewMode === 'function') {
                    settings.onViewMode.call($this.get(0), {
                        id: settings.id,
                        fieldName: this.name, 
                        fieldLabel: this.label,
                        fieldType: this.type,
                        value: this.getValue()
                    });
                }
            };
            row.onEditMode = function(){
                if(typeof settings.onEditMode === 'function') {
                    settings.onEditMode.call($this.get(0), {
                        id: settings.id,
                        fieldName: this.name, 
                        fieldLabel: this.label,
                        fieldType: this.type,
                        value: this.getValue()
                    });
                }
            };
            row.onRowDeselected = function(){
                $(this.getHTML()).removeClass('row_selected');
                if(typeof settings.onRowDeselected === 'function') {
                    settings.onRowDeselected.call($this.get(0), {
                        id: settings.id,
                        fieldName: this.name,
                        fieldLabel: this.label,
                        fieldType: this.type,
                        value: this.getValue()
                    });
                }
            };
            row.onChangeDiscarded = function(data){
                if(typeof settings.onChangeDiscarded === 'function') {
                    settings.onChangeDiscarded.call($this.get(0), {
                        id: settings.id,
                        fieldName: this.name,
                        fieldLabel: this.label,
                        fieldType: this.type,
                        currentValue: data.currentValue,
                        discardedValue: data.valueDiscarded
                    });
                }
            };
            row.onValueChanged = function(data) {
                if(typeof settings.onValueChanged === 'function') {
                    settings.onValueChanged.call($this.get(0), {
                        id: settings.id,
                        fieldName: this.name,
                        fieldLabel: this.label,
                        fieldType: this.type,
                        value: data.newValue,
                        previousValue: data.previousValue
                    });
                }
            };
            $(row.getHTML()).addClass(i%2===0?'odd':'even');
            appendRow.call(body, row);
        }

        table.append(body);
        if(typeof settings.onRowsInitialized === 'function') {
            settings.onRowsInitialized.call($this.get(0), {
                rows: settings.rows
            });
        }

        $this.append(table);
    },
    methods = {
        /**
         * method to initialize the grid.
         * @param  {object} settings        Object that contains the settings for the grid creation,
         *                                  this settings are:
         *                                  {
         *                                      id: []
         *                                  }
         *                                  id: an identifier for the element related to the properties on the grid
         *                                  width: the width for the grid, defaults to 'auto'
         *                                  rows: an objects array, each object have the settings for every field in the grid, 
         *                                         this settings may vary depend on the type of field, 
         *                                         however, there are 3 settings that are used in all types, they are:
         *                                         
         *                                         name: the name for the field, always required,
         *                                         label: the text show as label for the field
         *                                         type: the field type, it can be 'text', 'selection', 'yesNo',
         *                                         readOnly: a boolean that indicates if the field will be a read only item
         *
         *                                         the other settings are:
         *
         *                                          value: inicates the initial value for the field.
         *                                              it can be applied only in text and yesNo type fields.
         *                                          validate: it can contain the value 'integer' for admit just integer values, 
         *                                              it can applied only in text type field. default to 'none'
         *                                          options: an objects array, only for select type field, 
         *                                              each object specify the settings for every option in the select type field, 
         *                                              the structure for each object is:
         *                                                  label: the label to show in the option,
         *                                                  value: the value for the option,
         *                                                  selected: an boolean or string that indicates id the option is selected, 
         *                                                      if the value is a string then it is compared with the value field, 
         *                                                      if they are identical then the option is selected.
         *                                           trueLabel: only for yesNo type field, a string specifies the label for the true option
         *                                           falseLabel:  only for yesNo type field, a string that specifies the label for the false option
         *                                           yeaNoValueMode: only for yesNo type field, it can be 'boolean', 'int', 'literal'
         *                                                   'boolean' returns javascript boolean values (true, false)
         *                                                   'int' returns javascript integer values (0, 1)
         *                                                   'literal' returns javascript strings ('true', 'false')
         *                                                the value field for the select yesNo typoe can be boolean, int or string type, 
         *                                                it will converted internally to the right type 
         *                                   prefix: a string to be used in the class name for the elements on grid,
         *                                   onViewMode: callback to be executed when some field of the grid enters to view mode,
         *                                   onEditMode: callback to be executed when some field of the grid enters to edit mode,        
         *                                   onRowsInitialized:  callback to be executed when all fields of the grid are built,        
         *                                   onRowSelected:  callback to be executed when some field of the grid is selected,        
         *                                   onRowDeselected:  callback to be executed when some field of the grid is deselected,        
         *                                   onGridReady: callback to be executed when the grid is built and ready to use
         *                                          
         * @return {jQuery object}          jQuery object the plugin was invoked on
         */
        init : function( settings ) {
            settings = $.extend({}, defaultSettings, settings);
            return this.each(function(){
                instances++;
                $(this).empty();
                buildTable.call(this, settings);
                setWidth.call(this, settings.width);
                $(this).trigger('gridready');
                if(typeof settings.onGridReady === 'function') {
                    settings.onGridReady.call(this);
                }
            });
        },
        /**
         * jQuery function to set the grid width
         * @param {number|string} width the width value for the grid, it can be in css format (i.e. '300px', 'auto')
         */
        setWidth: function(width){
            setWidth.call(this, width);
        }
    };

    $.fn.progrid = function( method ) {
        if( methods[method] ) {
            return methods[method].apply(this, Array.prototype.slice.call( arguments, 1 ));
        } else if( typeof method === 'object' || !method ) {
            return methods.init.apply(this, arguments);
        } else {

        }
    };


})( jQuery );
var ErrorMessageItem = function (options) {
	Element.call(this, jQuery.extend(true, options , {
		/*width : 200,
		height : 20,*/
		position : "relative"
	}));
	this.message = null;
	this.messageId = null;
	this.messageContainer = null;
	this.parent = null;
	ErrorMessageItem.prototype.initObject.call(this, options);
};

ErrorMessageItem.prototype = new Element();

ErrorMessageItem.prototype.type = "ErrorMessageItem";

ErrorMessageItem.prototype.family = "Element";

ErrorMessageItem.prototype.initObject = function (options) {
	var defaults = {
		message : "[no message]",
		messageId : "",
		parent : null
	}
	jQuery.extend(true, defaults, options);
	this.setMessage(defaults.message);
	this.setMessageId(defaults.messageId);
	this.setParent(defaults.parent);
};

ErrorMessageItem.prototype.setParent = function (parent){
	this.parent = parent;
	return this;
};

ErrorMessageItem.prototype.getParent = function (parent){
	return this.parent;
};

ErrorMessageItem.prototype.setMessageId = function (messageId) {
	if ( !(typeof messageId === "string") ) {
		throw new Error("ErrorMessageItem.setMessageId(): not valid, should be a string value");
	}
	this.messageId  = messageId;
	return this;
};

ErrorMessageItem.prototype.getMessageId = function () {
	return this.messageId;
};

ErrorMessageItem.prototype.setMessage = function (message) {
	if ( !(typeof message === "string") ) {
		throw new Error("ErrorMessageItem.setMessage(): not valid, should be a string value");
	}
	this.message  = message;
	if (this.html){
		this.messageContainer.textContent = this.message; 
	}
	return this;
};

ErrorMessageItem.prototype.getMessage = function (){
	return this.message;
};

ErrorMessageItem.prototype.createHTML = function () {
	var messageContainer;
    if (!this.html) {
        this.html = this.createHTMLElement('li');
        this.html.id = this.id;
        this.style.applyStyle();
        this.style.addProperties({
            position: "relative",
            left: this.x,
            top: this.y,
            width: this.width,
            height: this.height,
            zIndex: this.zOrder
        });
        messageContainer = this.createHTMLElement('span');
		messageContainer.className = "messageContainer";
		this.html.appendChild(messageContainer);
		this.messageContainer = messageContainer;
		this.setMessage(this.message);
		this.html.style.height = "auto";
		this.html.style.width = "auto";
		this.html.className = "comment";
		this.html.style.padding = "3px 3px 3px 0px";

    }
    return this.html;
};
var ListContainer = function (options) {
	Container.call(this, options);
	ListContainer.prototype.initObject.call(this, options);
};

ListContainer.prototype = new Container();

ListContainer.prototype.type = 'ListContainer';

ListContainer.prototype.family = 'ListContainer';

ListContainer.prototype.initObject = function (options) {
};

ListContainer.prototype.setItems = function (items) {
	var i;
	this.clearItems();
	if (!(jQuery.isArray(items))) {
		throw new Error("ListContainer.setItems(): the value is invalid, should be a type array");
	}
	for ( i = 0 ; i < items.length ; i+=1 ) {
		this.addItem(items[i]);
	}
    return this;
};

ListContainer.prototype.addItem = function (item) {
	var newItem;
	if ( item instanceof ErrorMessageItem ) {
		newItem = item;
	} else if ( typeof  item === "object" ) {
		newItem = new ErrorMessageItem(item);
	} else {
		throw new Error ("ListContainer.addItem(): the value is invalid");
	}
	this.items.push(newItem);
	if ( this.html ) {
		this.messagecontainer.appendChild(newItem.getHTML());
	}
	return this;
};

ListContainer.prototype.clearItems = function () {
	var i, length = this.items.length;
	for ( i = 0 ; i < length ; i+=1 ) {
		this.removeItem(0)
	}	
	return this;
};

ListContainer.prototype.removeItem = function (index) {
	var item = this.items.splice(index,1)[0];  
	if ( item.html ) {
		jQuery(item.getHTML()).remove();
	}
	return this;
};


ListContainer.prototype.paintItems = function () {
	var i; 
	if ( this.messagecontainer ) {
		for ( i = 0 ; i < this.items.length ; i+=1 ) {
			this.body.appendChild(this.items[i].getHTML());
		}
	}
	return this;
};

ListContainer.prototype.createHTML = function () {
	if(!this.html){
		Container.prototype.createHTML.call(this);
		this.html.style.position = "relative"
	}
	return this.html;
};

ListContainer.prototype.getItems = function () {
	return this.items;
};

ListContainer.prototype.getItem = function (index) {
	if (index >= 0 && index < this.items.length ) {
		return this.items[index];
	} else {
		throw new Error("ListContainer.getItem():the index does not exist");
	}
};
var ErrorListItem = function (options) {
    ListContainer.call(this, options);
    this.messagecontainer = null ;
    this.iconContainer = null;
    this.titleContainer = null;
    this.errorType = null;
    this.errorId = null;
    this.title = null;
    this.onClick = null;
    this.parent = null;

	this.listOfTypes =  
		{	
			AdamGatewayEVENTBASED : "adam-tree-icon-gateway-exclusive",  
			AdamGatewayINCLUSIVE : "adam-tree-icon-gateway-exclusive", 
			AdamEventSTARTLeads : "adam-tree-icon-start-leads",
			AdamActivityUSERTASK : "adam-tree-icon-user-task",
			AdamEventSTARTOpportunities : "adam-tree-icon-start-opportunities",
			AdamEventSTARTDocuments : "adam-tree-icon-start-documents",
			AdamEventSTART : "adam-tree-icon-start",
			AdamGatewayEXCLUSIVE : "adam-tree-icon-gateway-exclusive",
			AdamGatewayPARALLEL : "adam-tree-icon-gateway-parallel",
			AdamEventINTERMEDIATETIMER  : "adam-tree-icon-intermediate-timer",
			AdamEventENDEMPTY  :"adam-tree-icon-end",
			AdamEventINTERMEDIATEMESSAGE  : "adam-tree-icon-intermediate-message",
			textannotation : "adam-tree-icon-textannotation ",
			AdamEventSTARTMESSAGE : "adam-tree-icon-start",
			AdamActivitySCRIPTTASK : "adam-tree-icon-user-task"
		};
    ErrorListItem.prototype.initObject.call(this, options);
};

ErrorListItem.prototype = new ListContainer();
ErrorListItem.prototype.type = 'ErrorListItem';

ErrorListItem.prototype.family = 'ErrorListItem';

ErrorListItem.prototype.initObject = function (options) {
	var defaults = {
		errorType : "",
		errorId : "",
		title : "[untitle]",
		onClick : null,
		parent : null
	};
	jQuery.extend(true, defaults, options);

	this.setErrorType(defaults.errorType);
	this.setErrorId(defaults.errorId);
	this.setTitle(defaults.title);
	this.setOnClick(defaults.onClick);
	this.setParent(defaults.parent);
};

ErrorListItem.prototype.setParent = function (parent) {
	this.parent = parent;
	return this;
};

ErrorListItem.prototype.getParent = function () {
	return this.parent;
};

ErrorListItem.prototype.setOnClick = function (handler) {
	if ( !(typeof handler === 'function' || handler === null) ) {
		throw new Error ("ErrorListItem.setInconHandler(): the value is invalid");
	}
	this.onClick = handler;
	return this;
};

ErrorListItem.prototype.attachListeners = function () {
    var that = this, item;
    jQuery(this.html).click(function(e){
    	if (typeof that.onClick === 'function' ) {
    		if ( that.parent ) {
				that.onClick(that.parent, that, that.errorType, that.errorId);	
    		} else {
	    		that.onClick(that, that.errorType, that.errorId);				
    		}
    			that.select();
    	}
    });
    return this;
};

ErrorListItem.prototype.setSelect = function (value) {
	if ( !(typeof value === "boolean") ) {
		throw new Error("ErrorListItem.select(): error in parameter");
	}
	this.selected = value;
	if ( this.html ) {
		if ( this.selected ) {
			this.select();
		} else {
			this.deselect();
		}
	}
	return this;
};

ErrorListItem.prototype.select = function () {
	if (this.html){
		if (this.parent){
			item = this.parent.getSelectedItem();
			if(item){
				item.deselect();
			}
			this.parent.setSelectedItem(this);
		}
		jQuery(this.getHTML()).css("background","#f3f8fe");		
	}
	return this;
};

ErrorListItem.prototype.deselect = function () {
	if (this.html){
		jQuery(this.getHTML()).css("background","inherit")	
	}
	return this;
};

ErrorListItem.prototype.setTitle = function (title) {
	if (!(typeof title === "string")) {
		throw new Error ("ErrorListItem.setTitle(): the value is invalid");					
	}
	this.title = title;
	if (this.html){
		this.titleContainer.textContent = this.title;
		this.resizeWidthTitle();
	}
	return this;	
};

ErrorListItem.prototype.resizeWidthTitle = function () {
	var auxWidth1, auxWidth2;
	if ( this.html ) {
		auxWidth1 = jQuery(this.titleContainer).outerWidth();
		this.titleContainer.style.width = "auto";
		auxWidth2 = jQuery(this.titleContainer).outerWidth();
		if ( auxWidth2 > auxWidth1 ) {
			this.titleContainer.title = this.title;
		} else {
			this.titleContainer.title = "";
		}
		this.titleContainer.style.width = "80%";
	}
	return this;
};

ErrorListItem.prototype.getTitle  = function () {
	return this.title;
};

ErrorListItem.prototype.setErrorId = function (id) {
	if (!(typeof id === "string")) {
		throw new Error ("ErrorListItem.addItem(): the value is invalid");					
	}
	this.errorId  = id;
	return this;
};

ErrorListItem.prototype.getErrorId = function () {
	return this.errorId;
};

ErrorListItem.prototype.createHTML = function () {
    var messagecontainer, iconContainer, titleContainer; 
    if (!this.html) {
	    ListContainer.prototype.createHTML.call(this);
	    messagecontainer = this.createHTMLElement('ul');
	    messagecontainer.className = "messagecontainer comments ";
	    messagecontainer.style.margin = "0 0 9px 25px";
	    iconContainer = this.createHTMLElement('i');
	    iconContainer.className = "iconContainer";
	    //iconContainer.textContent = "[x]"
	    titleContainer = this.createHTMLElement('span');
	    titleContainer.className = "titleContainer adam-error-color";
	    this.body.appendChild(iconContainer);
	    this.body.appendChild(titleContainer);
	    this.body.appendChild(messagecontainer);
	    this.messagecontainer = messagecontainer;
	    this.iconContainer = iconContainer;
	    this.titleContainer = titleContainer;
	    this.paintItems();
		this.setErrorType(this.errorType);
		this.setTitle(this.title);
		this.html.style.height = "auto";
		this.attachListeners();

		$(this.html).addClass('activitystream-posts-comments-container');
		this.html.style.padding = "8px";
		this.html.style.width = "auto";
		this.html.style.height = "auto";
		this.titleContainer.style.paddingLeft = "10px";
		this.fixedStyles();
    }
    return this.html;
};

ErrorListItem.prototype.fixedStyles = function () {
	if (this.html) {
		jQuery(this.titleContainer).css({
			"width": "80%",
			"text-overflow": "ellipsis",
			"white-space": "nowrap",
			"overflow": "hidden",
			"display": "inline-block",
			"cursor" : "pointer"
		});
	}
	return this;
}

ErrorListItem.prototype.paintItems = function () {
	var i; 
	if ( this.messagecontainer ) {
		for ( i = 0 ; i < this.items.length ; i+=1 ) {
			this.messagecontainer.appendChild(this.items[i].getHTML());
		}
	}
	return this;
};

ErrorListItem.prototype.setErrorType = function (errorType){
	if ( !(typeof errorType === "string") ) {
		throw new Error ("ErrorListItem.setErrorType(): not valid, should be a string value");
	}

	this.errorType = errorType;
	if ( this.html ) {
		jQuery(this.html).removeClass();			
		jQuery(this.html).addClass("error-"+errorType);
		this.iconContainer.className = this.listOfTypes[errorType];
	}
	return this;
};
ErrorListItem.prototype.addItem = function (item) {
	var newItem;
	if ( item instanceof ErrorMessageItem ) {
		newItem = item;
	} else if ( typeof  item === "object" ) {
		newItem = new ErrorMessageItem(item);
	} else {
		throw new Error ("ErrorListItem.addItem(): the value is invalid");
	}
	newItem.setParent(this);
	this.items.push(newItem);
	if ( this.html ) {
		this.messagecontainer.appendChild(newItem.getHTML());
	}
	return this;
};
ErrorListItem.prototype.getItemByMessageId = function (messageId){
	var i, item;
	for ( i = 0 ; i < this.items.length ; i+=1 ) {
		if (this.items[i].getMessageId() === messageId){
			item = this.items[i];
		}
	}
	if ( item ) {
		return item;					
	} else {
		null;
	}
};
var ErrorListPanel = function (options) {
    ListContainer.call(this, options);
    this.onClickItem = null;
    this.title = null;
    this.parent = null;
    this.titleContainer = null;
    this.selectedItem = null;
    this.classItemSelected = null;
    ErrorListPanel.prototype.initObject.call(this, options);
};

ErrorListPanel.prototype = new ListContainer();

ErrorListPanel.prototype.type = 'ErrorListPanel';

ErrorListPanel.prototype.family = 'ErrorListPanel';

ErrorListPanel.prototype.initObject = function (options) {
	var defaults = {
		onClickItem : null,
		title : "[Untitle]",
		parent : null,
		classItemSelected : "selected"
	}

	jQuery.extend(true, defaults , options);

	this.setOnClickItem(defaults.onClickItem);
	this.setTitle(defaults.title);
	this.setParent(defaults.parent);
	this.setClassItemSelected(defaults.classItemSelected);
};

ErrorListPanel.prototype.setClassItemSelected = function(className) {
	if ( !(typeof className === "string") ) {
		throw  new Error ("ErrorListPanel.setClassItemSelected:the value is invalid ");
	}
	this.classItemSelected = className;
	return this;
};
ErrorListPanel.prototype.getClassItemSelected = function() {
	return this.classItemSelected;
};

ErrorListPanel.prototype.setParent = function (parent) {
	this.parent = parent;
	return this;
};

ErrorListPanel.prototype.getParent = function () {
	return this.parent;
};

ErrorListPanel.prototype.setTitle = function (title) {
	if ( !(typeof title === "string") ) {
		throw  new Error ("ErrorListPanel.setTitle():the value is invalid ");
	}
	this.title = title;
	if ( this.html ) {
		this.titleContainer.textContent = title;
	}
	return this;
};

ErrorListPanel.prototype.getTitle = function () {
	return this.title;
};

ErrorListPanel.prototype.setOnClickItem = function (handler) {
	var i;
	if ( !(typeof handler === 'function' || handler === null) ) {
		throw new Error ("ErrorListPanel.setInconHandler(): the value is invalid");
	}
	this.onClickItem = handler;
	if (this.items.length){
		for ( i = 0 ; i < this.items.length ; i+=1 ) {
			this.items[i].onClick = this.onClickItem;
		}
	}
	return this;
};

ErrorListPanel.prototype.createHTML = function () {
    var titleContainer; 
    if (!this.html) {
	    ListContainer.prototype.createHTML.call(this);
	    titleContainer = this.createHTMLElement('h4');
	    titleContainer.className = "dashlet-title adam-error-color";
		this.html.appendChild(titleContainer);
	    this.titleContainer = titleContainer; 
	    jQuery(this.body).remove();
	    body = this.createHTMLElement('div');
	    body.className = 'j-container';
	    this.html.appendChild(body);
	    this.body = body;
	    this.setBodyHeight(this.bodyHeight);
	    this.paintItems();	    
	    this.setTitle(this.title);
	    this.customStyles();
    }
    return this.html;
};
ErrorListPanel.prototype.customStyles = function () {
	if (this.html){
		this.body.style.listStyle = "none";
		this.titleContainer.style.margin = "0px";
		this.titleContainer.style.padding = "6px 5px 6px 10px";
		this.titleContainer.style.fontWeight = 500;
		this.titleContainer.style.background = "#f6f6f6";
		this.titleContainer.style.borderBottom = "1px solid #ddd";
	    this.html.style.width = "auto";
	    this.html.style.background = "white";
	    this.html.style.height = "auto";
	    this.html.style.border = "1px solid #ddd";
	    jQuery(this.html).css("borderRadius", "3px");
	    jQuery(this.titleContainer).css("borderRadius", "3px 3px 0px 0px");
	}
	return this;
};

ErrorListPanel.prototype.paintItems = function () {
	var i; 
	if ( this.html ) {
		for ( i = 0 ; i < this.items.length ; i+=1 ) {
			this.body.appendChild(this.items[i].getHTML());
		}
	}
	return this;
};

ErrorListPanel.prototype.addItem = function (item) {
	var newItem;
	if ( item instanceof ErrorListItem ) {
		newItem = item;
	} else if ( typeof  item === "object" ) {
		newItem = new ErrorListItem(item);
	} else {
		throw new Error ("ErrorListPanel.addItem(): the value is invalid");
	}
	newItem.setParent(this);
	newItem.onClick  = this.onClickItem;
	this.items.push(newItem);
	if ( this.html ) {
		this.body.appendChild(newItem.getHTML());
	}
	return this;
};

ErrorListPanel.prototype.getContainerMessageById = function (id) {
	var item, i;
	for ( i = 0 ; i < this.items.length ; i+=1 ) {
		if (this.items[i].getErrorId() === id){
			item = this.items[i];
		}
	}
	if ( item ) {
		return item;					
	} else {
		null;
	}
};

ErrorListPanel.prototype.addNewMessage = function (containerId, message, messageId) {
	var item;
	item = this.getContainerMessageById(containerId);
	if (item){
		item.addItem({message:message, messageId: messageId});
	}
	return this;
};

ErrorListPanel.prototype.removeMessage = function (containerId, messageId) {
	var item, messageItem, index;
	item = this.getContainerMessageById(containerId);
	if ( item ) {
		messageItem = item.getItemByMessageId(messageId);
		if ( messageItem ) {
			index  = item.items.indexOf(messageItem);
			item.removeItem(index);
		}
	}
	return this;
};

ErrorListPanel.prototype.removeItemById = function (id) {
	var items = this.getItems(), i, index;

	if ( !(typeof id ===  "string") ) {
		throw new Error("ErrorListPanel.removeItemById(): the value is invalid");
	}

	for  ( i = 0 ; i < items.length; i+=1 ) {
		if (items[i].getErrorId() === id ) {
			index = i;
		}
	}
	if ( index !== undefined ) {
		item = this.getItem(index);
		this.removeItem(index);
		return item;
		return item;
	} else {
		return null;
	}
};

ErrorListPanel.prototype.appendTo = function (tagId) {
	var tag = tagId || "";
	if (jQuery(tag).length) {
		jQuery(tag).append(this.getHTML());	
	}
	return this;
};

ErrorListPanel.prototype.getItemById = function (id) {
	var items = this.getItems(), i, index;

	if ( !(typeof id ===  "string") ) {
		throw new Error("ErrorListPanel.removeItemById(): the value is invalid");
	}

	for  ( i = 0 ; i < items.length; i+=1 ) {
		if (items[i].getErrorId() === id ) {
			index = i;
		}
	}
	if ( index !== undefined ) {
		item = this.getItem(index);
		return item;
	} else {
		return null;
	}
};

ErrorListPanel.prototype.setSelectedItem = function (item) {
	if (item instanceof ErrorListItem) {
		this.selectedItem = item;
	}
	return this;
};

ErrorListPanel.prototype.getSelectedItem = function () {
	return this.selectedItem;
};

ErrorListPanel.prototype.getAllErros = function () {
	var count = 0;
	for ( i = 0 ; i < this.items.length ; i+=1 ) {
		count = count + this.items[i].getItems().length;
	}
	return count;
};

ErrorListPanel.prototype.resizeWidthTitleItems = function () {
	var i;
	if ( this.html ) {
		for ( i = 0 ; i < this.items.length ; i+=1 ) {
			this.getItem(i).resizeWidthTitle();
		}
	}
	return this;
};
//@ sourceURL=pmse.designer.js
