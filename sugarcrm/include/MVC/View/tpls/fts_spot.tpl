
!!!!RESULTS!!!!

<br>

{foreach from=$resultSet item=result}

Module:{$result->getModuleName()} ID: {$result->getId()} {$result->getSummaryText()} <br>

{/foreach}