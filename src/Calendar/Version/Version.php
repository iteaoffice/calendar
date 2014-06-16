<?php
/**
 * DebraNova copyright message placeholder
 *
 * @category    Calendar
 * @package     Version
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\Version;

use Zend\Json\Json;

/**
 * @category    Calendar
 * @package     Version
 */
class Version
{
    /**
     * DebraNova-Website version identification - see compareVersion()
     */
    const VERSION = '1.0.1-dev';
    /**
     * The latest stable version Zend Framework available
     *
     * @var string
     */
    protected static $latestVersion;

    /**
     * Compare the specified Zend Framework version string $version
     * with the current Zend_Version::VERSION of Zend Framework.
     *
     * @param string $version A version string (e.g. "0.7.1").
     *
     * @return int -1 if the $version is older,
     *             0 if they are the same,
     *             and +1 if $version is newer.
     *
     */
    public static function compareVersion($version)
    {
        $version = strtolower($version);
        $version = preg_replace('/(\d)pr(\d?)/', '$1a$2', $version);

        return version_compare($version, strtolower(self::VERSION));
    }

    /**
     * Fetches the version of the latest stable release
     *
     * @link https://api.github.com/repos/debranova/_website/git/refs/tags/release-
     * @return string
     */
    public static function getLatest()
    {
        if (null === static::$latestVersion) {
            static::$latestVersion = 'not available';
            $url                   = 'https://api.github.com/repos/debranova/calendar/git/refs/tags/release-';

            $apiResponse = Json::decode(file_get_contents($url), Json::TYPE_ARRAY);

            // Simplify the API response into a simple array of version numbers
            $tags = array_map(
                function ($tag) {
                    return substr($tag['ref'], 18); // Reliable because we're filtering on 'refs/tags/release-'
                },
                $apiResponse
            );

            // Fetch the latest version number from the array
            static::$latestVersion = array_reduce(
                $tags,
                function ($a, $b) {
                    return version_compare($a, $b, '>') ? $a : $b;
                }
            );
        }

        return static::$latestVersion;
    }

    /**
     * Returns true if the running version of Zend Framework is
     * the latest (or newer??) than the latest tag on GitHub,
     * which is returned by static::getLatest().
     *
     * @return bool
     */
    public static function isLatest()
    {
        return static::compareVersion(static::getLatest()) < 1;
    }
}
