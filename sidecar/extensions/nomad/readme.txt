
// Fetching metadata from the server
SUGAR.App.api.getMetadata(null, null, null, { success: function(data) { console.log(data); }});
SUGAR.App.api.getMetadata(null, SUGAR.App.config.metadataTypes, null, { success: function(data) { console.log(data); }});