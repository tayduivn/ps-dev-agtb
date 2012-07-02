var nowatch = process.argv.indexOf("--nowatch") > -1; // Allow --nowatch flag to only do compilation
var src_path = "./extensions/nomad/templates"; // directory of handlebars templates are stored with .hbt file extension
var compiled_path = "./extensions/nomad/compiled/"; // directory where the compiled .js files should be saved to

var fs = require('fs');
var handlebars = require('handlebars');
if (!nowatch) {
    var watcher = require('watch-tree-maintained').watchTree(src_path, {'sample-rate': 500})
}
var fstools = require('fs-tools');

var precompile = function() {
    console.log("Pre-compiling...");
    fstools.walk(src_path, '\.hbt$',
        function(path, stats, callback) {
            compileTemplate(path, callback);
        },
        function(err) {
            if (err) {
                console.error(err);
                process.exit(1);
            }
        }
    );
};

fstools.walk(compiled_path, '\.js$',
    function(path, stats, callback) {
        console.log("Deleting: " + path);
        fs.unlink(path, function(err) {
            if (err) {
                callback(err);
            }
            else {
                callback();
            }
        });
    },
    function(err) {
        console.log("Done deleting");
        if (err) {
            console.error(err);
            process.exit(1);
        }
        precompile();
    }
);

if (watcher) {
    watcher.on('fileModified', function(path, stats) {
        compileTemplate(path);
    });
}

var start =
    "(function() {\n" +
        "var template = Handlebars.template, templates = Handlebars.templates = Handlebars.templates || {};\n" +
        "templates['{tpl-name}'] = template(";
var end = "\n)})();";

// Template compiler
var compileTemplate = function(path, callback) {
    fs.readFile(path, 'ascii', function(err, data) {
        if (err) {
            console.log(err);
            if (callback) callback(err);
            return;
        }

        if (path.length - path.lastIndexOf(".hbt") == 4) {

            console.log("Compiling " + path + "...");

            var parts = path.split("/");
            var pos = parts.length - 3;

            var field = null;
            var tplName = null;
            if (parts[pos] == "fields") {
                field = parts[pos + 1];
                tplName = "f." + field + ".";
            }
            else if (parts[pos + 1] == "buttons") {
                field = parts[pos + 2].replace(".hbt", "") + "Button";
                tplName = "f." + field + ".";
            }

            var name = parts.reverse()[0].replace(".hbt", "");
            tplName = (tplName || "") + name;

            var filename = name + (field == null ? "" : ("-" + field));
            var filepath = compiled_path + filename + ".js";

            try {
                var compiled = start.replace("{tpl-name}", tplName) +
                    handlebars.precompile(data,{data:true}) + end;

                fs.writeFile(filepath, compiled, function(err) {
                    if (err) throw err;
                    console.log('Saved ' + filepath);
                });
            }
            catch (e) {
                console.log('Failed to compile template "' + filename + '": ' + e);
                if (callback) callback(err);
                return;
            }

            if (callback) callback();
        }
    });

};
