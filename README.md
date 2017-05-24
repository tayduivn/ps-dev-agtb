# SugarCRM Code Base

## Building Sugar

Building Sugar is easy with the use of `make`, you just need to define a few variables and then call `make`.

### Requirements
Please make sure that supported versions of [php](https://secure.php.net/), [composer](https://getcomposer.org/), and [Yarn](https://yarnpkg.com) are installed.

### Running Make

Alternatively, any of these variables can be set in your environment and `make` will pick them up, if you don't want to specify the required parameters manually each time.

```bash
SUGAR_VERSION=7.10.0.0 SUGAR_BUILD_DIR=/path/to/where/you/want/it/built/to make
```
```bash
=====> Composer Install <=====
Loading composer repositories with package information
Installing dependencies (including require-dev) from lock file
Nothing to install or update
Generating optimized autoload files
=====> Building Sidecar <=====
yarn install v0.24.5
[1/4] 🔍  Resolving packages...
success Already up-to-date.
✨  Done in 1.57s.
[10:20:47] Using gulpfile ~/Projects/Mango/sugarcrm/sidecar/gulpfile.js
[10:20:47] Starting 'jshint'...
[10:20:47] Starting 'build'...
[10:20:55] Finished 'build' after 7.35 s
[10:20:55] Finished 'jshint' after 8.08 s
[10:20:55] Starting 'default'...
[10:20:55] Finished 'default' after 19 μs
=====> Running Rome <=====
Building /Users/jwhitcraft/Projects/Mango/sugarcrmDONE

Importing Languages

TOTAL TIME: 29.913743972778
=====> Installing Sidecar Modules <=====
yarn install v0.24.5
[1/4] 🔍  Resolving packages...
[2/4] 🚚  Fetching packages...
warning store@1.3.20: The engine "browser" appears to be invalid.
[3/4] 🔗  Linking dependencies...
[4/4] 📃  Building fresh packages...
✨  Done in 4.89s.
=====> Build Clean Up <=====
=====> File md5 Hash <=====
```

### Supported Variables
- SUGAR_VERSION: The version you are building **Required**
- SUGAR_BUILD_DIR: Location to where you want Sugar to build **Required**
- SUGAR_FLAVOR: Specify the flavor you want to build -- valid options are `pro`, `ent` and `ult`; defaults to `ent`
- SUGAR_BUILD_NUMBER: Specify a build number for the app; only used on CI
- SUGAR_ENV: environment for which you are building -- supports `dev` and `production` and defaults to `dev`.
- SUGAR_PACKAGE_NAME: Used when creating the packages, defaults to `Sugar<Flavor>-<Version>`
- SUGAR_PACKAGE_FOLDER: Used to set the folder name inside of the package, defaults to `Sugar<Flavor>-Full-<Version>`

### Other Tasks
here are a few other tasks that can be called, as well:

- `clean` will clean the previous deploy instance
- `build` will build just Sugar and nothing else
- `package` will take a built version and create the zip files
- `build_release` will build Sugar and upgrade support files

### Translations
Translations will be auto detected and added when building if the translations folder is inside of this folder.  If you want to enable the SCM interactions of translations you can just set `SUGAR_TRANSLATIONS_SCM` variable to `true` and it will enable the SCM interactions when building translations.

### Docker image for building

Start by creating the docker image itself:
```
docker build -t sugarmake -f Dockerfile.build .
```

Now you can run the build process using the image you just created. Upon executing, in the example below, your Sugar build can be found at /host/build/destination

#### Examples
```
docker run -it --rm \
    -e SUGAR_VERSION=7.10.0.0 \
    -v /path/to/mango:/sugar \
    -v /host/build/destination:/build \
    sugarmake

docker run -it --rm \
    -e SUGAR_VERSION=7.10.0.0 \
    -v /path/to/mango:/sugar \
    -v /host/build/destination:/build \
    sugarmake clean
```
