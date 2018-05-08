<?php
/**
 * Class that handles calculation of, and formatting of, the Sugar Marketing
 * Version string based on the typical version string presented to the builder.
 */
class MarketingVersionHandler
{
    /**
     * When we cannot figure out the marketing version, just use an empty string
     * @var string
     */
    private $default = '';

    /**
     * Format for the marketing version. This is not, and should not be,
     * configurable.
     * @var string
     */
    private $format = "(%s '%02d)";

    /**
     * Patters to use for calculating the marketing version, going back to 7.10.x
     * @var array
     */
    private $versionPatterns = [
        '^7.10.[\d]+.[\d]+$' => [
            'method' => 'getFormattedVersion',
            'args' => ['season' => 'Fall', 'year' => 17],
        ],
        '^7.11.[\d]+.[\d]+$' => [
            'method' => 'getFormattedVersion',
            'args' => ['season' => 'Winter', 'year' => 18],
        ],
        '^[\d]{1,2}.[\d]+.[\d]+' => [
            'method' => 'getCalculatedVersion',
        ],
    ];

    /**
     * Seasons for the marketing versions
     * @var array
     */
    private $seasons = [
        'Spring',
        'Summer',
        'Fall',
        'Winter',
    ];

    /**
     * Gets the formatted marketing version. Takes in an array of season and year
     * @param array $args
     * @return string
     */
    private function getFormattedVersion(array $args) : string
    {
        // If we don't have what we need then just bail
        if (!isset($args['season'], $args['year'])) {
            return $this->default;
        }

        // Otherwise return the formatted string
        return sprintf(
            $this->format,
            $args['season'],
            $args['year']
        );
    }

    /**
     * Gets the formatted marketing version if one is found according to the
     * supported patterns, or the default value
     * @param string $version
     * @return string
     */
    public function getMarketingVersion(string $version) : string
    {
        foreach ($this->versionPatterns as $pattern => $actions) {
            if (preg_match("#$pattern#", $version)) {
                if (method_exists($this, $actions['method'])) {
                    return $this->{$actions['method']}($actions['args'] ?? $version);
                }
            }
        }

        return $this->default;
    }

    /**
     * Gets the metadata for a version for use in forming the marketing version
     * @param string $version
     * @return array|null
     */
    private function getVersionMeta(string $version) : ?array
    {
        $versions = $this->getVersionData($version);
        return $versions[$version] ?? null;
    }

    /**
     * Calculates a marketing version value based on the software version string
     * @param string $version
     * @return string
     */
    private function getCalculatedVersion(string $version) : string
    {
        if (($meta = $this->getVersionMeta($version)) !== null) {
            return $this->getFormattedVersion($meta);
        }

        return $this->default;
    }

    /**
     * Retrieves the full list of versions for a major version value
     * @return array
     */
    private function getVersionData($version) : array
    {
        // Prepare the return
        $r = [];

        // Get the major version value from the version stirng
        $major = (int) substr($version, 0, strpos($version, '.'));

        // Since this scema essentially started full swing with the 8.0 release
        // we should enforce that as a minimum version number for handling this
        if ($major < 8) {
            return $r;
        }

        // Add 10 to it since our pattern is 8.x.x == Season '18 (until Winter)
        $year = $major + 10;

        // Starting with the major octect version to start with, iterate to the
        // the next major octet version, calculating variants of major.minor.sub
        // that will correspond to the various seasons and years of the version.
        for ($i = $major, $m = $i + 1; $i < $m; $i++) {
            // This loop handles the minor version, which will be 0..3 inclusive
            for ($j = 0; $j < 4; $j++) {
                $ver = "$i.$j.0";

                // We rev the year when the minor octet is 3
                if ($j === 3) {
                    $year++;
                }

                // Maintain weirdness of incrementing years wrong,
                // like for Sugar version 91, which would be in the
                // year 2101.
                if ($year > 99) {
                    $year -= 100;
                }

                // Save the major.minor value
                $r[$ver] = [
                    'season' => $this->seasons[$j],
                    'year' => $year,
                ];

                // This handles the sub octet, and we will only need this when the
                // minor octet is 0, so that our pattern becomes something like...
                //  - 8.0.0
                //  - 8.0.1
                //  - 8.0.2
                //  - 8.0.3
                //  - 8.1.0
                //  - 8.2.0
                //  - 8.3.0
                if ($j === 0) {
                    // Sub octet will be 1..3 inclusive
                    for ($k = 1; $k < 4; $k++) {
                        $r["$i.$j.$k"] = $r[$ver];
                    }
                }
            }
        }

        return $r;
    }
}
