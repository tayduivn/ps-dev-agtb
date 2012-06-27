/**
 * Cordova plugin, adds Document Viewer support
 *
 * **DocumentViewer provides:**
 *
 * - Method to open document for preview or in external program
 * - Method to get document storage directory (working directory)
 *
 * **DocumentViewer examples**
 *
 * // Open document
 *
 * var fileName = 'document.docx';
 * var openAsPreview = false;
 * var successCallback = function(){};
 * var failCallback = function(){};
 *
 * window.plugins.documentViewer.openDocument(fileName, openAsPreview, successCallback, failCallback);
 *
 * // Get working directory
 *
 * var gotDocPath = function(args) {
 *      if (args.error == 0){
 *          app.logger.debug('Working directory is: ' + args.directory);
 *      } else {
 *          app.logger.error("Error getting document directory");
 *      }
 * }
 * window.plugins.documentViewer.getWorkingDirectory(gotDocPath);
 *
 */


function DocumentViewer() {}

DocumentViewer.prototype.openDocument = function(fileName, previewMode, successCB, errorCB) {
    // Showing open document menu for external programs could fail with 'ERROR_PRESENTING_DOCUMENT_CONTROLLER' message
    // this usually means that no applications are associated with this file type

    var args = {};
    args.fileName = fileName;
    args.previewMode = previewMode;
    cordova.exec(successCB, errorCB, "DocumentViewer", "openDocument", [args]);
};

DocumentViewer.prototype.getWorkingDirectory = function (callback) {
    // callback function receives dictionary as argument
    // contains fields:
    // error            0 on success, 1 on error
    // directory        directory path on success, empty on error

    cordova.exec(callback, callback, "DocumentViewer", "getWorkingDirectory", {});
};

cordova.addConstructor(function()  {
    if(!window.plugins)
    {
        window.plugins = {};
    }
    // shim to work in 1.5 and 1.6
    if (!window.Cordova) {
        window.Cordova = cordova;
    }

    window.plugins.documentViewer = new DocumentViewer();
});


/* Example code snippet for file copying in Cordova
*  tested on version 1.7.0
*  copies file from App bundle to App documents directory
*  using DocumentViewer plugin to get full path to it


 //////     Debug code


var flName = "txt.txt";
var docPath = undefined;
var fileSys = undefined;

var gotDocPath = function(args) {
    if (args.error == 0){
        docPath = args.directory;
        app.logger.debug('Got working directory');
        window.requestFileSystem(LocalFileSystem.PERSISTENT, 0, gotFS, fail);
    } else {
        docPath = -1;
        app.logger.debug("Error getting working directory");
    }
}

var onFileCopied = function(dirEntry) {
    app.logger.debug("Success!");
}

var gotFileEntry = function (fe) {
    app.logger.debug("Got file entry");
    fileSys.root.getDirectory(
        docPath,
        {create: false, exclusive: false},
        function(parentEntry){
            app.logger.debug("Copying file to: " + parentEntry.fullPath);                                                        fe.copyTo(parentEntry, flName, onFileCopied, function(err){
                    if (err.code === 9){
                        app.logger.debug("File already exists at destination");
                        onFileCopied(parentEntry.fullPath);
                    } else {
                        fail(err);
                    }
                }
            );
        },
        fail);
}

var gotFS = function(fileSystem) {
    if (docPath === -1)
    {
        app.logger.debug("File copying aborted");
        return;
    }
    fileSys = fileSystem;
    var fullFName = docPath + '/../../Nomad.app/' + flName;
    app.logger.debug("Got filesystem, getting file: " + fullFName);
    fileSystem.root.getFile(fullFName, {create: true, exclusive: false}, gotFileEntry, fail);
}

var fail = function(error) {
    app.logger.error ("code: " + error.code + "\n object: " + JSON.stringify(error));
};

window.plugins.documentViewer.getWorkingDirectory(gotDocPath);

*/