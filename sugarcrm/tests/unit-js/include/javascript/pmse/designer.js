//FILE SUGARCRM flav=ent ONLY
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
describe('includes.javascript.pmse.designer', function() {

    var app;
    var oldApp;
    var oldProject;

    beforeEach(function() {
        app = SugarTest.app;

        oldProject = project;
        project = {};

        oldApp = App;
        App = app;

        // Figure out how to mock the designer being open to a basic process definition
    });

    afterEach(function() {
        sinon.collection.restore();

        app = null;

        project = oldProject;
        App = oldApp;
    });

    // Test the validate button function
    describe('Validate button', function() {

        var traverseProcess;

        beforeEach(function() {
            // Create a stub for the traverseProcess() function
            traverseProcess = sinon.collection.stub(window, 'traverseProcess');
        });

        it('should start the validator when clicked', function() {
            // Figure out how to activate the button click event here
            // expect(traverseProcess).toHaveBeenCalled();
        });

        it('should not start the validator if validation is already running', function() {
            // Figure out how to simulate validation being in progress here
            // Figure out how to activate the button click event here

            // expect(traverseProcess).not.toHaveBeenCalled();
        });
    });

    // Test the refreshMarkers function
    describe('refreshMarkers', function() {
        // Figure out how to simulate elements having errors

        it('should clear all markers from all elements', function() {
            // Figure out how to show that elements are cleared of error markers (stub the clearIssueMarkers function?)
        });
        it('should add an error marker to all elements with errors', function() {
            // Figure out how to show that at the end of the function, elements with errors have error markers
        });
        it('should add a warning marker to all elements with warnings', function() {
            // Figure out how to show that at the end of the function, elements with warnings have warning markers
        });
    });

    // Test the traverseProcess function
    describe('traverseProcess', function() {
    });

    // Test the getAllElements function
    describe('getAllElements', function() {
        // Test that the returned array matches the expected one
    });

    // Test the getStartEvents function
    describe('getStartEvents', function() {
        // Test that the returned array matches the expected one
    });

    // Test the setGatewayScope function
    describe('setGatewayScope', function() {
        // Test both elements being non-gateway
        // After function, destElement's gateway scope should match currElement's

        // Test currElement being a diverging gateway
        // After function, destElement's gateway scope should match currElement's
        // plus currElement's gateway type as a string at the front of the array

        // Test currElement being a converging gateway
        // After function, destElement's gateway scope should match currElement's
        // minus the first entry of the array
    });

    // Test the finalCleanup function
    describe('finalCleanup', function() {
        // Test that elements that are expected to be unreachable have the unreachable warning
        // Test after function that each element's hasBeenQueued is undefined
    });

    // Test the getValidationTools function
    describe('getValidationTools', function() {
        // Test that after function, each property of the returned object is correct
        // Test that the "silent" variable was passed to ValidationProgressTracker
    });

    // Test the ValidationProgressTracker object
    describe('ValidationProgressTracker', function() {

        beforeEach(function() {
            // Create a mock ValidationProgressTracker (mockTracker)
            // Create a mock result table
        });

        describe('incrementTotalElement', function() {
            // Test that the mockTracker.totalElements increases by 1
            // Test that mockTracker.showProgress was called
        });
        describe('incrementSettingsGathered', function() {
            // Test that mockTracker.numSettingsGathered increases by 1

            // Test that if mockTracker.numSettingsGathered === mockTracker.totalElements,
            // mockTracker.incrementTotalValidation is called
            // App.api.triggerBulkCall is called with the parameter ('validate_element_settings')
            // mockTracker.incrementValidated is called
        });
        describe('incrementTotalValidations', function() {
            // Test that mockTracker.totalValidations increases by 1
            // Test that mockTracker.showProgress is called
        });
        describe('incrementValidated', function() {
            // Test that mockTracker.numValidated increases by 1
            // Test that mockTracker.showProgress is called
        });
        describe('showProgress', function() {
            // Test that if validation is finished:
            // errorsFound should equal the size of the mock result table
        });
        describe('updateButtons', function() {

        });
    });
});
