<div id="store-categories">
	{foreach from=$categories item=parent_category}
		<div id="{$parent_category.permalink}">
			<h1>{$parent_category.name}</h1>
		</div>
		<ul>
			{foreach from=$parent_category.subcategories item=subcategory}
				<!--li><a href="/store/category/{$subcategory.name|regex_replace:'/&[a-z]+;/':''|replace:'  ':' '|strip_tags:false|lower|replace:' ':'-'}">{$subcategory.name}</a></li-->
				<li {if $subcategory.name == $category.name}class="selected"{/if}><a href="/store/category/{$subcategory.permalink}">{$subcategory.name}</a></li>
			{/foreach}
		</ul><br />
	{/foreach}
</div>