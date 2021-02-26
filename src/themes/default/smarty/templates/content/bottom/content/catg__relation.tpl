<style type="text/css">
	.relation-border {
		background-color:#eee;
		border-left: 1px solid #ddd;
		border-right: 1px solid #ddd;
		padding:10px 20px 10px 0;
		border-bottom:none;
	}
</style>

[{if $view == "relation"}]
	<div style="margin:20px 20px 20px 5px;">
		<div class="fl">
			<span class="text-bold">[{isys type="lang" ident="LC_UNIVERSAL__OBJECT"}] 1: [{$master}]</span>
		</div>
		<div class="fl">
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[{$relation_type_description}]&nbsp;&nbsp;&nbsp;&nbsp;
		</div>
		<div>
			<span class="text-bold">[{isys type="lang" ident="LC_UNIVERSAL__OBJECT"}] 2: [{$slave}]</span>
		</div>
	</div>
[{elseif $view == "it_service"}]
	<div class="text-shadow border-bottom">
		<div class="fl p10 border-right bg-white" style="width:33%; box-sizing: border-box;">
			<p class="text-bold mb5">[{isys type="lang" ident="LC__CATG__RELATION__IT_SERVICE_COMPONENT"}] 1</p>
			[{isys type="f_dialog" name="C__CATG__RELATION_MASTER" inputGroupMarginClass=""}]
		</div>
		<div class="fl p10 bg-lightgrey" style="width:34%; box-sizing: border-box;">
			<p class="text-bold mb5">&nbsp;</p>
			[{isys type="f_dialog" p_bDbFieldNN="1" p_bInfoIconSpacer="0" name="C__CATG__RELATION__DIRECTION" p_strClass="input-block" inputGroupMarginClass=""}]
		</div>
		<div class="fl p10 border-left bg-white" style="width:33%; box-sizing: border-box;">
			<p class="text-bold mb5">[{isys type="lang" ident="LC__CATG__RELATION__IT_SERVICE_COMPONENT"}] 2</p>
			[{isys type="f_dialog" name="C__CATG__RELATION_SLAVE__HIDDEN" p_bInfoIconSpacer=0 p_strClass="input-block" inputGroupMarginClass=""}]
		</div>
		<br class="cb" />
	</div>
	<input type="hidden" name="C__CATG__RELATION__ITSERVICE" value="[{$it_service}]">
[{else}]
	<div class="border-bottom">
		<table class="w100 no-border-spacing">
			<tr>
				<td class="p10 border-right bg-white" style="width:33%">
					<p class="text-bold mb5">[{isys type="lang" ident="LC_UNIVERSAL__OBJECT"}] 1</p>

					[{isys name="C__CATG__RELATION_MASTER" p_bDbFieldNN=1 type="f_dialog" p_bInfoIconSpacer=0 p_strClass="input-block" inputGroupMarginClass=""}]
                    [{$hidden_relation_master}]
				</td>
				<td class="p10 border-right bg-lightgrey" style="width:33%">
					<p class="mb5">&nbsp;</p>

					[{isys type="f_dialog" p_bDbFieldNN="1" p_bInfoIconSpacer="0" name="C__CATG__RELATION__DIRECTION" p_strClass="input-block" inputGroupMarginClass=""}]
					<input type="hidden" name="C__RELATION__DIRECTION__CHANGED" id="C__RELATION__DIRECTION__CHANGED" value="0">
					[{$hidden_relation_direction}]
				</td>
				<td class="p10 bg-white" style="width:33%">
					<p class="text-bold mb5">[{isys type="lang" ident="LC_UNIVERSAL__OBJECT"}] 2</p>

					[{isys
						name="C__CATG__RELATION_SLAVE"
						type="f_popup"
						p_strPopupType="browser_object_ng"
						p_bDisableDetach=1
						p_bInfoIconSpacer="0"
						p_strClass="input-block"
						inputGroupMarginClass=""}]
					[{$hidden_relation_slave}]
				</td>
			</tr>
		</table>
	</div>
[{/if}]

<table class="contentTable mt20">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__RELATION__RELATION_TYPE' ident="LC__CATG__RELATION__RELATION_TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_bDbFieldNN="1" p_strPopupType="relation_type" name="C__CATG__RELATION__RELATION_TYPE"}]
		[{if $relation_type > 0}]
			<input type="hidden" name="C__CATG__RELATION__RELATION_TYPE" value="[{$relation_type}]">
		[{/if}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__RELATION__WEIGHTING' ident="LC__CATG__RELATION__WEIGHTING"}]</td>
		<td class="value">[{isys type="f_dialog" p_bDbFieldNN="1" name="C__CATG__RELATION__WEIGHTING" p_bSort=false}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__RELATION__ITSERVICE' ident='LC__CMDB__CATG__IT_SERVICE'}]</td>
		<td class="value">[{isys name="C__CATG__RELATION__ITSERVICE" p_bDbFieldNN="1" type="f_dialog"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='lang' ident="LC__CMDB__CATG__RELATION__RELATION_OBJECT"}]</td>
		<td class="value pl20"><a href="[{$relation_object.link}]">[{$relation_object.title}]</a></td>
	</tr>
</table>
<script type="text/javascript">
    (function () {
        "use strict";

        if($('C__CATG__RELATION__DIRECTION'))
        {
            var $changedDirection = $('C__RELATION__DIRECTION__CHANGED');
            // Set Flag if direction has been changed
            $('C__CATG__RELATION__DIRECTION').on('change', function () {
	            $changedDirection.setValue(parseInt($changedDirection.getValue()) ? 0 : 1);
            });
        }
    })();
</script>

[{if $sibling_list}]
	<fieldset class="overview">
		<legend><span>[{isys type="lang" ident="LC__PARALLEL_RELATIONS__ALIGNED_TO"}]</span></legend>

		<div class="p10">
			[{$sibling_list}]
		</div>

		<hr />
	</fieldset>
[{/if}]

<style type="text/css">
	#scroller > div > table > tbody > tr > td:nth-child(4) > a > img {
		margin-top:4px;
	}
</style>