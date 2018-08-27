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
/*global translate, $, document, window, SUGAR_FLAVOR, AdamProject, AdamCanvas, AdamEvent,
 AdamGateway, AdamActivity, AdamArtifact, AdamFlow, getAutoIncrementName, jCore,
 location, SUGAR_URL, adamUID, RestClient, SUGAR_REST, parseInt, SUGAR_AJAX_URL,
 PROJECT_LOCKED_VARIABLES, Tree, PROJECT_LOCKED_VARIABLES
 */
var project,
    canvas,
    PROJECT_MODULE = 'Leads',
    items,
    myLayout,
    adamUID,
    PROJECT_LOCKED_VARIABLES = [],
    PMSE_DECIMAL_SEPARATOR = '.',
    PMSE_DESIGNER_FORM_TRANSLATIONS = {
        ERROR_INVALID_EMAIL: translate('LBL_PMSE_ADAM_UI_ERROR_INVALID_EMAIL'),
        ERROR_INVALID_INTEGER: translate('LBL_PMSE_ADAM_UI_ERROR_INVALID_INTEGER'),
        ERROR_REQUIRED_FIELD: translate('LBL_PMSE_ADAM_UI_ERROR_REQUIRED_FIELD'),
        ERROR_COMPARISON: translate('LBL_PMSE_ADAM_UI_ERROR_COMPARISON'),
        ERROR_REGEXP: translate('LBL_PMSE_ADAM_UI_ERROR_REGEXP'),
        ERROR_TEXT_LENGTH: translate('LBL_PMSE_ADAM_UI_ERROR_TEXT_LENGTH'),
        ERROR_CHECKBOX_VALUES: translate('LBL_PMSE_ADAM_UI_ERROR_CHECKBOX_VALUES'),
        ERROR_TEXT: translate('LBL_PMSE_ADAM_UI_ERROR_TEXT'),
        ERROR_DATE : translate('LBL_PMSE_ADAM_UI_ERROR_DATE '),
        ERROR_PHONE: translate('LBL_PMSE_ADAM_UI_ERROR_PHONE'),
        ERROR_FLOAT: translate('LBL_PMSE_ADAM_UI_ERROR_FLOAT'),
        ERROR_DECIMAL: translate('LBL_PMSE_ADAM_UI_ERROR_DECIMAL'),
        ERROR_URL: translate('LBL_PMSE_ADAM_UI_ERROR_URL'),

        TITLE_BUSINESS_RULE_EVALUATION: translate('LBL_PMSE_ADAM_UI_TITLE_BUSINESS_RULE_EVALUATION'),
        LBL_BUSINESS: translate('LBL_PMSE_ADAM_UI_LBL_BUSINESS'),
        LBL_OPERATOR: translate('LBL_PMSE_ADAM_UI_LBL_OPERATOR'),
        LBL_UNIT: translate('LBL_PMSE_ADAM_UI_LBL_UNIT'),
        LBL_RESPONSE: translate('LBL_PMSE_LABEL_RESPONSE'),
        LBL_LOGIC_OPERATORS: translate('LBL_PMSE_ADAM_UI_LBL_LOGIC_OPERATORS'),
        LBL_GROUP: translate('LBL_PMSE_ADAM_UI_LBL_GROUP'),
        LBL_OPERATION: translate('LBL_PMSE_ADAM_UI_LBL_OPERATION'),
        LBL_DIRECTION: translate('LBL_PMSE_ADAM_UI_LBL_DIRECTION'),
        LBL_MODULE: translate('LBL_PMSE_FORM_LABEL_MODULE'),
        LBL_FIELD: translate('LBL_PMSE_LABEL_FIELD'),
        LBL_VALUE: translate('LBL_PMSE_LABEL_VALUE'),
        LBL_TARGET_MODULE: translate('LBL_PMSE_FORM_OPTION_TARGET_MODULE'),
        LBL_VARIABLE: translate('LBL_PMSE_ADAM_UI_LBL_VARIABLE'),
        LBL_NUMBER: translate('LBL_PMSE_ADAM_UI_LBL_NUMBER'),
        TITLE_MODULE_FIELD_EVALUATION: translate('LBL_PMSE_ADAM_UI_TITLE_MODULE_FIELD_EVALUATION'),
        TITLE_FORM_RESPONSE_EVALUATION: translate('LBL_PMSE_ADAM_UI_TITLE_FORM_RESPONSE_EVALUATION'),
        TITLE_SUGAR_DATE: translate('LBL_PMSE_ADAM_UI_TITLE_SUGAR_DATE'),
        TITLE_FIXED_DATE: translate('LBL_PMSE_ADAM_UI_TITLE_FIXED_DATE'),
        TITLE_UNIT_TIME: translate('LBL_PMSE_ADAM_UI_TITLE_UNIT_TIME'),
        LBL_FORM: translate('LBL_PMSE_LABEL_FORM'),
        LBL_STATUS: translate('LBL_PMSE_LABEL_STATUS'),
        LBL_APPROVED: translate('LBL_PMSE_LABEL_APPROVED'),
        LBL_REJECTED: translate('LBL_PMSE_LABEL_REJECTED'),
        BUTTON_SUBMIT: translate('LBL_PMSE_BUTTON_ADD'),
        BUTTON_CANCEL: translate('LBL_PMSE_BUTTON_CANCEL')
    },
    listPanelError = new ErrorListPanel({
            id : 'panel-Errors',
            onClickItem : function (listPanel, listItem, type, messageId){
                var shape, shapeId, canvas;
                canvas = jCore.getActiveCanvas();
                shapeId = listItem.getErrorId();
                shape = canvas.customShapes.find('id', shapeId);
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
            }
        });

    var countErrors = document.getElementById("countErrors");

var getAutoIncrementName = function (type, targetElement) {
    var i, j, k = canvas.getCustomShapes().getSize(), element, exists, index = 1, auxMap = {
        AdamUserTask: translate('LBL_PMSE_ADAM_DESIGNER_TASK'),
        AdamScriptTask: translate('LBL_PMSE_ADAM_DESIGNER_ACTION'),
        AdamEventLead: translate('LBL_PMSE_ADAM_DESIGNER_LEAD_START_EVENT'),
        AdamEventOpportunity: translate('LBL_PMSE_ADAM_DESIGNER_OPPORTUNITY_START_EVENT'),
        AdamEventDocument: translate('LBL_PMSE_ADAM_DESIGNER_DOCUMENT_START_EVENT'),
        AdamEventOtherModule: translate('LBL_PMSE_ADAM_DESIGNER_OTHER_MODULE_EVENT'),
        AdamEventTimer: translate('LBL_PMSE_ADAM_DESIGNER_WAIT_EVENT'),
        AdamEventMessage: translate('LBL_PMSE_ADAM_DESIGNER_MESSAGE_EVENT'),
        AdamEventReceiveMessage: translate('LBL_PMSE_ADAM_DESIGNER_MESSAGE_EVENT'),
        AdamEventBoundary: translate('LBL_PMSE_ADAM_DESIGNER_BOUNDARY_EVENT'),
        AdamGatewayExclusive: translate('LBL_PMSE_ADAM_DESIGNER_EXCLUSIVE_GATEWAY'),
        AdamGatewayParallel: translate('LBL_PMSE_ADAM_DESIGNER_PARALLEL_GATEWAY'),
        AdamEventEnd: translate('LBL_PMSE_ADAM_DESIGNER_END_EVENT'),
        AdamTextAnnotation: translate('LBL_PMSE_ADAM_DESIGNER_TEXT_ANNOTATION')
    };

    for (i = 0; i < k; i += 1) {
        exists = false;
        for (j = 0; j < k; j += 1) {
            element =  canvas.getCustomShapes().get(j);
            if (element.getName() === auxMap[type] + " # " + (i + 1)) {
                exists = !(targetElement && targetElement === element);
                break;
            }
        }
        if (!exists) {
            break;
        }
    }

    return auxMap[type] + " # " + (i + 1);
};

function renderProject (prjCode) {
    var pmseCurrencies, currencies, sugarCurrencies, currentCurrency, i;

    // initialize the error sidebar
    listPanelError.title = App.lang.get('LBL_PMSE_BPMN_WARNING_PANEL_TITLE', 'pmse_Project');
    listPanelError.appendTo('#div-bpmn-error');

    adamUID = prjCode;

    //RESIZE OPTIONS
    if ($('#container').length) {
        $('#container').height($(window).height() - $('#container').offset().top - $('#footer').height() - 46);
    }
    $(window).resize(function () {
        if ($('#container').length) {
            $('#container').height($(window).height() - $('#content').offset().top - $('#footer').height() - 46);
        }

    });

    //LAYOUT
    myLayout = $('#container').layout({
        east: {
            size: 300,
            maxSize: 300,
            minSize: 200,
            /*childOptions: {
                center__paneSelector:   ".east-center",
                south__paneSelector:    ".east-south",
                south__size: '50%'
            },*/
            initClosed: false,
            onresize: function () {
                listPanelError.resizeWidthTitleItems();
            }
        },
        north: {
            size: 44,
            spacing_open: 0,
            closable: false,
            slidable: false,
            resizable: false
        },
        north__showOverflowOnHover: true,
        south: {
            size: 200,
            maxSize: 200,
            minSize: 100,
            initHidden: true
        }
    });
    $('#container').css('zIndex', 1);

    $('.ui-layout-north').css('overflow', 'hidden');

    pmseCurrencies = [];
    currencies = SUGAR.App.metadata.getCurrencies();
    for (currID in currencies) {
        if (currencies.hasOwnProperty(currID)) {
            if (currencies[currID].status === 'Active') {
                pmseCurrencies.push({
                    id: currID,
                    iso: currencies[currID].iso4217,
                    name: currencies[currID].name,
                    rate: parseFloat(currencies[currID].conversion_rate),
                    preferred: currID === SUGAR.App.user.getCurrency().currency_id,
                    symbol: currencies[currID].symbol
                });
            }
        }
    }
    project = new AdamProject({
        metadata: [
            {
                name: "teamsDataSource",
                data: {
                    url: "pmse_Project/CrmData/teams/public",
                    root: "result"
                }
            },
            {
                name: "datePickerFormat",
                data: SUGAR.App.date.toDatepickerFormat(SUGAR.App.user.attributes.preferences.datepref)
            },
            {
                name: "fieldsDataSource",
                data: {
                    url: "pmse_Project/CrmData/allRelated/{MODULE}",
                    root: "result"
                }
            },
            {
                name: "targetModuleFieldsDataSource",
                data: {
                    url: "pmse_Project/CrmData/fields/{MODULE}",
                    root: "result"
                }
            },
            {
                name: "currencies",
                data: pmseCurrencies
            }
        ]
    });

    canvas = new AdamCanvas({
        name : 'Adam',
        id: "jcore_designer",
        container : "regular",
        readOnly : false,
        drop : {
            type : "container",
            selectors : ["#AdamEventDocument", "#AdamEventLead",
                "#AdamEventOpportunity", "#AdamEventTimer", "#AdamEventMessage", "#AdamEventEnd",
                "#AdamGatewayExclusive", "#AdamGatewayParallel", "#AdamUserTask", "#AdamScriptTask",
                "#AdamTextAnnotation", ".custom_shape", "#AdamEventReceiveMessage", "#AdamEventOtherModule" ]
        },
        copyAndPasteReferences: {
            AdamEvent: AdamEvent,
            AdamGateway: AdamGateway,
            AdamActivity: AdamActivity,
            AdamArtifact: AdamArtifact,
            AdamFlow: AdamFlow
        },
        toolbarFactory: function (id) {
            var customShape = null,
                name = getAutoIncrementName(id);
            switch (id) {
            case "AdamEventLead":
                customShape = new AdamEvent({
                    canvas : this,
                    width : 33,
                    height : 33,
                    style: {
                        cssClasses: [""]
                    },
                    evn_name: name,
                    evn_type: 'start',
                    evn_marker: 'MESSAGE',
                    evn_behavior: 'catch',
                    evn_message: 'Leads',
                    labels: [{
                        message: '',
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
                                'adam-shape-50-event-start',
                                'adam-shape-75-event-start',
                                'adam-shape-100-event-start',
                                'adam-shape-125-event-start',
                                'adam-shape-150-event-start'
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
                                'adam-marker-50-start-catch-leads',
                                'adam-marker-75-start-catch-leads',
                                'adam-marker-100-start-catch-leads',
                                'adam-marker-125-start-catch-leads',
                                'adam-marker-150-start-catch-leads'
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
                    drop : {type: 'connection'}
                });
                break;
            case "AdamEventOpportunity":
                customShape = new AdamEvent({
                    canvas : this,
                    width : 33,
                    height : 33,
                    style: {
                        cssClasses: [""]
                    },
                    evn_name: name,
                    evn_type: 'start',
                    evn_marker: 'MESSAGE',
                    evn_behavior: 'catch',
                    evn_message: 'Opportunities',
                    labels: [{
                        message: '',
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
                                'adam-shape-50-event-start',
                                'adam-shape-75-event-start',
                                'adam-shape-100-event-start',
                                'adam-shape-125-event-start',
                                'adam-shape-150-event-start'
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
                                'adam-marker-50-start-catch-opportunities',
                                'adam-marker-75-start-catch-opportunities',
                                'adam-marker-100-start-catch-opportunities',
                                'adam-marker-125-start-catch-opportunities',
                                'adam-marker-150-start-catch-opportunities'
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
                    drop : {type: 'connection'}
                });
                break;
            case "AdamEventDocument":
                customShape = new AdamEvent({
                    canvas : this,
                    width : 33,
                    height : 33,
                    style: {
                        cssClasses: [""]
                    },
                    evn_name: name,
                    evn_type: 'start',
                    evn_marker: 'MESSAGE',
                    evn_behavior: 'catch',
                    evn_message: 'Documents',
                    labels: [{
                        message: 'Document Start Event',
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
                                'adam-shape-50-event-start',
                                'adam-shape-75-event-start',
                                'adam-shape-100-event-start',
                                'adam-shape-125-event-start',
                                'adam-shape-150-event-start'
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
                                'adam-marker-50-start-catch-documents',
                                'adam-marker-75-start-catch-documents',
                                'adam-marker-100-start-catch-documents',
                                'adam-marker-125-start-catch-documents',
                                'adam-marker-150-start-catch-documents'
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
                    drop : {type: 'connection'}
                });
                break;
            case "AdamEventOtherModule":
                customShape = new AdamEvent({
                    canvas : this,
                    width : 33,
                    height : 33,
                    style: {
                        cssClasses: [""]
                    },
                    evn_name: name,
                    evn_type: 'start',
                    evn_marker: 'MESSAGE',
                    evn_behavior: 'catch',
                    evn_message: '',
                    labels: [{
                        message: 'Other Start Event',
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
                                'adam-shape-50-event-start',
                                'adam-shape-75-event-start',
                                'adam-shape-100-event-start',
                                'adam-shape-125-event-start',
                                'adam-shape-150-event-start'
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
                                'adam-marker-50-start-catch-message',
                                'adam-marker-75-start-catch-message',
                                'adam-marker-100-start-catch-message',
                                'adam-marker-125-start-catch-message',
                                'adam-marker-150-start-catch-message'
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
                    drop : {type: 'connection'}
                });
                break;
            case 'AdamEventTimer':
                customShape = new AdamEvent({
                    canvas : this,
                    width : 33,
                    height : 33,
                    style: {
                        cssClasses: [""]
                    },
                    evn_name: name,
                    evn_type: 'intermediate',
                    evn_marker: 'TIMER',
                    evn_behavior: 'catch',
                    evn_message: '',
                    labels: [{
                        message: '',
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
                                'adam-shape-50-event-intermediate',
                                'adam-shape-75-event-intermediate',
                                'adam-shape-100-event-intermediate',
                                'adam-shape-125-event-intermediate',
                                'adam-shape-150-event-intermediate'
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
                                'adam-marker-50-intermediate-catch-timer',
                                'adam-marker-75-intermediate-catch-timer',
                                'adam-marker-100-intermediate-catch-timer',
                                'adam-marker-125-intermediate-catch-timer',
                                'adam-marker-150-intermediate-catch-timer'
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
                    drop : {type: 'connection'}
                });
                break;
            case 'AdamEventMessage':
                customShape = new AdamEvent({
                    canvas : this,
                    width : 33,
                    height : 33,
                    style: {
                        cssClasses: [""]
                        //                              cssProperties : {
                        //                                  "border": "1px solid black"
                        //                              }
                    },
                    evn_name: name,
                    evn_type: 'intermediate',
                    evn_marker: 'MESSAGE',
                    evn_behavior: 'throw',
                    evn_message: '',
                    labels: [{
                        message: '',
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
                                'adam-shape-50-event-intermediate',
                                'adam-shape-75-event-intermediate',
                                'adam-shape-100-event-intermediate',
                                'adam-shape-125-event-intermediate',
                                'adam-shape-150-event-intermediate'
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
                                'adam-marker-50-intermediate-throw-message',
                                'adam-marker-75-intermediate-throw-message',
                                'adam-marker-100-intermediate-throw-message',
                                'adam-marker-125-intermediate-throw-message',
                                'adam-marker-150-intermediate-throw-message'
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
                    drop : {type: 'connection'}
                });
                break;
            case 'AdamEventReceiveMessage':
                customShape = new AdamEvent({
                    canvas : this,
                    width : 33,
                    height : 33,
                    style: {
                        cssClasses: [""]
                        //                              cssProperties : {
                        //                                  "border": "1px solid black"
                        //                              }
                    },
                    evn_name: name,
                    evn_type: 'intermediate',
                    evn_marker: 'MESSAGE',
                    evn_behavior: 'catch',
                    evn_message: '',
                    labels: [{
                        message: '',
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
                                'adam-shape-50-event-intermediate',
                                'adam-shape-75-event-intermediate',
                                'adam-shape-100-event-intermediate',
                                'adam-shape-125-event-intermediate',
                                'adam-shape-150-event-intermediate'
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
                                'adam-marker-50-intermediate-catch-message',
                                'adam-marker-75-intermediate-catch-message',
                                'adam-marker-100-intermediate-catch-message',
                                'adam-marker-125-intermediate-catch-message',
                                'adam-marker-150-intermediate-catch-message'
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
                    drop : {type: 'connection'}
                });
                break;
            case 'AdamUserTask':
                customShape = new AdamActivity({
                    canvas : this,
                    width: 100,
                    height: 50,
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
                            message : "",
                            //x : 10,
                            //y: 10,
                            width : 0,
                            height : 0,
                            orientation: 'horizontal',
                            position: {
                                location: 'center',
                                diffX : 0,
                                diffY : 0

                            },
                            updateParent : true
                        }
                    ],
                    markers: [
                        {
                            markerType: 'USERTASK',
                            x: 5,
                            y: 5,
                            markerZoomClasses: [
                                "adam-marker-50-usertask",
                                "adam-marker-75-usertask",
                                "adam-marker-100-usertask",
                                "adam-marker-125-usertask",
                                "adam-marker-150-usertask"
                            ]
                        }
                    ],
                    act_type: 'TASK',
                    act_task_type: 'USERTASK',
                    act_name: name,
                    minHeight: 50,
                    minWidth: 100,
                    maxHeight: 300,
                    maxWidth: 400
                });
                break;
            case 'AdamScriptTask':
                customShape = new AdamActivity({
                    canvas : this,
                    width: 35,
                    height: 35,
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
                                'adam-shape-50-activity-scripttask-none',
                                'adam-shape-75-activity-scripttask-none',
                                'adam-shape-100-activity-scripttask-none',
                                'adam-shape-125-activity-scripttask-none',
                                'adam-shape-150-activity-scripttask-none'
                            ]
                        }
                    ],
                    connectAtMiddlePoints: true,
                    drag: 'customshapedrag',
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
                    // drop : {
                    //     type: 'connectioncontainer',
                    //     selectors : ["#AdamEventBoundary", '.adam_boundary_event']
                    // },
                    drop : {type: 'connection'},
                    labels : [
                        {
                            message : "",
                            position: {
                                location: 'bottom',
                                diffX : 1,
                                diffY : 4
                            },
                            updateParent : false
                        }
                    ],
                    act_type: 'TASK',
                    act_task_type: 'SCRIPTTASK',
                    act_name: name, //name
                    act_script_type: 'NONE'
                });
                break;
            case 'AdamEventBoundary':
                customShape = new AdamEvent({
                    canvas : this,
                    width : 33,
                    height : 33,
                    style: {
                        cssClasses: [""]
                    },
                    evn_name: name,
                    evn_type: 'boundary',
                    evn_marker: 'TIMER',
                    evn_behavior: 'catch',
                    evn_message: '',
                    labels: [{
                        message: '',
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
                                'adam-shape-50-event-intermediate',
                                'adam-shape-75-event-intermediate',
                                'adam-shape-100-event-intermediate',
                                'adam-shape-125-event-intermediate',
                                'adam-shape-150-event-intermediate'
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
                                'adam-marker-50-intermediate-catch-timer',
                                'adam-marker-75-intermediate-catch-timer',
                                'adam-marker-100-intermediate-catch-timer',
                                'adam-marker-125-intermediate-catch-timer',
                                'adam-marker-150-intermediate-catch-timer'
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
                    drop : {type: 'connection'}
                });
                break;
            case 'AdamEventEnd':
                customShape = new AdamEvent({
                    canvas : this,
                    width : 33,
                    height : 33,
                    style: {
                        cssClasses: [""]
                        //                              cssProperties : {
                        //                                  "border": "1px solid black"
                        //                              }
                    },
                    evn_name: name,
                    evn_type: 'end',
                    evn_marker: 'EMPTY',
                    evn_behavior: 'throw',
                    evn_message: '',
                    labels: [{
                        message: '',
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
                                'adam-shape-50-event-end',
                                'adam-shape-75-event-end',
                                'adam-shape-100-event-end',
                                'adam-shape-125-event-end',
                                'adam-shape-150-event-end'
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
                                'adam-marker-50-end-throw-empty',
                                'adam-marker-75-end-throw-empty',
                                'adam-marker-100-end-throw-empty',
                                'adam-marker-125-end-throw-empty',
                                'adam-marker-150-end-throw-empty'
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
                    drop : {type: 'connection'}
                    //drop : {type: 'adamconnection'}
                });
                break;
            case 'AdamGatewayExclusive':
                customShape = new AdamGateway({
                    canvas : this,
                    width : 45,
                    height : 45,
                    gat_type: 'exclusive',
                    gat_direction: 'diverging',
                    gat_name: name,

                    style: {
                        cssClasses: [""]
                    },
                    labels : [
                        {
                            message : "",
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
                                'adam-shape-50-gateway-exclusive',
                                'adam-shape-75-gateway-exclusive',
                                'adam-shape-100-gateway-exclusive',
                                'adam-shape-125-gateway-exclusive',
                                'adam-shape-150-gateway-exclusive'
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
                    drop : {type: 'connection'}
                });
                break;
            case 'AdamGatewayParallel':
                customShape = new AdamGateway({
                    canvas : this,
                    width : 45,
                    height : 45,
                    gat_type: 'parallel',
                    gat_direction: 'diverging',
                    gat_name: name,
                    style: {
                        cssClasses: [""]
                    },
                    labels : [
                        {
                            message : "",
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
                                'adam-shape-50-gateway-parallel',
                                'adam-shape-75-gateway-parallel',
                                'adam-shape-100-gateway-parallel',
                                'adam-shape-125-gateway-parallel',
                                'adam-shape-150-gateway-parallel'
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
                    drop : {type: 'connection'}
                });
                break;
            case "AdamTextAnnotation":
                customShape = new AdamArtifact({
                    canvas : this,
                    width: 100,
                    height: 50,
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
                            message : "",
                            width : 0,
                            height : 0,
                            position: {
                                location : 'center',
                                diffX : 0,
                                diffY : 0
                            },
                            updateParent : true
                        }
                    ],
                    drop : {type: 'connection'},
                    art_type: 'TEXTANNOTATION',
                    art_name: name
                });
                break;
            }

            return customShape;
        }
    });
    canvas.attachListeners();

    jCore.setActiveCanvas(canvas);


    $("#adam_toolbar span[type=draggable]").draggable(
        {
            revert: "invalid",
            helper: function() {
                return $(this).clone().removeAttr('rel').css('zIndex', 5).show().appendTo('body');
            },
            cursor: "move"
        }
    );

    $('#ProjectTitle').hover(function (e) {
        $('.icon-edit-title').css('display', 'block');
    }, function (e) {
        $('.icon-edit-title').css('display', 'none');
    }).click(function (e) {
        e.preventDefault();
        $('#ProjectTitle').css('display', 'none');
        $('.icon-edit-title').css('display', 'block');
        $('#txt-title').css('display', 'block').focus().val($('#ProjectTitle').html());
    });

    var save_name = function() {
        $('#ProjectTitle').css('display', 'block');
        $('#txt-title').css('display', 'none');
        if ($('#ProjectTitle').html() != $('#txt-title').val()){
            $('#ProjectTitle').html(Handlebars.Utils.escapeExpression($('#txt-title').val()));
            url = App.api.buildURL('pmse_Project', null, {id: project.uid});
            attributes = {name: Handlebars.Utils.escapeExpression($('#txt-title').val())};
            App.alert.show('saving', {level: 'process', title: 'LBL_SAVING', autoclose: false});
            App.api.call('update', url, attributes, {
                success: function (data) {
                    App.alert.dismiss('saving');
                },
                error: function (err) {
                }
            });
        }
    };
    $('#txt-title').focusout(function (e) {
        if ($.trim($('#txt-title').val()) !== '') {
            save_name();
        }
    }).keypress(function(e) {
        if(e.which == 13) {
            if ($.trim(this.value) != '') {
                App.alert.dismiss('error-project-name');
                save_name();
            }
            else {
                App.alert.show('error-project-name', {
                    level: 'warning',
                    messages: translate('LBL_PMSE_PROJECT_NAME_EMPTY','pmse_Project'),
                    autoClose: false
                });
            }
        }
    });

    $('#ButtonUndo').click(function () {
        jCore.getActiveCanvas().commandStack.undo();
        jCore.getActiveCanvas().RemoveCurrentMenu();
    });

    $('#ButtonRedo').click(function () {
        jCore.getActiveCanvas().commandStack.redo();
        jCore.getActiveCanvas().RemoveCurrentMenu();
    });

    $('#ButtonSave').click(function () {
        project.save();
        jCore.getActiveCanvas().RemoveCurrentMenu();
    });

    /**
     * Button that when clicked triggers the process design validator
     */
    $('#ButtonValidate').click(function() {

        // Only start the validator if no validation is already running (no active AJAX requests)
        if (!jQuery.active) {

            // Clear the table of errors from any previous validation runs
            $('#Error-table').find('tr:gt(0)').remove();

            // Traverse the process
            traverseProcess();
        } else {

            // Inform the user that the attempt to start validation was not successful
            App.alert.show('validation_results', {
                level: 'error',
                title: translate('LBL_PMSE_VALIDATOR_WAIT_FOR_PROCESSES')
            });
        }
        jCore.getActiveCanvas().RemoveCurrentMenu();
    });

    //HANDLE ZOOM DROPDOWN
    $('#zoom').change(function (e) {
        var newZoomValue;
        newZoomValue = parseInt($(this).val());
        jCore.getActiveCanvas().applyZoom(newZoomValue);
        jCore.getActiveCanvas().bpmnValidation();
        $('.ui-layout-north').css('overflow', 'hidden');
    }).mouseenter(function() {
        $('.ui-layout-north').css('overflow', 'visible');
    });

    project.setUid(prjCode);
    project.setSaveInterval(20000);
    project.setCanvas(canvas);
    project.load(prjCode, {
        success: function() {
            $.extend(canvas, {'name': project.name});

            PROJECT_MODULE = project.process_definition.pro_module;
            project.init();

            if (App.config.autoValidateProcessesOnImport &&
                App.router.getPreviousFragment() === 'pmse_Project/layout/project-import') {
                traverseProcess();
            }
        }
    });
};

/**
 * Traverses the process to access each element in order
 * @return {Array} an array containing the errors found during traversal
 */
var traverseProcess = function() {
    var i;
    var j;
    var queue;
    var currElement;
    var destElement;
    var connectedElements;
    var validationTools = getValidationTools();

    // Initialize the arrays of elements placed on the canvas
    var allElements = getAllElements();
    var startEvents = getStartEvents();

    // If there are elements to validate, display the progress alert
    if (startEvents.length) {
        validationTools.progress.incrementTotal();
    }

    // For each start event element, traverse the path starting from that element
    for (i = 0; i < startEvents.length; i++) {

        // Initialize the queue with just the start node
        queue = [startEvents[i]];
        queue[0].hasBeenQueued = true;
        queue[0].currentGatewayScope = [];

        // While there are still elements left to traverse:
        while (queue.length) {

            // Remove the front element of the queue and validate it
            currElement = queue.shift();
            if (currElement.validate) {
                currElement.validate(validationTools);
            }

            // For each unvisited element that the current element connects to, add it to the queue
            connectedElements = currElement.getDestElements();
            for (j = 0; j < connectedElements.length; j++) {
                destElement = connectedElements[j];

                if (!destElement.hasBeenQueued) {

                    // Set the proper gateway scope of the destination element
                    setGatewayScope(currElement, destElement);

                    // Push the destination element onto the queue and mark it as queued
                    queue.push(destElement);
                    destElement.hasBeenQueued = true;
                }
            }
        }
    }
    // Perform final checks for unvisited elements
    finalCleanup(allElements);
    validationTools.progress.incrementValidated();
};

/**
 * Returns an array containing all user-placed elements on the canvas
 * @return {Array}
 */
var getAllElements = function() {
    return jCore.getActiveCanvas().children.asArray().filter(function(elem) {
        return elem.type !== 'MultipleSelectionContainer';
    });
};

/**
 * Returns an array containing all start events placed on the canvas
 * @return {Array}
 */
var getStartEvents = function() {
    return jCore.getActiveCanvas().children.asArray().filter(function(elem) {
        return elem.type === 'AdamEvent' && elem.getEventType() === 'START';
    });
};

/**
 * Updates the destination element's gateway scope depending on the current element
 * @param {Object} currElement is the current element being examined in the traversal
 * @param {Object} destElement is a destination element of the current element being examined in the traversal
 */
var setGatewayScope = function(currElement, destElement) {
    destElement.currentGatewayScope = currElement.currentGatewayScope.slice();
    if (currElement.getType() === 'AdamGateway') {
        if (currElement.getDirection() === 'DIVERGING') {
            destElement.currentGatewayScope.unshift(currElement.getGatewayType());
        } else if (currElement.getDirection() === 'CONVERGING') {
            destElement.currentGatewayScope.shift();
        }
    }
};

/**
 * Perform a final check for unvisited elements and return the elements to their original state
 * @param  {Array} allElements is an array containing all user-placed elements on the canvas
 */
var finalCleanup = function(allElements) {
    var i;
    for (i = 0; i < allElements.length; i++) {

        // Check if the element was never visited (unreachable)
        if (!allElements[i].hasBeenQueued && allElements[i].getType() !== 'AdamArtifact') {
            createError(allElements[i], 'LBL_PMSE_ERROR_ELEMENT_UNREACHABLE');
        }

        // Delete each element's hasBeenQueued attribute in case we want to run the traversal again
        delete allElements[i].hasBeenQueued;
    }
};

/**
 * @return {Object} a collection of utility functions used in element validation
 */
var getValidationTools = function() {
    return {
        'progress': new ProgressTracker(),
        'canvas': jCore.getActiveCanvas(),
        'validateNumberOfEdges': validateNumberOfEdges,
        'validateAtom': validateAtom,
        'createError': createError,
        'CriteriaEvaluator': CriteriaEvaluator,
        'LogicTracker': LogicTracker,
        'LogicAtom': LogicAtom,
        'getTargetModule': getTargetModule
    };
};

/*
 * Below are various utility functions that are used during a validation traversal of
 * the process definition.
 */

/**
 * Keeps track of the progress of a validation traversal of the canvas elements
 * and displays the status to the user as a percentage
 */
var ProgressTracker = function() {
    this.validated = 0,
    this.total = 0,

    /**
     * Increments the count of finished/validated items
     */
    this.incrementValidated = function() {
        var errorsFound;
        this.validated++;
        if (this.validated === this.total) {

            errorsFound = document.getElementById('Error-table').rows.length - 1;

            // We've reached the end of validation. Replace the 'in process' alert with
            // a closable one that reports the completion to the user including the number
            // of errors found
            App.alert.dismiss('validator_running');
            App.alert.show('validation_results', {
                level: 'success',
                title: translate('LBL_PMSE_VALIDATOR_COMPLETE') + errorsFound
            });
        } else {
            this.show();
        }
    },

    /**
     * Increments the total count of items that require validation
     */
    this.incrementTotal = function() {
        this.total++;
        this.show();
    },

    /**
     * Refreshes the alert that displays the progress to the user
     */
    this.show = function() {
        var progress = Math.ceil((this.validated / this.total) * 100);
        var newMessage = translate('LBL_PMSE_VALIDATOR_IN_PROGRESS') + ': ' + progress + '%';
        var errorsFound = document.getElementById('Error-table').rows.length - 1;
        App.alert.dismiss('validator_running');

        // Only display the south pane if there is an error
        if (errorsFound) {
            myLayout.show('south');
        }

        App.alert.show('validator_running', {
            level: 'process',
            title: newMessage,
            autoClose: false
        });
    };
};

/**
 * Validates that an element has a proper number of incoming and outgoing edges
 * @param  {integer} minIncoming is the minimum number of incoming edges allowed for the element
 * @param  {integer} maxIncoming is the maximum number of incoming edges allowed for the element
 * @param  {integer} minOutgoing is the minimum number of outgoing edges allowed for the element
 * @param  {integer} maxOutgoing is the maximum number of outgoing edges allowed for the element
 * @param  {Object} element is the element on the canvas that is currently being examined/validated
 */
var validateNumberOfEdges = function(minIncoming, maxIncoming, minOutgoing, maxOutgoing, element) {
    var incomingEdges = element.getPorts().asArray().filter(function(edge) {
        return edge.connection.srcPort.parent.id !== element.id;
    });
    var outgoingEdges = element.getDestElements();
    // Depending on element type, check proper number of incoming and outgoing edges
    if (minIncoming && incomingEdges.length < minIncoming) {
        createError(element, 'LBL_PMSE_ERROR_FLOW_INCOMING_MINIMUM', minIncoming);
    }
    if (maxIncoming && incomingEdges.length > maxIncoming) {
        createError(element, 'LBL_PMSE_ERROR_FLOW_INCOMING_MAXIMUM', maxIncoming);
    }
    if (minOutgoing && outgoingEdges.length < minOutgoing) {
        createError(element, 'LBL_PMSE_ERROR_FLOW_OUTGOING_MINIMUM', minOutgoing);
    }
    if (maxOutgoing && outgoingEdges.length > maxOutgoing) {
        createError(element, 'LBL_PMSE_ERROR_FLOW_OUTGOING_MAXIMUM', maxOutgoing);
    }
};

/**
 * Validates that the criterion is valid in the current instance of Sugar. The field names in the
 * criterionTypes variable are meant to match with criterion type IDs that occur in element settings
 * data. This way, we can simply pass in the information from a piece of criteria and this function
 * will validate it. Some fields in criterionTypes check the same thing; this is because some criteria
 * boxes contain criterion type IDs that differ from other criteria boxes' type IDs, but have the same
 * meaning. For each entry in criterionTypes, 'url' is the endpoint URL to search, 'key' is the unique
 * value to search for at that endpoint, and 'text' is the readable representation of type of criterion
 * we are validating.
 *
 * @param {string} type represents the type of criterion being validated
 * @param {string} module is the module ID of the piece of criterion
 * @param {string} field is the field ID of the piece of criterion
 * @param {string} value is the value of the piece of criterion
 * @param {Object} element is the element on the canvas that is currently being examined/validated
 * @param {Object} validationTools is a collection of utility functions for validating element data
 */
var validateAtom = function(type, module, field, value, element, validationTools) {
    var i;
    var searchInfo;
    var criterionTypes = {
        'MODULE': {
            // Validates a module field criterion
            url: App.api.buildURL('pmse_Project/CrmData/fields/' + module + '?base_module=' + getTargetModule()),
            key: field,
            text: 'Module field'
        },
        'VARIABLE': {
            // Validates a module field criterion
            url: App.api.buildURL('pmse_Project/CrmData/fields/' + module + '?base_module=' + getTargetModule()),
            key: value,
            text: 'Module field'
        },
        'recipient': {
            // Validates a module field criterion
            url: App.api.buildURL('pmse_Project/CrmData/fields/' + module + '?base_module=' + getTargetModule()),
            key: value,
            text: 'Module field'
        },
        'USER_IDENTITY': {
            // Validates a user criterion
            url: App.api.buildURL('pmse_Project/CrmData/users/'),
            key: value,
            text: 'User'
        },
        'USER_ROLE': {
            // Validates a role criterion
            url: App.api.buildURL('pmse_Project/CrmData/rolesList/'),
            key: value,
            text: 'Role'
        },
        'role': {
            // Validates a role criterion
            url: App.api.buildURL('pmse_Project/CrmData/rolesList/'),
            key: value,
            text: 'Role'
        },
        'RELATIONSHIP': {
            // Validates a module relationship criterion
            url: App.api.buildURL('pmse_Project/CrmData/related/' + getTargetModule()),
            key: value,
            text: 'Module relationship'
        },
        'user': {
            // Validates a module relationship criterion
            url: App.api.buildURL('pmse_Project/CrmData/related/' + getTargetModule()),
            key: module,
            text: 'Module relationship'
        },
        'TEAM': {
            // Validates a team criterion
            url: App.api.buildURL('pmse_Project/CrmData/teams/public/'),
            key: value,
            text: 'Team'
        },
        'team': {
            // Validates a team criterion
            url: App.api.buildURL('pmse_Project/CrmData/teams/public/'),
            key: value,
            text: 'Team'
        },
        'CONTROL': {
            // Validates a form response criterion
            url: App.api.buildURL('pmse_Project/CrmData/activities/' + project.uid),
            key: field,
            text: 'Form activity'
        },
        'ALL_BUSINESS_RULES': {
            // Validates a business rule criterion
            url: App.api.buildURL('pmse_Project/CrmData/rulesets/' + project.uid),
            key: value,
            text: 'Business rule'
        },
        'BUSINESS_RULES': {
            // Validates a business rule action criterion
            url: App.api.buildURL('pmse_Project/CrmData/businessrules/' + project.uid),
            key: field,
            text: 'Business rule action'
        },
        'TEMPLATE': {
            // Validates an email template criterion
            url: App.api.buildURL('pmse_Project/CrmData/emailtemplates/' + getTargetModule()),
            key: value,
            text: 'Email template'
        }
    };
    if (criterionTypes[type]) {
        searchInfo = criterionTypes[type];
    }
    if (searchInfo) {
        validationTools.progress.incrementTotal();
        App.api.call('read', searchInfo.url, null, {
            success: function(data) {
                for (i = 0; i < data.result.length; i++) {
                    if (data.result[i].value === searchInfo.key) {
                        return;
                    }
                }
                createError(element, 'LBL_PMSE_ERROR_DATA_NOT_FOUND', searchInfo.text);
            },
            complete: function() {
                validationTools.progress.incrementValidated();
            }
        });
    }
};

/**
 * Adds a new error to the error list table
 * @param {Object} element is the element on the canvas that is currently being examined/validated
 * @param {string} description contains the error text to be presented to the user about the error
 * @param {string} field is an optional value representing a specific field that the error refers to
 */
var createError = function(element, errorLabel, field) {

    // Get the information about the error
    var elementName = element.getName();
    var errorName = field ? (translate(errorLabel) + ': ' + field) : translate(errorLabel);
    var errorInfo = translate(errorLabel + '_INFO');

    // Get a reference to the error table
    var tableRef = document.getElementById('Error-table').getElementsByTagName('tbody')[0];

    // Find the correct spot to place the new row, based alphabetically by the element name
    var rowNumber;
    var otherElement;
    for (rowNumber = 0; rowNumber < tableRef.rows.length; rowNumber++) {
        otherElementName = tableRef.rows[rowNumber].cells[0].innerText;
        if (element.getName() < otherElementName) {
            break;
        }
    }

    // Insert a new row into the error table at the correct index
    var newRow = tableRef.insertRow(rowNumber);

    // Insert new cells into the new table row
    var nameCell = newRow.insertCell(0);
    var errorCell = newRow.insertCell(1);

    // Create the elements that will go into the cells
    var nameText = document.createElement('a');
    var errorText = document.createElement('a');

    // Set the text content and click handler of the name cell element
    nameText.textContent = elementName;
    nameText.onclick = function() {

        // When the user clicks the element name, select the element on the canvas and center the canvas view
        // on it
        canvas.emptyCurrentSelection();
        canvas.addToSelection(element);
        centerCanvasOnElement(element);
    };

    // Set the text content and tooltip of the error cell element
    errorText.textContent = errorName;
    errorText.setAttribute('rel', 'tooltip');
    errorText.setAttribute('data-placement', 'top');
    errorText.setAttribute('data-original-title', errorInfo);

    // Add the new elements to the cells
    nameCell.appendChild(nameText);
    errorCell.appendChild(errorText);
};

/**
 * Centers the canvas view on the given element
 * @param {Object} element is the element on the canvas that is currently being examined/validated
 */
var centerCanvasOnElement = function(element) {

    // Calculate the correct scroll positions for the horizontal and vertical scrollbars in the center pane
    var centerPane = myLayout.center.pane[0];
    var targetScrollLeft = element.zoomX - (centerPane.clientWidth / 2);
    var targetScrollTop = element.zoomY - (centerPane.clientHeight / 2);
    targetScrollLeft = targetScrollLeft < 0 ? 0 : targetScrollLeft;
    targetScrollTop = targetScrollTop < 0 ? 0 : targetScrollTop;

    // Move the horizontal and vertical scrollbars to the calculated positions
    centerPane.scrollLeft = targetScrollLeft;
    centerPane.scrollTop = targetScrollTop;
};

/**
 * CriteriaEvaluator provides a way to analyze gateway criteria box logic.
 * The addOr or addAnd methods accept gateway flo_criteria JSON objects,
 * and can be used to build larger statements across multiple criteria
 * boxes. Included are methods to determine whether the logical statement
 * is always true or always false.
 */
var CriteriaEvaluator = function() {
    this.criteria = [],

    // Some criteria boxes count empty criteria as true (i.e. start events). For others,
    // it is false (i.e. diverging gateways). The following property can be used to
    // adjust whether or not to count empty criteria as true for this CriteraEvaluator
    // object. By default, it is set to false.
    this.emptyCriteriaIsTrue = false;

    /**
     * Appends a logical statement onto the current one represented by this
     * CriteriaEvaluator as an OR
     * @param {Array} newCriteria is an array of criteria JSON elements parsed
     *                from a set of criteria box data
     */
    this.addOr = function(newCriteria) {
        var newOR;
        if (newCriteria.length) {
            if (this.criteria.length) {
                newOR = new Operand('LOGIC', undefined, undefined, undefined, 'OR');
                this.criteria.push(newOR);
            }
            this.criteria.push(this.changeCriteriaIntoEvaluableStructure(newCriteria));
        }
    },

    /**
     * Appends a logical statement onto the current one represented by this
     * CriteriaEvaluator as an AND
     * @param {Array} newCriteria is an array of criteria JSON elements parsed
     *                from a set of criteria box data
     */
    this.addAnd = function(newCriteria) {
        var newAND;
        if (newCriteria.length) {
            if (this.criteria.length) {
                newAND = new Operand('LOGIC', undefined, undefined, undefined, 'AND');
                this.criteria.push(newAND);
            }
            this.criteria.push(this.changeCriteriaIntoEvaluableStructure(newCriteria));
        }
    },

    /**
     * Determines if the logical statement represented by this CriteriaEvaluator is
     * a tautology (always true).
     * @return {boolean} true if there is no way for the statement to be false; false otherwise
     */
    this.isAlwaysTrue = function() {
        var i;
        var logicTracker;
        var possibilities;
        if (!this.emptyCriteriaIsTrue && !this.criteria.length) {
            return false;
        }
        this.negateExpression(this.criteria);
        possibilities = this.generatePossibilities(this.criteria.slice());
        for (i = 0; i < possibilities.length; i++) {
            logicTracker = new LogicTracker();
            logicTracker.add(possibilities[i]);
            if (logicTracker.evaluate()) {
                this.negateExpression(this.criteria);
                return false;
            }
        }
        this.negateExpression(this.criteria);
        return true;
    },

    /**
     * Determines if the logical statement represented by this CriteriaEvaluator is
     * a contradiction (always false).
     * @return {boolean} true if there is no way for the statement to be true; false otherwise
     */
    this.isAlwaysFalse = function() {
        var i;
        var logicTracker;
        var possibilities = this.generatePossibilities(this.criteria.slice());
        if (this.emptyCriteriaIsTrue && !this.criteria.length) {
            return false;
        }
        for (i = 0; i < possibilities.length; i++) {
            logicTracker = new LogicTracker();
            logicTracker.add(possibilities[i]);
            if (logicTracker.evaluate()) {
                return false;
            }
        }
        return true;
    },

    /**
     * Converts a JSON-parsed criteria array into an array of Operand objects and
     * simplifies the statement by getting rid of any '( )' groupings and 'NOT'
     * statements
     * @param  {Array} criteria is the JSON-parsed criteria array obtained from criteria box data
     * @return {Array} an array of Operand objects that represents a simplified version of the
     *                 original criteria array
     */
    this.changeCriteriaIntoEvaluableStructure = function(criteria) {
        // Convert the elements of the criteria into easy-to-work-with Operand objects
        this.convertToOperandObjects(criteria);
        // Convert all '( )' enclosed expressions in the criteria into nested arrays
        criteria = this.getRidOfParentheses(criteria);
        // Perform all negations in the expression in order to remove all 'NOT' Operands
        this.getRidOfNOTs(criteria);
        return criteria;
    },

    /**
     * Converts the array of criteria box data into an array of Operand objects
     * @param  {Array} criteria is the JSON-parsed criteria array obtained from criteria box data
     */
    this.convertToOperandObjects = function(criteria) {
        var i;
        for (i = 0; i < criteria.length; i++) {
            criteria[i] = new Operand(
                criteria[i].expType || undefined,
                criteria[i].expModule || undefined,
                criteria[i].expField || undefined,
                criteria[i].expOperator || undefined,
                criteria[i].expValue || undefined
            );
        }
    },

    /**
     * Adjusts an array of Operand objects to remove any '(' and ')' Operands groupings, and replace
     * the groupings with nested arrays of Operand objects that are easier to work with.
     * @param  {Array} criteria is an array of Operand objects representing a criteria box logical statement
     * @return {Array} newCriteria is a new array representing the original array after converting its
     *                 nested statements into nested arrays
     */
    this.getRidOfParentheses = function(criteria) {
        var newCriteria = [];
        while (criteria.length) {
            if (criteria[0].type === 'GROUP' && criteria[0].value === '(') {
                criteria.shift();
                newCriteria.push(this.getRidOfParentheses(criteria));
            } else if (criteria[0].type === 'GROUP' && criteria[0].value === ')') {
                criteria.shift();
                return newCriteria;
            } else {
                newCriteria.push(criteria.shift());
            }
        }
        return newCriteria;
    },

    /**
     * Performs any negation within an array of Operand objects, and removes the 'NOT' Operands.
     * @param  {Array} criteria is an array of Operand objects that has had any nested statements
     *                 converted to nested arrays via the getRidOfParentheses() method
     * @return {Array} the array after negation has been performed and all 'NOT' Operands have been removed.
     */
    this.getRidOfNOTs = function(criteria) {
        var i;
        // Recurse to the inner depths first
        for (i = 0; i < criteria.length; i++) {
            if (Array.isArray(criteria[i])) {
                criteria[i] = this.getRidOfNOTs(criteria[i]);
            }
        }

        // If we encounter a 'NOT', remove the not from the list, and invert the following expression
        for (i = 0; i < criteria.length; i++) {
            if (criteria[i].type === 'LOGIC' && criteria[i].value === 'NOT') {
                criteria.splice(i, 1);
                this.negateExpression(criteria[i]);
            }
        }
        return criteria;
    },

    /**
     * Returns an array of ALL combinations of values that could make the current criteria statement
     * true. Note that this does not mean each combination is possible or valid, as it does not take
     * into account contradictions among operators and values. Each subarray of the returned array
     * can be thought of as an 'OR' with the other subarrays. Within each of those subarrays, each
     * Operand can be thought of as an 'AND' with the other Operands.
     * @param  {Array} criteria is an array of criteria box data that has been converted via the
     *                 changeCriteriaIntoEvaluableStructure() method
     * @return {Array} an array of subarrays; each subarray is a collection of Operands that represents
     *                 a possible combination of 'AND's that could render the logical statement true
     */
    this.generatePossibilities = function(criteria) {
        var i;
        var j;
        var k;
        var l;
        var dataToReturn = [];
        var temp = [[]];
        var combinations;
        var possibility;

        // Start by going into the bottom of each parentheses group recursively
        for (i = 0; i < criteria.length; i++) {
            if (Array.isArray(criteria[i])) {
                criteria[i] = this.generatePossibilities(criteria[i]);
            }
        }
        // Iterate through the criteria, and add each possible combination of
        // criteria values as subarrays to the dataToReturn array
        for (i = 0; i < criteria.length; i++) {
            // If we reach an 'OR', start a new subarray
            if (criteria[i].type === 'LOGIC' && criteria[i].value === 'OR') {
                for (j = 0; j < temp.length; j++) {
                    dataToReturn.push(temp[j]);
                }
                // dataToReturn = dataToReturn.concat(temp);
                temp = [[]];
            } else if (Array.isArray(criteria[i])) {
                // If we encounter an array at this point, it has already been
                // evaluated to an array of subarrays. Combine all possible
                // combinations of the subarrays with the current temp array.
                combinations = [];
                for (j = 0; j < temp.length; j++) {
                    for (k = 0; k < criteria[i].length; k++) {
                        possibility = temp[j].concat(criteria[i][k]);
                        combinations.push(possibility);
                    }
                }
                temp = combinations;
            } else if (criteria[i].type !== 'LOGIC') {
                // If we encounter a single expression, add it to the temp array
                for (j = 0; j < temp.length; j++) {
                    temp[j].push(criteria[i]);
                }
            }
        }
        // Since we have finished iterating, check if temp has unpushed elements
        for (i = 0; i < temp.length; i++) {
            if (temp[i].length) {
                dataToReturn.push(temp[i]);
            }
        }
        return dataToReturn;
    },

    /**
     * Negates the given expression, either an Operand or array of Operands
     * @param  {Array} expression is an Operand object or array of Operand objects. This
     *                 array should not contain any '(', ')', or 'NOT' Operands.
     */
    this.negateExpression = function(expression) {
        var i;
        if (Array.isArray(expression)) {
            for (i = 0; i < expression.length; i++) {
                this.negateExpression(expression[i]);
            }
        } else {
            this.negateSingleExpression(expression);
        }
    },

    /**
     * Negates a single Operand object
     * @param  {Operand} expression is a single Operand object. It must not be a '(',
     *                   ')', or 'NOT' Operand.
     */
    this.negateSingleExpression = function(expression) {
        // Provides mappings for negations of logic values
        var invertLogic = {
            'equals': 'not_equals',
            'not_equals': 'equals',
            'starts_with': 'not_starts_with',
            'not_starts_with': 'starts_with',
            'ends_with': 'not_ends_with',
            'not_ends_with': 'ends_with',
            'contains': 'does_not_contain',
            'does_not_contain': 'contains',
            'AND': 'OR',
            'OR': 'AND'
        };
        if (expression.type === 'LOGIC') {
            expression.value = invertLogic[expression.value];
        } else {
            expression.operator = invertLogic[expression.operator];
        }
    };
};

/**
 * LogicTracker stores a collection of LogicAtoms which together hold the
 * information about an entire logical statement in a criteria box. Its
 * evaluate function will return true only if all LogicAtoms it contains
 * are valid
 */
var LogicTracker = function() {
    this.atoms = [],

    /**
     * Adds an array of operand objects to this LogicTracker
     * @param {Array} operands is an array of Operand objects. This array
     *                should contain only property Operands (no 'AND', 'OR',
     *                '(', ')', or 'NOT' Operands). Each Operand of this array
     *                is considered an 'AND' with each of the other Operands
     */
    this.add = function(operands) {
        var i;
        var k;
        var found = false;
        var newAtom;
        for (i = 0; i < operands.length; i++) {
            found = false;

            // Check if the property referenced by the Operand already
            // exists in the logic tracker, and update it if it does
            for (k = 0; k < this.atoms.length; k++) {
                if (operands[i].type === this.atoms[k].type) {
                    if (operands[i].module === this.atoms[k].module) {
                        if (operands[i].field === this.atoms[k].field) {
                            this.atoms[k].add(operands[i].operator, operands[i].value);
                            found = true;
                            break;
                        }
                    }
                }
            }

            // Otherwise, create a new entry in the logic tracker
            if (!found) {
                newAtom = new LogicAtom(operands[i].type, operands[i].module, operands[i].field);
                newAtom.add(operands[i].operator, operands[i].value);
                this.atoms.push(newAtom);
            }
        }
    },

    /**
     * Evaluates whether the group of LogicAtoms represented by this LogicTracker are all valid
     * @return {boolean} true if the logical statement represented by this LogicTracker is valid; false otherwise
     */
    this.evaluate = function() {
        var i;
        if (!this.atoms.length) {
            return false;
        }
        for (i = 0; i < this.atoms.length; i++) {
            if (!this.atoms[i].evaluate()) {
                return false;
            }
        }
        return true;
    };
};

/**
 * LogicAtom represents a single property referenced in a logical expression.
 * It holds information about all constraints placed on that property
 * throughout the entire expression. Its evaluate function allows us to
 * evaluate whether all the constraints placed on a property in a logical
 * expression are valid when combined together.
 * @param {string} type is the type of Operand that this LogicAtom represents
 * @param {string} module is the module of the property that this LogicAtom represents
 * @param {field} field is the field of the property that his LogicAtom represents
 */
var LogicAtom = function(type, module, field) {

    this.type = type,
    this.module = module,
    this.field = field,
    this.operators = {
        'equals': [],
        'not_equals': [],
        'starts_with': [],
        'not_starts_with': [],
        'ends_with': [],
        'not_ends_with': [],
        'contains': [],
        'does_not_contain': []
    },

    /**
     * Adds a constraint to the property that this LogicAtom represents
     * @param {string} operator is the operator that the constraint uses (see this.operators)
     * @param {string} value is the specific value of the constraint
     */
    this.add = function(operator, value) {
        this.operators[operator].push(value);
    },

    /**
     * Evaluates all constraints on this LogicAtom to check if they are valid
     * together. Examples of invalid LogicAtoms inlcude properties that are
     * required to equal two different values simultaneously, are required to
     * contain values that they are also required not to contain, etc.
     * @return {boolean} true if this LogicAtom is valid; false otherwise
     */
    this.evaluate = function() {
        var equals = this.operators.equals;
        var notEquals = this.operators.not_equals;
        var startsWith = this.operators.starts_with;
        var notStartsWith = this.operators.not_starts_with;
        var endsWith = this.operators.ends_with;
        var notEndsWith = this.operators.not_ends_with;
        var contains = this.operators.contains;
        var notContains = this.operators.does_not_contain;
        var result = true;

        // Check for any contradictions from 'is' constraints
        if (this.operators.equals.length) {
            if (this.type !== 'USER_ROLE') {
                // Exception for roles (users can have multiple roles)
                result = result && arrayContainsOneDistinctValue(equals);
            }
            result = result && arrayDoesNotContainValues(equals, notEquals);
            result = result && wordsStartWithPrefixes(equals, startsWith);
            result = result && wordsDoNotStartWithPrefixes(equals, notStartsWith);
            result = result && wordsEndWithSuffixes(equals, endsWith);
            result = result && wordsDoNotEndWithSuffixes(equals, notEndsWith);
            result = result && wordsContainSubstrings(equals, contains);
            result = result && wordsDoNotContainSubstrings(equals, notContains);
        }

        // Check for any contradictions from 'starts with' constraints
        if (this.operators.starts_with.length) {
            result = result && multiplePrefixesAreAllValid(startsWith);
            result = result && wordsDoNotStartWithPrefixes(startsWith, notStartsWith);
            result = result && wordsDoNotContainSubstrings(startsWith, notContains);
        }

        // Check for any contradictions from 'ends with' constraints
        if (this.operators.ends_with.length) {
            result = result && multipleSuffixesAreAllValid(endsWith);
            result = result && wordsDoNotEndWithSuffixes(endsWith, notEndsWith);
            result = result && wordsDoNotContainSubstrings(endsWith, notContains);
        }

        // Check for any contradictions from 'contains' constraints
        if (this.operators.contains.length) {
            result = result && wordsDoNotContainSubstrings(contains, notContains);
        }

        return result;
    },

    /**
     * Checks that there is only one distinct value inside the given array
     * @param  {Array} an array of string values
     * @return {boolean} true if there is only one distinct value in the array; false otherwise
     */
    arrayContainsOneDistinctValue = function(array) {
        return _.uniq(array).length < 2;
    },

    /**
     * Checks that the given array does not contain any of the given values
     * @param  {Array} values is an array of string values
     * @param  {Array} array is an array of string values
     * @return {boolean} false if any string in values is found in array; true otherwise
     */
    arrayDoesNotContainValues = function(values, array) {
        var i;
        for (i = 0; i < values.length; i++) {
            if (array.indexOf(values[i]) !== -1) {
                return false;
            }
        }
        return true;
    },

    /**
     * Checks whether all of the given words begin with all of the given prefixes
     * @param  {Array} words is an array of string values
     * @param  {Array} prefixes is an array of string values
     * @return {boolean} true if all strings in words begin with all strings in prefixes; false otherwise
     */
    wordsStartWithPrefixes = function(words, prefixes) {
        var i;
        var j;
        for (i = 0; i < words.length; i++) {
            for (j = 0; j < prefixes.length; j++) {
                if (words[i].indexOf(prefixes[j]) !== 0) {
                    return false;
                }
            }
        }
        return true;
    },

    /**
     * Checks whether none of the given words begin with any of the given prefixes
     * @param  {Array} words is an array of string values
     * @param  {Array} prefixes is an array of string values
     * @return {boolean} false if any string in words begins with any string in prefixes; true otherwise
     */
    wordsDoNotStartWithPrefixes = function(words, prefixes) {
        var i;
        var j;
        for (i = 0; i < words.length; i++) {
            for (j = 0; j < prefixes.length; j++) {
                if (words[i].indexOf(prefixes[j]) === 0) {
                    return false;
                }
            }
        }
        return true;
    },

    /**
     * Checks whether all of the given words end with all of the given suffixes
     * @param  {Array} words is an array of string values
     * @param  {Array} suffixes is an array of string values
     * @return {boolean} true if all strings in words end with all strings in suffixes; false otherwise
     */
    wordsEndWithSuffixes = function(words, suffixes) {
        var i;
        var j;
        for (i = 0; i < words.length; i++) {
            for (j = 0; j < suffixes.length; j++) {
                if (words[i].substring(words[i].length - suffixes[j].length) !== suffixes[j]) {
                    return false;
                }
            }
        }
        return true;
    },

    /**
     * Checks whether any of the given words end with any of the given suffixes
     * @param  {Array} words is an array of string values
     * @param  {Array} suffixes is an array of string values
     * @return {boolean} false if any string in words ends with any string in suffixes; true otherwise
     */
    wordsDoNotEndWithSuffixes = function(words, suffixes) {
        var i;
        var j;
        for (i = 0; i < words.length; i++) {
            for (j = 0; j < suffixes.length; j++) {
                if (words[i].substring(words[i].length - suffixes[j].length) === suffixes[j]) {
                    return false;
                }
            }
        }
        return true;
    },

    /**
     * Checks whether all of the given words contain all of the given substrings
     * @param  {Array} words is an array of string values
     * @param  {Array} substrings is an array of string values
     * @return {boolean} true if all strings in words contain all strings in substrings; false otherwise
     */
    wordsContainSubstrings = function(words, substrings) {
        var i;
        var j;
        for (i = 0; i < words.length; i++) {
            for (j = 0; j < substrings.length; j++) {
                if (words[i].indexOf(substrings[j]) === -1) {
                    return false;
                }
            }
        }
        return true;
    },

    /**
     * Checks whether any of the given words contain any of the given substrings
     * @param  {Array} words is an array of string values
     * @param  {Array} substrings is an array of string values
     * @return {boolean} false if any strings in words contain any strings in substrings; true otherwise
     */
    wordsDoNotContainSubstrings = function(words, substrings) {
        var i;
        var j;
        for (i = 0; i < words.length; i++) {
            for (j = 0; j < substrings.length; j++) {
                if (words[i].indexOf(substrings[j]) !== -1) {
                    return false;
                }
            }
        }
        return true;
    },

    /**
     * Checks whether all string values in the array could represent the beginning of the same word.
     * For example, ['app', 'appl', 'a', 'apple'] are all valid suffixes of the word 'apple'
     * @param  {Array} array is an array of string values
     * @return {boolean} false if any prefixes contradict each other; true otherwise
     */
    multiplePrefixesAreAllValid = function(array) {
        var currWord;
        var i;
        array.sort(function(a, b) {
            return a.length - b.length;
        });
        currWord = array[0];
        for (i = 1; i < array.length; i++) {
            if (!wordsStartWithPrefixes([array[i]], [currWord])) {
                return false;
            }
            currWord = array[i];
        }
        return true;
    },

    /**
     * Checks whether all string values in the array could represent the ending of the same word.
     * For example, ['e', 'ple', 'le', 'apple'] are all valid suffixes of the word 'apple'
     * @param  {Array} array is an array of string values
     * @return {boolean} false if any suffixes contradict each other; true otherwise
     */
    multipleSuffixesAreAllValid = function(array) {
        var currWord;
        var i;
        array.sort(function(a, b) {
            return a.length - b.length;
        });
        currWord = array[0];
        for (i = 1; i < array.length; i++) {
            if (!wordsEndWithSuffixes([array[i]], [currWord])) {
                return false;
            }
            currWord = array[i];
        }
        return true;
    };
};

/**
 * Operand represents a single criterion of a criteria box logical expression. It provides an easier type of object
 * to work with when evaluating the validity of a logical expression.
 * @param {string} typeID is the type of the criterion gathered from the criteria box JSON
 * @param {string} moduleID is the module of the criterion gathered from the criteria box JSON
 * @param {string} fieldID is the field of the criterion gathered from the criteria box JSON
 * @param {string} operatorID is the operator of the criterion gathered from the criteria box JSON
 * @param {string} value is the value of the criterion gathered from the criteria box JSON
 */
var Operand = function(typeID, moduleID, fieldID, operatorID, value) {
    this.type = typeID;
    this.module = moduleID;
    this.field = fieldID;
    this.operator = operatorID;
    this.value = value;
};

/**
 * Returns the target module of the current process definition being designed
 * @return {string} The name of the target module
 */
var getTargetModule = function() {
    return project.process_definition.pro_module;
};
