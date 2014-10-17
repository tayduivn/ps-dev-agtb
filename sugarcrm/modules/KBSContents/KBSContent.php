<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */
class KBSContent extends SugarBean {

    const DEFAULT_STATUS = 'draft';
    const ST_DRAFT = 'draft';
    const ST_IN_REVIEW = 'in-review';
    const ST_APPROVED = 'approved';
    const ST_PUBLISHED = 'published';
    const ST_PUBLISHED_IN = 'published-in';
    const ST_PUBLISHED_EX = 'published-ex';
    const ST_EXPIRED = 'expired';

    public $table_name = "kbscontents";
    public $object_name = "KBSContent";
    public $new_schema = true;
    public $module_dir = 'KBSContents';

    public $status;
    public $active_rev;
    public $internal_rev;
    public $active_date;
    public $exp_date;

    /**
     * Return root id for KB categories.
     * @return string for root node of KB categories.
     */
    public function getCategoryRoot()
    {
        $admin = BeanFactory::getBean('Administration');
        $config = $admin->getConfigForModule('KBSContents');
        $category = BeanFactory::newBean('Categories');

        if (empty($config['category_root']) || !$category->retrieve($config['category_root'])) {
            $this->setupCategoryRoot();
            $config = $admin->getConfigForModule('KBSContents');
        }

        return $config['category_root'];
    }

    /**
     * Setup root for KBSContents categories.
     */
    public function setupCategoryRoot()
    {
        require_once 'clients/base/api/ConfigModuleApi.php';
        require_once 'include/api/RestService.php';

        $categoryRoot = BeanFactory::newBean('Categories');
        $categoryRoot->name = 'KBSContentCategory';

        $apiUser = new User();
        $apiUser->is_admin = '1';
        $api = new RestService();
        $api->user = $apiUser;
        $api->platform = 'base';
        $client = new ConfigModuleApi();
        $client->configSave(
            $api,
            array(
                'category_root' => $categoryRoot->makeRoot(),
                'module' => 'KBSContents',
            )
        );
    }

    /**
     * Return primary language for KB.
     * @return array Key and label for primary language.
     */
    public function getPrimaryLanguage()
    {
        $admin = BeanFactory::getBean('Administration');
        $config = $admin->getConfigForModule('KBSContents');

        if (empty($config['languages']['primary'])) {
            $this->setupPrimaryLanguage();
            $config = $admin->getConfigForModule('KBSContents');
        }
        $langs = $config['languages'];
        $default = null;
        foreach ($langs as $lang) {
            if ($lang['primary'] === true) {
                $default = $lang;
                unset($default['primary']);
                $default = array(
                    'label' => reset($default),
                    'key' => key($default)
                );
                break;
            }
        }
        return $default;
    }

    /**
     * Setup Default Languages for KBSContents.
     */
    public function setupPrimaryLanguage()
    {
        require_once 'clients/base/api/ConfigModuleApi.php';
        require_once 'include/api/RestService.php';

        $apiUser = new User();
        $apiUser->is_admin = '1';
        $api = new RestService();
        $api->user = $apiUser;
        $api->platform = 'base';
        $client = new ConfigModuleApi();
        $client->configSave(
            $api,
            array(
                'languages' => array(
                    array(
                        'en' => 'English',
                        'primary' => true,
                    ),
                ),
                'module' => 'KBSContents',
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function save_relationship_changes($is_update, $exclude = array())
    {
        parent::save_relationship_changes($is_update, $exclude);

        if ($is_update) {
            return;
        }

        $doc = $article = null;

        if (empty($this->kbsdocument_id)) {
            $doc = BeanFactory::getBean('KBSDocuments');
            $doc->new_with_id = true;
            $doc->id = create_guid();
            $doc->name = $this->name;
            $doc->save();
            $this->load_relationship('kbsdocuments_kbscontents');
            $this->kbsdocuments_kbscontents->add($doc);
        }

        if (empty($this->kbsarticle_id)) {
            $article = BeanFactory::getBean('KBSArticles');
            $article->new_with_id = true;
            $article->id = create_guid();
            $article->name = $this->name;
            $article->save();
            $this->load_relationship('kbsarticles_kbscontents');
            $this->kbsarticles_kbscontents->add($article);
        }

        if (!empty($article) && !empty($doc)) {
            $article->load_relationship('kbsdocuments_kbsarticles');
            $article->kbsdocuments_kbsarticles->add($doc);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function save($check_notify = false)
    {
        if(empty($this->id) || !empty($this->new_with_id)) {
            if (empty($this->language)) {
                $lang = $this->getPrimaryLanguage();
                $this->language = $lang['key'];
            }
            if (empty($this->revision)) {
                $this->revision = 1;
                if (!empty($this->kbsdocument_id) && !empty($this->kbsarticle_id)) {
                    $query = new SugarQuery();
                    $query->from(BeanFactory::getBean('KBSContents'));
                    $query->select(array('id'))->fieldRaw('MAX(revision)', 'max_revision');
                    $query->where()
                        ->equals('kbsdocument_id', $this->kbsdocument_id)
                        ->equals('kbsarticle_id', $this->kbsarticle_id);

                    $result = $query->execute();
                    if (!empty($result[0]['max_revision'])) {
                        $this->revision = $result[0]['max_revision'] + 1;
                    }
                }
            }
            if (empty($this->status)) {
                $this->status = self::DEFAULT_STATUS;
            }
            $this->active_rev = (int) empty($this->kbsarticle_id);
        }

        $this->checkActivRev();

        return parent::save($check_notify);
    }

    /**
     * {@inheritDoc}
     */
    public function mark_deleted($id)
    {
        if ($this->active_rev == 1) {
            $query = new SugarQuery();
            $query->from(BeanFactory::getBean('KBSContents'));
            $query->select(array('id'));
            $query->where()
                ->notEquals('id', $this->id)
                ->equals('kbsdocument_id', $this->kbsdocument_id)
                ->equals('kbsarticle_id', $this->kbsarticle_id);
            $query->orderBy('date_entered', 'DESC');
            $query->limit(1);

            $result = $query->execute();

            if ($result) {
                $bean = BeanFactory::getBean('KBSContents', $result[0]['id']);
                if ($bean->id) {
                    $this->resetActivRev();

                    $bean->active_rev = 1;
                    $bean->save();
                }
            }
        }
        parent::mark_deleted($id);
    }

    /**
     * Checks if current article was published.
     * @return bool
     */
    protected function isPublished()
    {
        $published = array('published-in', 'published-ex', 'published');
        if(empty($this->id) || !empty($this->new_with_id)) {
            return in_array($this->status, $published);
        } else {
            $dataChanges = $this->db->getDataChanges($this);
            if (!isset($dataChanges['status'])) {
                return false;
            }
            return in_array($dataChanges['status']['after'], $published) &&
            !in_array($dataChanges['status']['before'], $published);
        }
    }

    /**
     * Check is current document active revision or not.
     * Marks all previous revisions as non-active.
     * Marks all previous published revisions as expired.
     * @param SugarBean $bean
     */
    protected function checkActivRev($bean = null)
    {
        $bean = ($bean === null) ? $this : $bean;
        if (empty($bean->kbsarticle_id)) {
            $bean->active_rev = 1;
        } elseif ($bean->isPublished()) {
            $bean->resetActivRev();
            $bean->active_rev = 1;
            $bean->expirePublished();
            if (empty($bean->active_date)) {
                $bean->active_date = $bean->db->convert($GLOBALS['timedate']->nowDbDate(), 'datetime');
            }
        }
    }

    /**
     * Reset active revision status for all revisions in article.
     * @param SugarBean $bean
     */
    protected function resetActivRev($bean = null)
    {
        $bean = ($bean === null) ? $this : $bean;
        $query = "UPDATE {$bean->table_name}
                    SET active_rev = 0
                    WHERE
                      kbsdocument_id = {$bean->db->quoted($bean->kbsdocument_id)} AND
                      kbsarticle_id = {$bean->db->quoted($bean->kbsarticle_id)}
                ";
        $bean->db->query($query);
    }

    /**
     * Expire all published articles.
     * @param SugarBean $bean
     */
    protected function expirePublished($bean = null)
    {
        $bean = ($bean === null) ? $this : $bean;
        $expDate = $this->db->convert("'".$GLOBALS['timedate']->nowDb()."'", 'datetime');
        $publishStatuses = implode("', '", array('published-in', 'published-ex', 'published'));
        $query = "UPDATE {$bean->table_name}
                    SET exp_date = {$expDate}, status = {$bean->db->quoted("expired")}
                    WHERE
                      kbsdocument_id = {$bean->db->quoted($bean->kbsdocument_id)} AND
                      kbsarticle_id = {$bean->db->quoted($bean->kbsarticle_id)} AND
                      id != {$bean->db->quoted($bean->id)} AND
                      status IN ('{$publishStatuses}')
                ";
        $bean->db->query($query);
    }

    /**
     * {@inheritdoc}
     **/
    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }
        return false;
    }

    public function get_summary_text()
    {
        return $this->name;
    }
}
