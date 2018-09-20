/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
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
    /**
     * ArrayList of markers added to this shape
     * @type {jCore.ArrayList}
     */
    this.markersArray = new jCore.ArrayList();
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
    }
    return this;
};

AdamShape.prototype.saveProject = function (root, App, w) {
    root.canvas.showModal();
    App.alert.show('upload', {level: 'process', title: 'LBL_LOADING', autoClose: false});
    root.canvas.project.save({
        success: function () {
            root.canvas.hideModal();
            w.show();
            w.html.style.display = 'none';
        }
    });
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
    layer = this.layers.find('id', this.id + 'Layer-error-layer');
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
        cls = 'adam-status-' + ((i * 25) + 50) + '-warning adam-error-color fa fa-exclamation-circle';
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

/**
 * Initiates the validation process of an AdamShape.
 * @param {Object} validationTools is a collection of utility functions for validating element data
 */
AdamShape.prototype.validate = function(validationTools) {
    var self = this;
    var url = App.api.buildURL(this.getBaseURL() + self.id, null, null);
    var options = {
        'bulk': 'get_element_settings'
    };
    var callback = self.getValidationFunction();
    if (url && callback) {
        validationTools.progressTracker.incrementTotalElements();
        App.api.call('read', url, null, {
            success: function(data) {
                callback(data, self, validationTools);
            },
            error: function(data) {
                validationTools.createWarning(element, 'LBL_PMSE_ERROR_UNABLE_TO_VALIDATE', self.getName());
            },
            complete: function(data) {
                validationTools.progressTracker.incrementSettingsGathered();
            }
        }, options);
    }
};
