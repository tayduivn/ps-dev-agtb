<?php
namespace Sugarcrm\SugarcrmTests\Bootstrap;

/**
 * Defines application features from the specific context.
 */
class SamlFeatureContext extends FeatureContext
{
    /**
     * Switch to another window
     * @When /^I switch to login popup$/
     * @And /^I switch to login popup$/
     * @throws \RuntimeException
     */
    public function switchToLoginPopup()
    {
        $current = $this->getSession()->getWindowName();
        $windows = $this->getSession()->getWindowNames();
        if (count($windows) != 2) {
            throw new \RuntimeException("Only two windows must be opened at one moment.");
        }
        $second = array_filter($windows, function ($window) use ($current) {
                return $window != $current;
        });
        $second = array_pop($second);
        $this->getSession()->switchToWindow($second);
    }

    /**
     * Switch to main window
     * @When /^I switch to main window$/
     * @And /^I switch to main window$/
     */
    public function switchToMainWindow()
    {
        $this->getSession()->switchToWindow(null);
    }

    /**
     * wait until login window will be opened
     * @When /^I wait until login popup is opened$/
     * @And /^I wait until login popup is opened$/
     */
    public function waitForLoginPopup()
    {
        $this->spin(function (FeatureContext $context) {
            return count($context->getSession()->getWindowNames()) == 2;
        }, 10);
    }

    /**
     * open SAML logout window
     * @When /^I initiate SAML logout$/
     */
    public function openSamlLogoutWindow()
    {
        $currentUrl = $this->getSession()->getCurrentUrl();
        $redirectTo = sprintf(
            'http://localhost:8080/simplesaml/saml2/idp/SingleLogoutService.php?ReturnTo=%s',
            $currentUrl
        );
        $this->visit($redirectTo);
    }
}
