<?php

class Latin
{

    public function __construct($rome, $translationPath, $baseDir, $ver, $no_latin_scm, $langs = null)
    {
        $this->translationPath = $translationPath;
        $this->rome = $rome;
        $this->baseDir = realpath($baseDir);
        $this->ver = $ver;
        $this->no_latin_scm = $no_latin_scm;
        $this->langs = $langs;
        if (empty($this->startPath)) {
            $this->startPath = $this->baseDir;
        }
    }

    private function updateGit()
    {
        if (!file_exists($this->translationPath)) {
            chdir(dirname($this->translationPath));
            passthru("git clone git@github.com:sugarcrm/translations");
        }
        chdir(realpath($this->cwd . "/" . $this->translationPath));
        
        passthru("git checkout master");
        passthru("git fetch -a");
        passthru("git reset --hard");

        $translationBranch = "master";

        if (version_compare($this->ver, "7.8", ">=")) {
            $translationBranch = "7_8";     // 7_8 is the branch for 7.8.x train
        } elseif (version_compare($this->ver, "7.7", ">=")) {
            $translationBranch = "7_7";     // 7_7 is the branch for 7.7.x train
        }
        else if ( version_compare($this->ver, "7.7","<") &&
            version_compare($this->ver, "7.6", ">=")
        ) {
            $translationBranch = "7_b";     // 7_b is the translation branch for all versions >= 7.6.X
        }
        else if ( version_compare($this->ver, "7.6", "<") &&
            version_compare($this->ver, "7.0", ">")) {
            $translationBranch = "7_0";     // 7_0 is the translation branch for all versions > 7.0 and <= 7.5
        }

        exec("git branch -r", $remoteBranches);
        if (preg_grep("/$translationBranch/", $remoteBranches)) {
            passthru("git checkout $translationBranch");
            passthru("git pull origin $translationBranch");
        }
    }

    private function copyFiles($path)
    {
        require($this->cwd . "/" . $this->translationPath . '/config_override.php');
        $langConfig = array();
        $dir = new DirectoryIterator($path);
        foreach ($dir as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            if ($fileInfo->isDir()) {
                $this->copyFiles($fileInfo->getPathname());
            } else {
                foreach ($this->rome->config['builds'] as $flav => $build) {

                    if (empty($build['languages'])) {
                        continue;
                    }
                    if ($this->langs) {
                        $build['languages'] = array_intersect($build['languages'], $this->langs);
                    }
                    foreach ($build['languages'] as $lang) {
                        if (strpos($fileInfo->getFilename(), $lang . '.') !== false) {
                            $path = $fileInfo->getPathname();
                            $path = realpath($path);
                            $path = str_replace($this->baseDir . '/', '', $path);
                            $this->rome->setOnlyBuild($flav);
                            $this->rome->setStartPath($this->startPath);
                            $lpath = str_replace("$lang.", 'en_us.', $this->rome->cleanPath("{$this->baseDir}/$path"));
                            $en_usPath = "{$this->rome->buildPath}/$flav/$lpath";
                            if (file_exists($en_usPath)) {
                                $this->rome->buildFile($this->baseDir . '/' . $path, $this->startPath);
                            }
                        }
                        if (!empty($sugar_config['languages'][$lang])) {
                            $langConfig[$lang] = $sugar_config['languages'][$lang];
                        } else {
                            $langConfig[$lang] = $lang;
                        }
                    }
                    $lic_cont = trim(file_get_contents($this->baseDir . '/sugarcrm/LICENSE'));
                    $subbed_lic = str_replace(PHP_EOL, PHP_EOL . ' * ', $lic_cont);
                    $license = '/*' . PHP_EOL . ' * ' . $subbed_lic . PHP_EOL . ' */' . PHP_EOL;
                    $lang_config_path = "{$this->rome->buildPath}/$flav/sugarcrm/install/lang.config.php";
                    $config_vars = var_export($langConfig, true);
                    file_put_contents($lang_config_path, "<?php\n$license\n" . '$config["languages"]=' . "$config_vars;");
                }
            }
        }
    }

    public function copyTranslations()
    {
        $this->cwd = getcwd();
        if ($this->no_latin_scm == false) {
            $this->updateGit();
        }

        $tmp_path=realpath("$this->cwd" ."/". "$this->translationPath");
        $this->copyFiles($tmp_path);
        chdir($this->cwd);
    }
}
