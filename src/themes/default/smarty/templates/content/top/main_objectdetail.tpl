[{assign var=contentTop value=$smarty.session.viewMode.contentTop}]

<div id="aj_result"></div>

<div id="contentHeader">
	<div class="contentHeaderImage fl">
		[{isys type="object_image"}]
	</div>

	<div class="qr-code fr m5">
		[{if defined('C__MODULE__PRO') && constant('C__MODULE__PRO') && isys_tenantsettings::get('barcode.enabled', 1)}]
			[{if isys_tenantsettings::get('barcode.type', 'qr') === "qr"}]
				[{if $show_qr_code === true}]

					[{if $qr_code_link === $smarty.const.C__QRCODE__LINK__IQR}]
						<a href="[{$qr_code_iqr_url}]">
							<img style="border:1px solid #999; height:85px;width:85px;" id="barcode" src="[{$qr_code_src_img}]" border="0" />
						</a>
					[{else}]
						<a href="javascript:" onclick="var w = window.open('[{$qr_code_src_popup}]', 'QR', 'width=640, height=480, dependant=yes, toolbar=no, location=no, status=no, menubar=no'); w.focus();">
							<img style="border:1px solid #999; height:85px;width:85px;" id="barcode" src="[{$qr_code_src_img}]" border="0" />
						</a>
					[{/if}]
				[{/if}]
			[{else}]
				<a href="javascript:" onclick="var w = window.open('[{isys_application::instance()->www_path}]src/tools/php/barcode_window.php?height=65&barcode=[{isys type="f_data" name="C__CATG__SYSID" p_plain=1}]', 'Barcode', 'width=640, height=480, dependant=yes, toolbar=no, location=no, status=no, menubar=no'); w.focus();">
					<img id="barcode" class="mt5" src="[{isys_application::instance()->www_path}]src/tools/php/barcode.php?height=65&barcode=[{isys type="f_data" name="C__CATG__SYSID" p_plain=1}]" border="0" />
				</a>
			[{/if}]
		[{/if}]
	</div>

	<div id="contentHeaderTable" class="m0 p5">

		<h1 id="contentTopTitle">
			[{if $content_title != ""}]
				[{$content_title|escape}]: [{isys type="f_data" name="C__CATG__TITLE" p_bInfoIconSpacer="0" len=50}] [{if $categoryTitle}]([{$categoryTitle|escape}])[{else}]-[{/if}]
			[{else}]
				[{isys type="f_data" name="C__CATG__TITLE" p_bInfoIconSpacer="0" len=50}] [{if $categoryTitle}]([{$categoryTitle|escape}])[{else}]-[{/if}]
			[{/if}]

			[{if isset($g_locked)}]<span class="red">- LOCKED ([{$lock_user}]) -</span>[{/if}]
		</h1>

		<table id="contentHeaderInnerTable" class="border-none mt5" style="[{if $contentTop == "off"}]display:none;[{/if}]" cellpadding="2px" cellspacing="0px;">
			<tr>
				<td>[{isys type="lang" ident="LC__CMDB__CATG__SYSID" p_bInfoIconSpacer="0"}]</td>
				<td><strong>[{isys type="f_data" name="C__CATG__SYSID" p_bInfoIconSpacer="0"}]</strong></td>
				<td class="pl20">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION"}]</td>
				<td><strong>[{isys type="f_data" name="C__CATG__LOCATION" p_bInfoIconSpacer="0"}]</strong></td>
			</tr>
			<tr>
				<td>[{isys type="lang" ident="LC__CMDB__CATG__PURPOSE"}]</td>
				<td><strong>[{isys type="f_data" name="C__CATG__PURPOSE" p_bInfoIconSpacer="0"}]</strong></td>
				<td class="pl20">[{isys type="lang" ident="LC__CMDB__CATG__CONTACT"}]</td>
				<td><strong>[{isys type="f_data" name="C__CATG__CONTACT" p_bInfoIconSpacer="0"}]</strong></td>
			</tr>
			<tr>
				<td>[{isys type="lang" ident="LC__CMDB__CATG__RELATION"}]</td>
				<td><strong>[{isys type="f_data" name="C__CATG__RELATIONS" p_bInfoIconSpacer="0"}]</strong></td>
				<td class="pl20">[{isys type="lang" ident="LC__OBJECTDETAIL__ACCESS"}]</td>
				<td><strong>[{isys type="f_data" name="C__CATG__ACCESS" p_bInfoIconSpacer="0"}]</strong></td>
			</tr>
		</table>

		[{if isset($index_includes.contenttopobjectdetail)}]
			[{if is_array($index_includes.contenttopobjectdetail)}]
				[{foreach from=$index_includes.contenttopobjectdetail item=template}]
					[{include file=$template}]
				[{/foreach}]
			[{else}]
				[{include file=$index_includes.contenttopobjectdetail}]
			[{/if}]
		[{/if}]
	</div>
</div>

<script type="text/javascript">
	[{if $contentTop == "off"}]
		$('object_image_header').morphed = true;
		if ($('barcode')) $('barcode').hide();
	[{/if}]
</script>
