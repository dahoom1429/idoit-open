[{if $smarty.get.pid eq "2"}]
	[{if $g_list}]
		[{$g_list}]
	[{else}]
		[{isys type='lang' ident='LC__CMDB__EXPORT__NO_TEMPLATES'}]
	[{/if}]

	[{if $note}]
	<br />
	<div class="p5 m5 box-green bold">[{$note}]</div>
	[{/if}]

[{else}]
<h2 class="gradient p10">[{isys type='lang' ident='LC__CMDB__EXPORT'}]</h2>

<fieldset class="overview">
	<legend><span>[{isys type='lang' ident='LC__UNIVERSAL__STEP_1'}]: [{isys type='lang' ident='LC__CMDB__EXPORT__CHOICE'}]</span></legend>

	<div class="p10">
		<label class="m5 display-block">
			<input type="radio" checked="checked" name="export_filter" value="1" /> [{isys type='lang' ident='LC__CMDB__EXPORT__CHOICE__SELECTED_OBJECTS'}]
		</label>
		<label class="m5 display-block">
			<input type="radio" name="export_filter" value="2" /> [{isys type='lang' ident='LC__CMDB__EXPORT__CHOICE__OBJECTS_BY_TYPES'}]
		</label>
		<label class="m5 display-block">
			<input type="radio" name="export_filter" value="3" /> [{isys type='lang' ident='LC__CMDB__EXPORT__CHOICE__OBJECTS_BY_LOCATION'}]
		</label>

		<input type="hidden" id="step" name="step" value="2" />

		<input class="btn" type="submit" value="[{isys type='lang' ident='LC__UNIVERSAL__BUTTON_NEXT'}]" />

		[{if $note}]
		<br /><br />
		<div class="p5 m5 box-green bold">[{$note}]</div>
		[{/if}]
	</div>
</fieldset>
[{/if}]

