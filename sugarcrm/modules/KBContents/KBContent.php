<?php
if(!defined('sugarEntry') || !sugarEntry)
	die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

class KBContent extends SugarBean {

    const DEFAULT_STATUS = 'draft';
    const ST_DRAFT = 'draft';
    const ST_IN_REVIEW = 'in-review';
    const ST_APPROVED = 'approved';
    const ST_PUBLISHED = 'published';
    const ST_PUBLISHED_IN = 'published-in';
    const ST_PUBLISHED_EX = 'published-ex';
    const ST_EXPIRED = 'expired';

    public $table_name = "kbcontents";
    public $object_name = "KBContent";
    public $new_schema = true;
    public $module_dir = 'KBContents';
    public $importable = true;

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
        $config = $admin->getConfigForModule('KBContents');
        $category = BeanFactory::newBean('Categories');

        if (empty($config['category_root']) || !$category->retrieve($config['category_root'])) {
            $this->setupCategoryRoot();
            $config = $admin->getConfigForModule('KBContents');
        }

        return $config['category_root'];
    }

    /**
     * Setup root for KBContents categories.
     */
    public function setupCategoryRoot()
    {
        require_once 'clients/base/api/ConfigModuleApi.php';
        require_once 'include/api/RestService.php';

        $categoryRoot = BeanFactory::newBean('Categories');
        $categoryRoot->name = 'KBContentCategory';

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
                'module' => 'KBContents',
            )
        );
    }

    /**
     * Return primary language for KB.
     * @return array Key and label for primary language.
     */
    public function getPrimaryLanguage()
    {
        $langs = $this->getLanguages();
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
        if ($default === null) {
            $this->setupPrimaryLanguage();
            $default = $this->getPrimaryLanguage();
        }
        return $default;
    }

    /**
     * Return available languages for KB.
     * @return array
     */
    public function getLanguages()
    {
        $admin = BeanFactory::getBean('Administration');
        $config = $admin->getConfigForModule('KBContents');
        return isset($config['languages']) ? $config['languages'] : array();
    }

    /**
     * Setup Default Languages for KBContents.
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
                'module' => 'KBContents',
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

        if (empty($this->kbdocument_id)) {
            $doc = BeanFactory::getBean('KBDocuments');
            $doc->new_with_id = true;
            $doc->id = create_guid();
            $doc->name = $this->name;
            $doc->team_set_id = $this->team_set_id;
            $doc->team_id = $this->team_id;
            $doc->save();
            $this->load_relationship('kbdocuments_kbcontents');
            $this->kbdocuments_kbcontents->add($doc);
        }

        if (empty($this->kbarticle_id)) {
            $article = BeanFactory::getBean('KBArticles');
            $article->new_with_id = true;
            $article->id = create_guid();
            $article->name = $this->name;
            $article->team_set_id = $this->team_set_id;
            $article->team_id = $this->team_id;
            $article->save();
            $this->load_relationship('kbarticles_kbcontents');
            $this->kbarticles_kbcontents->add($article);
        }

        if (!empty($article) && !empty($doc)) {
            $article->load_relationship('kbdocuments_kbarticles');
            $article->kbdocuments_kbarticles->add($doc);
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
                if (!empty($this->kbdocument_id) && !empty($this->kbarticle_id)) {
                    $query = new SugarQuery();
                    $query->from(BeanFactory::getBean('KBContents'));
                    $query->select(array('id'))->fieldRaw('MAX(revision)', 'max_revision');
                    $query->where()
                        ->equals('kbdocument_id', $this->kbdocument_id)
                        ->equals('kbarticle_id', $this->kbarticle_id);

                    $result = $query->execute();
                    if (!empty($result[0]['max_revision'])) {
                        $this->revision = $result[0]['max_revision'] + 1;
                    }
                }
            }
            if (empty($this->status)) {
                $this->status = self::DEFAULT_STATUS;
            }
            $this->active_rev = (int) empty($this->kbarticle_id);
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
            $query->from(BeanFactory::getBean('KBContents'));
            $query->select(array('id'));
            $query->where()
                ->notEquals('id', $this->id)
                ->equals('kbdocument_id', $this->kbdocument_id)
                ->equals('kbarticle_id', $this->kbarticle_id);
            $query->orderBy('date_entered', 'DESC');
            $query->limit(1);

            $result = $query->execute();

            if ($result) {
                $bean = BeanFactory::getBean('KBContents', $result[0]['id']);
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
        if (empty($bean->kbarticle_id)) {
            $bean->active_rev = 1;
        } else {
            if ($bean->isPublished()) {
                $bean->resetActivRev();
                $bean->active_rev = 1;
                $bean->expirePublished();
                if (empty($bean->active_date)) {
                    $bean->active_date = $bean->db->convert($GLOBALS['timedate']->nowDbDate(), 'datetime');
                }
            } else {
                $activeRevisionStatus = $this->getActiveRevisionStatus($bean);
                if ($activeRevisionStatus && !in_array($activeRevisionStatus['status'], array('published-in', 'published-ex', 'published'))) {
                    $bean->resetActivRev();
                    $bean->active_rev = 1;
                    if (empty($bean->active_date)) {
                        $bean->active_date = $bean->db->convert($GLOBALS['timedate']->nowDbDate(), 'datetime');
                    }
                }
            }
        }
    }

    /**
     * Get status for document with active revision.
     * @param KBContent $bean
     * @return bool
     * @throws SugarQueryException
     */
    protected function getActiveRevisionStatus(KBContent $bean)
    {
        if ($bean->kbdocument_id && $bean->kbarticle_id) {
            $query = new SugarQuery();
            $query->from(BeanFactory::getBean('KBContents'));
            $query->select(array('id', 'status'));
            $query->where()
                ->notEquals('id', $bean->id)
                ->equals('active_rev', 1)
                ->equals('kbdocument_id', $bean->kbdocument_id)
                ->equals('kbarticle_id', $bean->kbarticle_id);
            $query->orderBy('revision', 'DESC');
            $query->limit(1);

            $result = $query->execute();

            if ($result) {
                return $result[0];
            }
        }
        return false;
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
                      kbdocument_id = {$bean->db->quoted($bean->kbdocument_id)} AND
                      kbarticle_id = {$bean->db->quoted($bean->kbarticle_id)}
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
                      kbdocument_id = {$bean->db->quoted($bean->kbdocument_id)} AND
                      kbarticle_id = {$bean->db->quoted($bean->kbarticle_id)} AND
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
