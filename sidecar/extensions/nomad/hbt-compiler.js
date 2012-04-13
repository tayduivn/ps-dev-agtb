var src_path = "./extensions/nomad/views"; // directory of handlebars templates are stored with .hbt file extension
var compiled_path = "./extensions/nomad/compiled/"; // directory where the compiled .js files should be saved to

var fs = require('fs');
var handlebars = require('handlebars');
var watcher = require('watch-tree-maintained').watchTree(src_path, {'sample-rate': 500})

var start =
    "(function() {\n" +
        "var template = Handlebars.template, templates = Handlebars.templates = Handlebars.templates || {};\n" +
        "templates['{tpl-name}'] = template(";
var end = "\n)})();";

watcher.on('fileModified', function(path, stats) {
    fs.readFile(path, 'ascii', function(err, data) {
        if (err) throw err;

        if (path.length - path.lastIndexOf(".hbt") == 4) {

            console.log("Compiling " + path + "...");

            var parts = path.split("/");
            var pos = parts.length - 3;

            var field = null;
            var tplName = null;
            if (parts[pos] == "fields") {
                field = parts[pos + 1];
                tplName = "sugarField." + field + ".";
            }
            else if (parts[pos + 1] == "buttons") {
                field = parts[pos + 2].replace(".hbt", "") + "Button";
                tplName = "sugarField." + field + ".";
            }

            var name = parts.reverse()[0].replace(".hbt", "");
            tplName = (tplName || "") + name;

            var filename = name + (field == null ? "" : ("-" + field));
            var filepath = compiled_path + filename + ".js";

            try {
                var compiled = start.replace("{tpl-name}", tplName) +
                    handlebars.precompile(data) + end;

                fs.writeFile(filepath, compiled, function(err) {
                    if (err) throw err;
                    console.log('Saved ' + filepath);
                });
            }
            catch (e) {
                console.log('Failed to compile template "' + filename + '": ' + e);
            }
        }
    });
});