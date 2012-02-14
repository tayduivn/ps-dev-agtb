{foreach from=$filterModules item=entry key=module}
    {if $entry.count > 0}
        <div class="ftsModuleFilterSpan">
            <input type="checkbox" checked="checked" id="{$entry.module}" name="module_filter" class="ftsModuleFilter">
            <span id="{$entry.module}_label">&nbsp;{$entry.label}</span>
            <span id="{$entry.module}_count">({$entry.count})</span>
        </div>
    {/if}
{/foreach}