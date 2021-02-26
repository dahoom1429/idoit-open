[{if $bShowMenuTreeButtons}]
	<div class="browser-content menuTreeTopArea">
	    <a class="fr" style="margin:11px 7px;" href="javascript:" onclick="toggleTreeExpand();">
	        <img src="[{$dir_images}]expand_icon.png" alt="" />
	    </a>
		<ul class="browser-tabs tree-tabs m0">
			<li><a class="text-shadow mouse-pointer [{$menuTree.objectClass}]" href="javascript:" onclick="[{$menuTree.objectLink}]">[{isys type="lang" ident="LC__CMDB__OBJECT_VIEW"}]</a></li>
            [{if $has_location_view_right}]
			<li><a class="text-shadow mouse-pointer [{$menuTree.locationClass}]" href="javascript:" onclick="[{$menuTree.locationLink}]">[{isys type="lang" ident="LC__CMDB__MENU_TREE_VIEW"}]</a></li>
            [{/if}]
		</ul>
	</div>
[{/if}]

[{if $smarty.get.objID && $menuTreeStickyLinks}]
	<div id="treeTop" class="p10">
		<a href="[{$menuTreeLinksBack}]" class="fr text-bold">
			<img src="[{$dir_images}]icons/silk/arrow_up.png" /> [{isys type="lang" ident="LC__UNIVERSAL__BACK"}]
		</a>

		[{foreach $menuTreeStickyLinks as $identifier => $item}]
		<a href="[{$item.link}]" title="[{$item.title}]"><img src="[{$item.icon}]" alt="[{$item.title}]" /></a>
		[{/foreach}]
	</div>
[{/if}]