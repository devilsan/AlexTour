{*<!--
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
-->*}

{strip}
    <div class="col-md-2">
        {if $MULTI_SELECT}
            {if !empty($LISTVIEW_ENTRIES)}<button id="item_lookup_select" class="item-lookup-select btn btn-default" ><strong>{vtranslate('Add All', $VD_MODULE)}</strong></button>{/if}
        {else}
            &nbsp;
        {/if}
    </div>
    <div class="col-md-10">
        {assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
        {include file="MPagination.tpl"|vtemplate_path:$VD_MODULE SHOWPAGEJUMP=true}
    </div>
{/strip}