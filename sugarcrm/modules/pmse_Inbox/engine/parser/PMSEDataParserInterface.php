<?php
/**
 * interfaces for classes
 * ADAMUserRoleParser, ADAMFormResponseParser, ADAMFieldParser and ADAMBusinessRuleParser
 * @codeCoverageIgnore
 */
interface PMSEDataParserInterface
{
    public function setEvaluatedBean($bean);
    public function setCurrentUser($currentUser);
    public function setBeanList($beanList);
    public function parseCriteriaToken($criteriaToken, $params = array());
}
