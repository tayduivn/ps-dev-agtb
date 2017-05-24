<?php
// parse command-line options
$options = '';
$long_options = array(
    'path:',
);
$command_line_options = getopt($options, $long_options);

// determine locale path and create it
$path = $command_line_options['path'];

// if the file exists, remove it
if (file_exists($path . "/files.md5")) {
    unlink($path . "/files.md5");
}
// this list of files should not be included in the files.md5
$invalid_folders = [
    'tests',
    'sidecar/tests',
    'portal2/tests',
];
// actually do the work and loop over the path to find every file
$directory = new \RecursiveDirectoryIterator($path);
// filter to make sure we are not including any of the files inside of the folders
$filter = new \RecursiveCallbackFilterIterator(
    $directory,
    function ($current, $key, $iterator) use ($invalid_folders) {
        // Skip hidden files and directories.
        if ($current->getFilename()[0] === '.') {
            return false;
        }
        if ($current->isDir()) {
            // if the current is is an invalid path
            foreach ($invalid_folders as $path) {
                if (strstr($current->getPathname(), $path)) {
                    return false;
                }
            }
        }
        // Only consume files of interest.
        return true;
    }
);
// the interator
$iterator = new \RecursiveIteratorIterator($filter);
$files = [];
// loop over and create the files
foreach ($iterator as $info) {
    $file = str_replace($path . "/", '', $info->getPathname());
    $files["./$file"] = md5_file($info->getPathname());
}

// write out the file
date_default_timezone_set('UTC');
$buildTime = date("c");
file_put_contents(
    "${path}/files.md5",
    "<?php\n\n// created: " . $buildTime . "\n\$md5_string = " . var_export($files, true) . ";"
);
