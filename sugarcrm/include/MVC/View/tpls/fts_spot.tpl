
!!!! RESULTS !!!!

<br>

{foreach from=$resultSet item=result}

Module:{$result->getModuleName()} ID: {$result->getId()} {$result->getSummaryText()} <br>
<b>Hit Field:</b> {$result->getHighlightedFieldName()}   <b>Hit Text Highlighted:</b> {$result->getHighlightedHitText()}

<br>
<br>
{/foreach}