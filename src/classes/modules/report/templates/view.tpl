[{if $viewTemplate}]
	[{include file=$viewTemplate}]
[{else}]
	<table class="w100">
		<tbody>
			[{$viewContent}]
		</tbody>
	</table>
[{/if}]
