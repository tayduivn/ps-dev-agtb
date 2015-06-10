<?php

namespace Sugarcrm\Sugarcrm\Util\Arrays\TrackableArray;


/**
 * Class TrackableArray
 *
 * Implements an array in which all changes can be tracked and replayed on another array.
 *
 * @package Sugarcrm\Sugarcrm\Util\Arrays\TrackableArray
 */
class TrackableArray extends \ArrayObject
{
    protected $modifiedKeys = array();

    protected $unsetKeys = array();

    protected $track = false;

    /**
     * {@inheritdoc} Also tracks sets to keys if tracking is currently enabled.
     * Array values are automatically converted to instances of TrackableArray
     */
    public function offsetSet($offset, $value)
    {
        if ($this->track) {
            $this->modifiedKeys[$offset] = true;
            if (isset($this->unsetKeys[$offset])) {
                unset($this->unsetKeys[$offset]);
            }
        }
        if (is_array($value)) {
            $value = new TrackableArray($value);
            $value->enableTracking($this->track);
        }
        parent::offsetSet($offset, $value);
    }

    /**
     * {@inheritdoc} Also tracks unsets to keys if tracking is currently enabled.
     */
    public function offsetUnset($offset)
    {
        if ($this->track) {
            $this->unsetKeys[$offset] = true;
            if (isset($this->modifiedKeys[$offset])) {
                unset($this->modifiedKeys[$offset]);
            }
        }
        parent::offsetUnset($offset);
    }

    /**
     * {@inheritdoc} Override of parent signature to return by reference.
     * This is done to allow multidimensional array syntax. Ex. $arr['foo']['bar'] = 'baz';
     * Multidimensional arrays created this way are automatically converted to TrackableArray instances.
     */
    public function &offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            $val = new TrackableArray();
            $val->enableTracking($this->track);
            $this->offsetSet($offset, $val);
        } else {
            $val = parent::offsetGet($offset);
        }

        return $val;
    }

    /**
     * Merges the given array into the values stored in this array.
     * These changes are not tracked.
     * The merge is not recursive.
     *
     * @param array $array
     *
     * @return null;
     */

    public function populateFromArray(array $array)
    {
        $shouldTrack = $this->track;
        $this->enableTracking(false);
        foreach ($array as $key => $val) {
            $this->offsetSet($key, $val);
        }
        $this->enableTracking($shouldTrack);
    }

    /**
     * Enables or disables tracking of changes for this array. Recursively applies to multidimensional arrays.
     *
     * @param bool $track
     *
     * @return null
     */
    public function enableTracking($track = true)
    {
        $this->track = $track;
        array_walk($this, function ($val) use ($track) {
            if ($val instanceof TrackableArray) {
                $val->enableTracking($track);
            }
        }
        );
    }

    /**
     * Modifies an array with the changes that have been tracked by this array.
     * Applies recursively to multidimensional arrays.
     *
     * @param array $array
     */
    public function applyTrackedChangesToArray(array &$array)
    {
        foreach ($this->modifiedKeys as $key => $v) {
            $val = $this->offsetGet($key);
            if ($val instanceof self) {
                if (!isset($array[$key]) || !is_array($array[$key])) {
                    $array[$key] = array();
                }
                $val->applyTrackedChangesToArray($array[$key]);
            } else {
                $array[$key] = $val;
            }
        }

        foreach ($this->unsetKeys as $key => $v) {
            unset($array[$key]);
        }
    }

    public function __toString()
    {
        return print_r($this, true);
    }
}
