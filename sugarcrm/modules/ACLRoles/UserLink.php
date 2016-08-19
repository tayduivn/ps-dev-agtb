<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Class UserLink
 *
 * Class representing link to users for ACLRole bean.
 */
class UserLink extends Link2
{
    public function delete($id, $related_id = '')
    {
        if (empty($related_id)) {
            return parent::delete($id, $related_id);
        }

        $userBean = $this->getUserBean($related_id);
        if ($userBean === null) {
            return parent::delete($id, $related_id);
        }
        // Just updating user date_modified property.
        // We need to do this to update user hash and as result invalidate HTTP ETag.
        $userBean->setModifiedDate(TimeDate::getInstance()->nowDb());
        $userBean->save();

        return parent::delete($id, $related_id);
    }

    /**
     * Returns user bean instance.
     *
     * @param string $id
     * @return SugarBean|null
     */
    protected function getUserBean($id)
    {
        return BeanFactory::retrieveBean('Users', $id);
    }
}
