<?php
namespace Sugarcrm\SugarcrmTests\Bootstrap;

use Behat\Gherkin\Node\TableNode;

class LdapFeatureContext extends FeatureContext
{
    /**
     * @var array
     */
    protected $sugarAdmin;

    /**
     * @var array
     */
    protected $ldapAdmin;

    /**
     * SetUp necessary configs.
     *
     * @param array $sugarAdmin
     * @param array $ldapAdmin
     */
    public function __construct(array $sugarAdmin, array $ldapAdmin)
    {
        $this->sugarAdmin = $sugarAdmin;
        $this->ldapAdmin = $ldapAdmin;
    }

    /**
     * @BeforeScenario @ldap
     */
    public function beforeLdapScenario()
    {
        $this->iAmOnHomepage();
        $this->clearLocalData();
        $this->reload();
        $this->iWaitUntilTheLoadingIsCompleted();
        $this->waitForThePageToBeLoaded();
    }

    /**
     * @AfterScenario @ldap
     */
    public function afterLdapScenario()
    {
        $this->beforeLdapScenario();
        $this->iLogin($this->ldapAdmin['username'], $this->ldapAdmin['password']);
        $this->iGoToAdministration();
        $this->clickLink('Password Management');
        $this->waitForThePageToBeLoaded();
        $this->uncheckOption('system_ldap_enabled');
        $this->pressButton('btn_save');
        $this->waitForThePageToBeLoaded();
        $this->deleteAddedUsers();
        $this->clearLocalData();
    }

    /**
     * Filling LDAP settings.
     *
     * @param TableNode $table
     * @param string $adminKey
     *
     * @Given /^As "([^"]*)" filling in the following LDAP settings:$/
     */
    public function fillLdapSettings($adminKey, TableNode $table)
    {
        $credentials = $this->$adminKey;
        if (empty($credentials)) {
            throw new \RuntimeException('Configuration for '.$adminKey.' not found');
        }

        $this->iLogin($credentials['username'], $credentials['password']);
        $this->iGoToAdministration();
        $this->clickLink('Password Management');
        $this->waitForThePageToBeLoaded();
        $page = $this->getSession()->getPage();
        $page->checkField('system_ldap_enabled');
        $clickToEditElement = $page->find(
            'xpath',
            '//span[contains(@class, "button") and text() = "Click to Edit"]'
        );
        $passwordEditElement = $page->findById('ldap_admin_password');
        $passwordEditHidden = false;
        if ($clickToEditElement && $clickToEditElement->isVisible() && !$passwordEditElement->isVisible()) {
            $passwordEditHidden = true;
        }
        foreach ($table->getHash() as $row) {
            switch ($row['type']) {
                case 'checkbox':
                    if ($row['value'] == 'checked') {
                        $page->checkField($row['field']);
                    } else {
                        $page->uncheckField($row['field']);
                    }
                    break;
                default:
                    if ($row['field'] == 'ldap_admin_password' && $passwordEditHidden) {
                        $clickToEditElement->click();
                    }
                    $page->fillField($row['field'], $row['value']);
                    break;
            }
        }
        $this->pressButton('btn_save');
        $this->waitForThePageToBeLoaded();
        $this->iLogout();
    }
}
