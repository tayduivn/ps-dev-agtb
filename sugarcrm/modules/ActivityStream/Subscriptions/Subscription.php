<?php

class Subscription extends Basic
{
    public $table_name = 'subscriptions';
    public $object_name = 'Subscription';
    public $module_name = 'Subscriptions';
    public $module_dir = 'ActivityStream/Subscriptions';

    public $id;
    public $name = '';
    public $parent_type;
    public $parent_id;
    public $deleted;
    public $active;
    public $date_entered;
    public $date_modified;
    public $created_by;
    public $created_by_name;

    /**
     * Gets the subscribed users for the record specified.
     * @param  SugarBean $record
     * @param  string    $type   Return type for data
     * @return mixed
     */
    public static function getSubscribedUsers(SugarBean $record, $type = 'array')
    {
        $query = self::getQueryObject();
        $query->select(array('created_by'));
        $query->where()->equals('parent_id', $record->id);
        $query->where()->equals('parent_type', $record->module_name);

        return $query->execute($type);
    }

    /**
     * Gets the subscribed records for the user specified.
     * @param  User   $user
     * @param  string $type Return type for data
     * @return mixed
     */
    public static function getSubscribedRecords(User $user, $type = 'array')
    {
        $query = self::getQueryObject();
        $query->select(array('parent_id'));
        $query->where()->equals('created_by', $user->id);

        return $query->execute($type);
    }

    /**
     * Checks whether the specified user is subscribed to the given record.
     * @param  User      $user
     * @param  SugarBean $record
     * @return string|null       GUID of subscription or null
     */
    public static function checkSubscription(User $user, SugarBean $record)
    {
        $query = self::getQueryObject();
        $query->select(array('id'));
        $query->where()->equals('parent_id', $record->id);
        $query->where()->equals('created_by', $user->id);
        $result = $query->execute();
        if (count($result)) {
            return $result[0]['id'];
        }
        return null;
    }

    /**
     * Retrieve the subscription bean for a user-record relationship.
     * @param  User      $user
     * @param  SugarBean $record
     * @return Subscription|null
     */
    public static function getSubscription(User $user, SugarBean $record)
    {
        $guid = self::checkSubscription($user, $record);
        if (!empty($guid)) {
            return BeanFactory::retrieveBean('Subscriptions', $guid);
        }
        return null;
    }

    /**
     * Adds a user subscription to a record if one doesn't already exist.
     * @param  User      $user
     * @param  SugarBean $record
     * @return string|bool       GUID of the subscription or false.
     */
    public static function subscribeUserToRecord(User $user, SugarBean $record)
    {
        if (!self::checkSubscription($user, $record)) {
            $seed = BeanFactory::getBean('Subscriptions');
            $seed->parent_type = $record->module_name;
            $seed->parent_id = $record->id;
            $seed->set_created_by = false;
            $seed->created_by = $user->id;
            return $seed->save();
        }
        return false;
    }

    public static function unsubscribeUserFromRecord(User $user, SugarBean $record)
    {
        $sub = self::getSubscription($user, $record);
        if ($sub) {
            $sub->mark_deleted($sub->id);
            return true;
        }
        return false;
    }

    /**
     * Returns a query object for subscription queries.
     * @return SugarQuery
     */
    protected static function getQueryObject()
    {
        $seed = BeanFactory::getBean('Subscriptions');
        $query = new SugarQuery();
        $query->from($seed);
        return $query;
    }
}
