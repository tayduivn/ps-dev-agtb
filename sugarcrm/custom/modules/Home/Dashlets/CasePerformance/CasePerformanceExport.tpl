{foreach from=$displayColumns key=colHeader item=params}{sugar_translate label=$params.label module=$pageData.bean.moduleDir},{/foreach}

{foreach name=rowIteration from=$data key=id item=rowData}{foreach from=$displayColumns key=col item=params}{if $params.link && !$params.customCode}{$rowData.$col}{elseif $params.customCode}{sugar_evalcolumn var=$params.customCode rowData=$rowData}{elseif $params.currency_format}{sugar_currency_format var=$rowData.$col round=$params.currency_format.round decimals=$params.currency_format.decimals symbol=$params.currency_format.symbol}{elseif $params.type == 'bool'}{if !empty($rowData[$col])}{$YES}{/if}{else}{$rowData.$col}{/if},{/foreach}

{/foreach}