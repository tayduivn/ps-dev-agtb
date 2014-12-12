global.dom = require('jsdom');
global.$ = null;
var Base;

global.dom.env({
  html: '<body></body>',
  scripts: ['../jquery-1.9.1.js',
        '../underscore-min.js',
        '../../../package/SugarModules/modules/pmse_Project/js/pmse.jcore.js',
        '../../../package/SugarModules/modules/pmse_Project/js/pmse.ui.js'],
  done: function(errors, _window) {
    if (errors) {
      console.log("errors:", errors);
    }
    global.window = _window;
    global.document = _window.document;
    global.$ = global.jQuery = window.$;
    global.jCore = window.jCore;
    Base = require('../../../designer/src/ui/base.js');
    /*global.jCore = jCore = require 

('../../../package/SugarModules/modules/pmse_Project/js/pmse.jcore.js'),
    Util = require('../../../designer/src/jcore/utils.js'),  
                Base = require

('../../../package/SugarModules/modules/pmse_Project/js/pmse.ui.js');*/

    return global.$;
  }
});
if (!$) {
  beforeEach(function() {
    return waitsFor(function() {
      return $;
    });
  });
}

beforeEach(function () {
    a = new Base();
    
  });
  
//**CODE**//