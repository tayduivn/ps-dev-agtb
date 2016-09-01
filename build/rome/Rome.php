<?php
ini_set("max_execution_time", "0");
ini_set("memory_limit", "600M");
define('sugarEntry', true);

class Rome
{
    protected $config = array();
    protected $output = array();
    protected $commentBuffer = array();
    protected $active = array();
    protected $depth = 0;
    protected $file = '';
    protected $lineCount = 0;
    protected $tagStack = array();
    protected $excludeDirs = array();
    protected $buildPath = 'buildstest';
    public $startPath = '';
    //FILE ONLY TAGS (reset at build of each file)
    protected $onlyOutput = array();
    protected $retainCommentSpacing = false;
    //LANGUAGE BUILDS (never reset)
    protected $onlyBuild = array();

    protected $symlinks = array();
    
    /**
     * The Regex that is used for matching build tags.
     *
     * @var string
     */
    protected $buildTagRegex = '/\/\/\s*(BEGIN|END|FILE|ELSE)\s*SUGARCRM\s*(.*) ONLY/i';

    /**
     * Construct that loads the config file
     *
     * @return unknown_type
     */
    public function __construct()
    {
        include 'config/config.php';
        $this->config = $config;
        //$this->addTestDirectoriesToBlackList();
        $this->retainCommentSpacing = isset($this->config['retainCommentSpacing']) ? $this->config['retainCommentSpacing'] : false;
    }

    public function __get($key)
    {
        return $this->$key;
    }

    /**
     * Parses the blacklist directories list and adds the relevant tests/ directory entry for them as well
     */
    protected function addTestDirectoriesToBlackList()
    {
        foreach ($this->config['builds'] as $build => $details) {
            $blackListExtras = array();

            if (empty($this->config['blackList'][$build])) {
                continue;
            }

            foreach ($this->config['blackList'][$build] as $dirname => $x) {
                $blacklistExtras["tests/$dirname"] = $x;
            }

            foreach ($blacklistExtras as $dirname => $x) {
                $this->config['blackList'][$build][$dirname] = $x;
            }
        }
    }

    /**
     * Sets the directories to be excluded from building
     */
    public function setExcludeDirs($dirs)
    {
        $this->excludeDirs = $dirs;
    }

    /**
     * Sets the Path For The Build Directory
     */
    public function setBuildDir($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->buildPath = realpath($dir);
    }

    public function getBuildDir()
    {
        return $this->buildPath;
    }

    public function setOnlyBuild($flav)
    {
        $this->onlyBuild = (is_array($flav)) ? $flav : array($flav => $flav);
    }

    /**
     *  Specifies the regions that should be built
     *
     */
    public function setFlav($flav)
    {
        $replaced = array();
        foreach ($this->config['builds'] as $build => $data) {
            if ($build != $flav) {
                $replaced[] = $build;
            }
        }
        foreach ($replaced as $build) {
            unset($this->config['builds'][$build]);
        }

    }

    public function setSkipFlavs($skipflavs)
    {
        $this->skipflavs = $skipflavs;
        foreach ($this->config['builds'] as $build => $data) {
            if (!isset($this->skipflavs[$build])) {
                continue;
            }
            if ($this->skipflavs[$build]) {
                $replaced[] = $build;
            }
        }
        foreach ($replaced as $build) {
            unset($this->config['builds'][$build]);
        }
    }

    public function setRegions($regions, $replace = false)
    {
        $replaced = array();
        foreach ($this->config['builds'] as $build => $data) {
            $replaced[] = $build;
            foreach ($regions as $reg) {
                $reg_build = $reg . '_' . $build;
                $this->config['builds'][$reg_build] = $this->config['builds'][$build];
                unset($this->config['builds'][$reg_build]['reg']);
                $this->config['builds'][$reg_build]['reg'][] = $reg;

            }
        }
        if ($replace) {
            foreach ($replaced as $build) {
                unset($this->config['builds'][$build]);
            }
        }

    }

    public function setDeployments($deployments, $replace = false)
    {
        $replaced = array();
        foreach ($this->config['builds'] as $build => $data) {
            $replaced[] = $build;
            foreach ($deployments as $dep) {
                $dep_build = $dep . '_' . $build;
                $this->config['builds'][$dep_build] = $this->config['builds'][$build];
                unset($this->config['builds'][$dep_build]['dep']);
                $this->config['builds'][$dep_build]['dep'][] = $dep;
            }
        }
        if ($replace) {
            foreach ($replaced as $build) {
                unset($this->config['builds'][$build]);
            }
        }
    }

    public function setRetainCommentSpacing($val)
    {
        $this->retainCommentSpacing = $val;
    }

    /**
     *  dynamic generate sugarcrm version
     *
     */
    public function setVersion($ver)
    {
        $this->config['sugarVariables']['@_SUGAR_VERSION'] = $ver;

    }

    /**
     *  Cleanup Function that is run between files to ensure that every files build starts clean
     *
     */

    protected function clearOutput()
    {
        $this->depth = 0;
        $this->lineCount = 0;
        $this->currentTags = array();
        $this->tagStack = array();
        $this->onlyOutput = array();
        $this->commentBuffer = array();

        foreach ($this->config['builds'] as $build => $includes) {
            $this->output[$build] = '';
            //everyone is active by default
            $this->active[$build] = true;
        }
    }

    /**
     * Adds a line to all the active builds.
     */

    protected function addToOutput($line)
    {
        $emp = empty($this->commentBuffer);
        $ps = ($emp) ? strpos(trim($line), '/*') : false;
        if ($ps !== 0) {
            $ps = false;
        }
        $pe = strpos($line, '*/');
        $output = '';
        $comment = '';
        $tailout = '';
        $flushComment = false;

        //remove '$Id:', '@version','$Log:','$Header:'
        foreach ($this->config['replace'] as $id) {
            if (strpos($line, $id)) {
                $line = "\n";
                break;
            }
        }
        //if we don't have anything in the comment buffer (we aren't in a comment) and this isn't the start of a comment just output to the active builds
        if ($ps === false && $emp || ($ps !== false && $pe !== false)) {
            $output .= $line;
        } else {

            if ($ps !== false && $emp) {
                //ignore "/*"
                if ((strpos($line, '"/*"') == 0) && (strpos($line, "//*") == 0)) {
                    //starting a comment
                    $output = substr($line, 0, $ps);

                    $comment = substr($line, $ps);
                } else {
                    $output = $line;
                }
            }
            if ($pe !== false && !$emp) {
                //ending a comment
                //flush the comment
                $flushComment = true;
                $comment .= substr($line, 0, $pe + 2);
                $tailout .= substr($line, $pe + 2);

            }
            //not ending and not starting a comment then it's just a line in a comment
            if ($pe === false && $ps === false) {
                $comment = $line;
            }
        }

        foreach ($this->active as $build => $active) {
            if ($flushComment && !empty($this->commentBuffer[$build])) {
                $this->output[$build] .= $this->commentBuffer[$build];
            }
            if ($active) {
                $this->output[$build] .= $output;
                if (!empty($comment)) {
                    if ($flushComment) {
                        $this->output[$build] .= $comment;
                        $this->output[$build] .= $tailout;
                    } else {
                        if (!isset($this->commentBuffer[$build])) {
                            $this->commentBuffer[$build] = '';
                        }
                        $this->commentBuffer[$build] .= $comment;
                    }
                }

            } elseif ($this->retainCommentSpacing) {
                $this->output[$build] .= "\n";
            }
        }
        if ($flushComment) {
            $this->commentBuffer = array();
        }
    }

    public function throwException($msg, $die)
    {
        echo "\n" . $msg . " IN {$this->file} ON LINE {$this->lineCount}";
        //throw new Exception($msg . " IN {$this->file} ON LINE {$this->lineCount}");
        //if($die)die("EXCEPTION WAS THROWN");
    }

    protected function evalComment($results, $build)
    {
        if (empty($this->config['builds'][$build]['matches'])) {
            $this->config['builds'][$build]['matches'] = array();
            if (!empty($this->config['builds'][$build]['flav'])) {
                $this->config['builds'][$build]['matches'] = array_merge(
                    $this->config['builds'][$build]['matches'],
                    $this->config['builds'][$build]['flav']
                );
            }
            if (!empty($this->config['builds'][$build]['reg'])) {
                $this->config['builds'][$build]['matches'] = array_merge(
                    $this->config['builds'][$build]['matches'],
                    $this->config['builds'][$build]['reg']
                );
            }
            if (!empty($this->config['builds'][$build]['lic'])) {
                $this->config['builds'][$build]['matches'] = array_merge(
                    $this->config['builds'][$build]['matches'],
                    $this->config['builds'][$build]['lic']
                );
            }
            if (!empty($this->config['builds'][$build]['dep'])) {
                $this->config['builds'][$build]['matches'] = array_merge(
                    $this->config['builds'][$build]['matches'],
                    $this->config['builds'][$build]['dep']
                );
            }

        }
        $eval = $results['eval'];
        if (!empty($this->config['builds'][$build]['matches'])) {
            $eval = str_replace($this->config['builds'][$build]['matches'], "'match'", $results['eval']);
        }
        $reg = 'match';
        $flav = 'match';
        $lic = 'match';
        $dep = 'match';
        try {
            eval('$r =' . $eval . ';');
        } catch (Exception $e) {
            $this->throwExption("Check Comment Syntax: $eval", true);
            die();
        }

        return $r;

    }

    protected function setCurrentBuild($results, $can_activate)
    {
        $active = array();

        foreach ($this->active as $build => $a) {
            //top of the stack restore to no tag state
            if (empty($results) && $can_activate) {
                $active[$build] = true;
                continue;
            }
            if (!$can_activate && !$a) {
                $active[$build] = false;
                continue;
            }

            $eval = trim($results['eval']);
            if (empty($eval)) {
                $active[$build] = true;
                continue;
            } else {
                $active[$build] = $this->evalComment($results, $build);

            }
        }
        //now set the objects active to match;
        $this->active = $active;

    }

    protected function changeActive($results)
    {
        $type = strtolower($results['state']);
        if ($type === 'file') {
            foreach ($this->active as $build => $a) {
                if (empty($this->onlyOutput[$build])) {
                    $this->onlyOutput[$build] = $this->evalComment($results, $build);
                }
            }

        } elseif ($type === 'end') {
            if ($this->depth < 1) {
                $this->throwException("END TAG BEFORE BEGIN TAG", true);
            }
            $this->depth--;

            array_pop($this->tagStack);
            $lastTags = ($this->depth > 0) ? $this->tagStack[$this->depth - 1] : array();
            $this->setCurrentBuild($lastTags, true);
        } elseif ($type == 'begin') {
            $this->depth++;
            $this->tagStack[] = $results;
            $this->setCurrentBuild($results, false);
        }

    }

    protected function getLower($val)
    {
        static $lower = array();
        if (isset($lower[$val])) {
            return $lower[$val];
        }
        $lower[$val] = strtolower(trim($val));

        return $lower[$val];
    }

    protected function parseComment($line)
    {
        $results = array();
        preg_match($this->buildTagRegex, $line, $match);
        if (empty($match[2])) {
            return $results;
        }
        $results['state'] = strtolower($match[1]);
        $results['original'] = $match[2];
        $tags = $this->getTags();
        $results['eval'] = str_replace(array_keys($tags), array_values($tags), strtolower($match[2]));
        $results['eval'] = str_replace('=', '==', strtolower($results['eval']));

        return $results;
    }

    protected function getTags()
    {
        static $tags = array();
        if (empty($tags)) {
            $keys = array_keys($this->config['registry']);
            foreach ($keys as $key) {
                $tags[$key] = '$' . $key;
            }
        }

        return $tags;
    }

    public function buildFile($path, $startPath, $skipBuilds = array())
    {
        $this->clearOutput();

        if (!$this->isFile($path)) {
            $this->quickCopy($path, $skipBuilds);
        } elseif ($this->isLink($path)) {
            $linkTarget = readlink($path);
            $this->saveSymlink($path, $linkTarget, $skipBuilds);
        } elseif (!$this->hasSugarBuildTag($path)) {
            $this->quickCopy($path, $skipBuilds);
        } else {
            $this->file = $path;
            if (!empty($startPath)) {
                $this->startPath = $startPath;
            }
            $fp = fopen($path, 'r');

            while ($line = fgets($fp)) {
                $this->lineCount++;
                if (substr_count($line, '//') == 0) {
                    //not a comment keep moving along
                    $this->addToOutput($line);
                } else {
                    $result = $this->parseComment($line);
                    if (!empty($result)) {
                        $this->changeActive($result);
                        if ($this->retainCommentSpacing) {
                            $this->addToOutput("\n");
                        }
                    } else {
                        //just a normal comment let's add it back
                        $this->addToOutput($line);
                    }
                }
            }
            $this->writeFiles($path, $skipBuilds);
        }
    }

    /**
     * Check if the file contains any SugarCRM Build Tags
     *
     * @param string $file
     * @return bool
     */
    protected function hasSugarBuildTag($file)
    {
        return (preg_match($this->buildTagRegex, file_get_contents($file)) === 1);
    }

    public function cleanPath($path)
    {
        if (empty($this->startPath)) {
            return $path;
        } elseif (empty ($this->config['mergeDirs'])) {
            return str_replace($this->startPath . DIRECTORY_SEPARATOR, '', $path);
        } else {
            $path = str_replace($this->startPath . DIRECTORY_SEPARATOR, '', $path);

            return str_replace('translations', $this->config['mergeDirs']['translations'], $path);
        }
    }

    /**
     * Save a symlink to be written out after the build has completed
     *
     * @param string $path Path of the symlink
     * @param string $link Target of the symlink
     * @param array $skipBuilds What Builds to skip
     */
    protected function saveSymlink($path, $link, $skipBuilds = array())
    {
        $path = $this->cleanPath($path);
        $blackListPath = $this->getBlacklistPath($path);

        foreach ($this->output as $f => $o) {
            if (!empty($this->onlyBuild) && empty($this->onlyBuild[$f])) {
                continue;
            }

            if (!empty($this->config['blackList'][$f][$blackListPath])) {
                continue;
            }

            if (!empty($skipBuilds[$f]) || !empty($this->config['skipBuilds'][$f]) ||
                (!empty($this->onlyOutput) && empty($this->onlyOutput[$f]))
            ) {
                continue;
            }
            $this->makeDirs(dirname($path), $f);

            // lets save this for the end to make sure all the files are written
            $this->symlinks[$this->buildPath . DIRECTORY_SEPARATOR . $f . DIRECTORY_SEPARATOR . $path] = $link;
        }
    }

    /**
     * This is called right after the build has completed to restore any symlinks found in the source dir.
     *
     */
    protected function writeSymlinks()
    {
        foreach ($this->symlinks as $path => $link) {
            if (is_file($path)) {
                unlink($path);
            }
            symlink($link, $path);
        }
        $this->symlinks = array();
    }

    protected function writeFiles($path, $skipBuilds = array())
    {
        $path = $this->cleanPath($path);
        $blackListPath = $this->getBlacklistPath($path);

        foreach ($this->output as $f => $o) {
            if (!empty($this->onlyBuild) && empty($this->onlyBuild[$f])) {
                continue;
            }

            if (!empty($this->config['blackList'][$f][$blackListPath])) {
                continue;
            }

            if (!empty($skipBuilds[$f]) || !empty($this->config['skipBuilds'][$f]) || (!empty($this->onlyOutput) && empty($this->onlyOutput[$f]))) {
                continue;
            }
            $this->makeDirs(dirname($path), $f);
            //replace some sugar variables
            $this->config['sugarVariables']['@_SUGAR_FLAV'] = strtoupper($f);
            foreach ($this->config['sugarVariables'] as $var => $data) {
                if ($data != '') {
                    $o = str_replace("$var", "$data", $o);
                }
            }
            //str_replace is equiv to dos2unix command
            file_put_contents(
                $this->buildPath . DIRECTORY_SEPARATOR . $f . DIRECTORY_SEPARATOR . $path,
                str_replace("\r\n", "\n", $o)
            );
        }

    }

    protected function makeDirs($path, $build)
    {
        static $madeDirs = array();
        if (empty($madeDirs[$build][$path])) {
            $b_path = $this->buildPath . DIRECTORY_SEPARATOR . $build;
            if ($path != '.') {
                $b_path .= DIRECTORY_SEPARATOR . $path;
            }
            if (!file_exists($b_path)) {
                mkdir($b_path, 0755, true);
            }
            $madeDirs[$build][$path] = true;
        }
    }

    protected function quickCopy($orig_path, $skipBuilds)
    {
        $path = $this->cleanPath($orig_path);
        foreach ($this->active as $f => $a) {
            if (!empty($this->config['blackList'][$f][$path]) || !empty($skipBuilds[$f]) || !empty($this->config['skipBuilds'][$f])) {
                continue;
            }
            $this->makeDirs(dirname($path), $f);
            $b_path = $this->buildPath . DIRECTORY_SEPARATOR . $f . DIRECTORY_SEPARATOR . $path;
            copy($orig_path, $b_path);
        }
    }

    public function setStartPath($path)
    {
        $this->startPath = $path;
    }

    public function build($path, $skipBuilds = array())
    {
        if (empty($this->startPath)) {
            $this->startPath = $path;
        }
        $d = dir($path);
        while ($e = $d->read()) {
            //don't change entryPoint.php
            if (substr($e, 0, 1) == '.' && $e != '.htaccess') {
                continue;
            }
            if (!empty($this->config['skipDirs'][$e]) || in_array($e, $this->excludeDirs)) {
                continue;
            }
            $next = $path . DIRECTORY_SEPARATOR . $e;
            if (is_dir($next)) {
                $sugar_path = $this->cleanPath($next);
                $blackListPath = $this->getBlacklistPath($sugar_path);

                $nextSkip = $skipBuilds;
                foreach ($this->active as $f => $a) {
                    if (empty($nextSkip[$f]) && !empty($this->config['blackList'][$f][$blackListPath])) {

                        //Also place the tests directory in the skip list
                        if ((strpos(trim($sugar_path), 'modules') == 0)) {
                            $this->config['blackList'][$f]['tests/' . $sugar_path] = true;
                        }
                        $nextSkip[$f] = true;
                    }
                }
                $this->build($next, $nextSkip);
            } elseif ($this->isFile($next)) {
                $this->buildFile($next, "", $skipBuilds);

            } else {
                //these aren't files we scan just copy them over
                $this->quickCopy($next, $skipBuilds);
            }
        }
        $d->close();

        // now that the build is done, lets write out any symlinks that were found.
        $this->writeSymlinks();

        if ($path == $this->startPath) {
            echo 'DONE' . "\n";
        }

        return true;
    }

    /**
     * Do we have a symlink?
     *
     * @param  string $link The potential symlink
     * @return bool
     */
    protected function isLink($link)
    {
        $path = $this->cleanPath($link);

        return is_link($link) && empty($this->config['excludeFileTypes'][substr(
            $link,
            -4
        )]) && empty($this->config['excludeFiles'][$path]);
    }

    protected function isFile($next)
    {
        $path = $this->cleanPath($next);

        return is_file($next) && empty($this->config['excludeFileTypes'][substr(
            $next,
            -4
        )]) && empty($this->config['excludeFiles'][$path]);
    }

    public function remove($path)
    {
        if (!file_exists($path)) {
            return true;
        }
        if (is_file($path)) {
            return unlink($path);
        }
        $d = dir($path);
        while ($e = $d->read()) {
            if ($e == '.' || $e == '..') {
                continue;
            }
            $nPath = $path . '/' . $e;
            $this->remove($nPath);
        }
        $d->close();

        return rmdir($path);
    }

    /**
     * @param string $path Path to the file that is being built
     * @return string
     */
    protected function getBlacklistPath($path)
    {
        $blackListPath = strpos($path, '/') == 0 ? substr($path, 1) : $path;
        // just in case someone is building with --base_dir pointed directly at the
        // sugarcrm/ dir.
        if (strpos($blackListPath, 'sugarcrm/') !== 0) {
            $blackListPath = 'sugarcrm/' . $blackListPath;
        }
        return $blackListPath;
    }

    public function generateMD5($dir)
    {
        $md5 = $this->calculateMD5($dir);
        $md5String = var_export($md5, true);
        $contents = <<<MD5
<?php

\$md5_string = $md5String;

MD5;
        file_put_contents($dir . '/files.md5', $contents);
    }

    protected function calculateMD5($dir)
    {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir)
        );

        $results = array();
        foreach ($it as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            $results['./' . $it->getSubPathName()] = md5_file($fileInfo);
        }

        ksort($results);
        return $results;
    }
}

function RomeErrorHandler($errno, $errstr, $errfile, $errline, $context)
{
    echo "\n$errno Error: $errstr \n\t{$errfile} [$errline]";
    echo "\n\tBuild File: {$GLOBALS['rome']->file}({$GLOBALS['rome']->lineCount})";
    if (!empty($context['eval'])) {
        echo "\n\tComment Evaluated To:" . $context['eval'];
    }
    echo "\n";
    die();

}

set_error_handler('RomeErrorHandler');
