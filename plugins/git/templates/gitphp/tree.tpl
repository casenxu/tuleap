{*
 *  tree.tpl
 *  gitphp: A PHP git repository browser
 *  Component: Tree view template
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *}
{include file='header.tpl'}

 {* Nav *}
   <div class="page_nav">
     {include file='nav.tpl' current='tree' logcommit=$commit}
     <br /><br />
   </div>

 {include file='title.tpl' titlecommit=$commit}

{if $commit}
    {include file='path.tpl' pathobject=$tree target='tree'}
{/if}

 <div class="page_body">
     {if $commit}
         {* List files *}
         <table cellspacing="0" class="treeTable">
             {include file='treelist.tpl'}
         </table>
     {else}
         <em>{t}No commits{/t}</em>
     {/if}
 </div>

 {include file='footer.tpl'}

