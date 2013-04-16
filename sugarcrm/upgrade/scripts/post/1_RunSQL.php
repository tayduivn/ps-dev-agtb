<?php

/**
 * Run SQL scripts from $temp_dir/scripts/ relevant to current conversion, e.g.
 * scripts/65x_to_67x_mysql.sql
 */
class SugarUpgradeRunSQL extends UpgradeScript
{
    public $order = 1000;
    public $type = self::UPGRADE_DB;

    public function run()
    {
        $vfrom = $this->implodeVersion($this->from_version);
        $vto = $this->implodeVersion($this->to_version);
        $this->log("Looking for SQL scripts from $vftom/{$this->from_flavor} to $vto/{$this->to_flavor}");
        if ($vfrom == $vto) {
            if ($this->from_flavor == $this->to_flavor) {
                // minor upgrade, no schema changes
                return;
            } else {
                $script = "{$vfrom}_{$this->from_flavor}_to_{$this->to_flavor}";
            }
        } else {
            $script = "{$vfrom}_to_{$vto}";
        }
        $script .= "_" . $this->db->getScriptName() . ".sql";
        $filename = $this->context['temp_dir'] . "/scripts/$script";
        $this->log("Script name: $script ($filename)");
        if (file_exists($filename)) {
            $this->parseAndExecuteSqlFile($filename);
        }
    }

    protected function parseAndExecuteSqlFile($sqlScript)
    {
        // TODO: resume support?
        $contents = file_get_contents($sqlScript);
        $anyScriptChanges = $contents;
        $resumeAfterFound = false;
        if (rewind($fp)) {
            $completeLine = '';
            $count = 0;
            while ($line = fgets($fp)) {
                if (strpos($line, '--') === false) {
                    $completeLine .= " " . trim($line);
                    if (strpos($line, ';') !== false) {
                        $query = str_replace(';', '', $completeLine);
                        // if resume from query is not null then find out
                        // from where
                        // it should start executing the query.

                        if ($query != null) {
                            $this->db->query($query);
                        }
                        $completeLine = '';
                    }
                }
            } // while
        }
    }
}
