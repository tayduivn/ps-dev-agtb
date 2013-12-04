<?php

class Mobile {
    /**
     * @param Rome $rome
     * @param string $nomadRepoUri
     * @param string $repoBranch
     * @param string $ver version
     * @param array $flavs server editions we are building for
     * @param string $scheme build scheme for mobile build-web.sh
     * @param string|null $nomadCheckoutDir defaults to rome build dir
     */
    function __construct($rome, $nomadRepoUri, $repoBranch, $ver, $flavs, $scheme, $nomadCheckoutDir=null){
        $this->nomadRepoUri = $nomadRepoUri;
        $this->rome = $rome;
        $this->buildDir = $rome->getBuildDir();
        /**
         * nomad repository will be cloned from this directory
         */
        $this->nomadCheckoutDir = $nomadCheckoutDir ? $nomadCheckoutDir : $this->buildDir;
        /**
         * nomad sources directory
         */
        $this->nomadBuildDir = $this->nomadCheckoutDir . DIRECTORY_SEPARATOR . 'nomad';
        $this->version = $ver;
        $this->branch = $repoBranch;
        $this->scheme = $scheme;

        $prevDir = getcwd();

        if ($this->setFlavs($flavs) && $this->checkRequirements() && $this->initGitRepository() && $this->updateGitRepository()) {
            $this->buildNomad();
        }

        // return back to initial directory
        chdir($prevDir);
    }

    function setFlavs ($flavs) {

        $allowedFlavs = array("PRO", "CORP", "ENT", "ULT");
        $result = array();
        foreach ($flavs as $flav) {
            if (in_array(strtoupper($flav), $allowedFlavs)) {
                $result[] = $flav;
            }
        }
        $this->flavs = $result;

        return !empty($result);
    }

    function checkRequirements () {
        echo "(*) node: ";
        passthru("node -v", $nodeV);
        echo "(*) npm: ";
        passthru("npm -v", $npmV);
        echo "(*) git: ";
        passthru("git --version", $gitV);
        // check nodejs, npm and git
        if ($nodeV > 0 || $npmV > 0 || $gitV > 0) {
            echo "(!) Missing nodejs or npm or git, please install them first\n";
            return false;
        }
        return true;
    }

    /**
     * clone repository and init submodules
     */
    function initGitRepository () {
        if (! file_exists($this->buildDir)) {
            mkdir(realpath($this->buildDir), '0777', true);
        }
        // init nomad repo
        if ( !file_exists($this->nomadCheckoutDir) ) {
            echo "Nomad checkout directory not found: {$this->nomadCheckoutDir}\n Aborting build.";
            return false;
        }
        if ( !file_exists($this->nomadCheckoutDir . DIRECTORY_SEPARATOR . 'nomad'. DIRECTORY_SEPARATOR . '.git')) {
            echo "(*) Will clone nomad repo into {$this->nomadCheckoutDir}\n";
            $repo_init_cmd = "cd {$this->nomadCheckoutDir} && git clone {$this->nomadRepoUri}\n";
            echo "executing $repo_init_cmd";
            passthru($repo_init_cmd, $repo_init_cmdV);
            if ($repo_init_cmdV > 0) {
                echo "(!) Can not init nomad repo into {$this->nomadBuildDir}\n";
                return false;
            }
        }
        return true;
    }

    function updateGitRepository () {
        $repo_update_cmd = <<<EOF
cd {$this->nomadBuildDir} && git clean -ffd
git fetch -a
git remote prune origin
git reset --hard
if [ {$this->branch} != "master" ]; then
    git checkout master
    git branch -D {$this->branch}
    git checkout -b {$this->branch} origin/{$this->branch}
else
    git merge origin/master
fi
git submodule init
git submodule update
EOF;
        passthru($repo_update_cmd, $repo_update_cmdV);
        if ($repo_update_cmdV > 0) {
            echo "(!) Can not update nomad repo \n";
            return false;
        }
        return true;
    }

    /**
     * builds nomad and copies contents to destination directory
     */
    function buildNomad () {
        echo "Building nomad";
        $BUILD_NUM = array_key_exists("BUILD_NUM", $GLOBALS) ? $GLOBALS["BUILD_NUM"] : "1000";
        $nomad_build_num = '1';
        $nomad_version = '1';
        $nomad_manifest_tpl = "{$this->nomadBuildDir}/app/manifest.tpl";
        // Find out nomad build num
        if (file_exists($nomad_manifest_tpl)) {
            $nomad_manifest = file($nomad_manifest_tpl);

            foreach($nomad_manifest as $item) {
                if (preg_match("/#\sVersion:(.*)/", "$item", $matches)) {
                    $nomad_version = trim($matches[1]);
                } else if (preg_match("/#\sBuild:(.*)/", "$item", $matches)) {
                    $nomad_build_num = trim($matches[1]);
                }
            }
        }

        // Update build_num against nomad build number and sugar's
        $BUILD_NUM = "$nomad_build_num/$BUILD_NUM" ;
        echo "(*) nomad version is $nomad_version\n";
        echo "(*) nomad build number is $nomad_build_num\n";
        echo "(*) sugar nomad building number is $BUILD_NUM\n";
        $nomad_build_cmd = <<<EOF
cd {$this->nomadBuildDir}
npm install .
./scripts/build-web.sh --build {$BUILD_NUM} --version {$nomad_version} --scheme {$this->scheme} --site-url ".."
EOF;
        passthru($nomad_build_cmd, $nomad_build_cmdV);
        if ( $nomad_build_cmdV == 0 && file_exists($this->buildDir)) {
            return $this->copyToDestination();
        } else {
            echo "(!) Failed to build nomad web \n";
            return false;
        }
    }

    /**
     * copies web version to `sugar/mobile` directory
     * @return bool
     */
    function copyToDestination () {
        foreach ($this->flavs as $flav) {
            $sugarBuildDirectory = $this->getSugarBuildDirectory($flav);
            if ($sugarBuildDirectory) {
                $mobileDir = $sugarBuildDirectory . DIRECTORY_SEPARATOR . 'mobile';
                $this->rome->remove($mobileDir); // clear mobile directory if exists
                passthru("cp -a {$this->nomadBuildDir}/web  $sugarBuildDirectory/mobile", $copy_nomadV);
                if ($copy_nomadV > 0) {
                    echo "(!) Failed to copy nomad web into $sugarBuildDirectory/mobile\n";
                } else {
                    echo "(*) Copied web into $sugarBuildDirectory/mobile successfully\n";
                }
            }
        }
    }

    function getSugarBuildDirectory ($flav) {
        $rootDir = $this->buildDir . DIRECTORY_SEPARATOR . $flav;
        if (!file_exists($rootDir)) {
            mkdir($rootDir);
        }
        $sugarBuildDir = $rootDir . DIRECTORY_SEPARATOR . 'sugarcrm';
        if (!file_exists($sugarBuildDir)) {
            mkdir($sugarBuildDir);
        }
        return $sugarBuildDir;
    }

}