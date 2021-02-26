[{isys_group name="content"}]
<table class="contentTable" id="table_cluster_options">
	<tr>
		<td class="key" style="vertical-align: top;">
			<p style="margin-top:4px;">
				[{isys
					type="f_label"
                    name="C__CMDB__CATG__CLUSTER_SERVICE__HOST_ADDRESSES"
					ident="LC__CMDB__CATG__CLUSTER_SERVICE__HOST_ADDRESSES"}]
			</p>
		</td>
		<td class="value" style="vertical-align: top;">
			[{isys
				type="f_popup"
				p_strPopupType="browser_cat_data"
				p_strSelectedID=$smarty.post.application_id|strip_tags|escape
				p_preSelection=$ip_preselection
				dataretrieval="isys_cmdb_dao_category_g_ip::catdata_browser"
				name="C__CMDB__CATG__CLUSTER_SERVICE__HOST_ADDRESSES"
				title="LC__POPUP__BROWSER__IP_TITLE"}]
		</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">
			[{isys
                type='f_label'
                name='C__CMDB__CATG__CLUSTER_SERVICE__DRIVES'
                ident="LC__CMDB__CATG__CLUSTER_SERVICE__VOLUMES"}]
		</td>
		<td class="value" style="vertical-align: top;">
			[{isys
				type="f_popup"
				p_strPopupType="browser_cat_data"
				p_strSelectedID=$smarty.post.application_id|strip_tags|escape
				p_preSelection=$drive_preselection
				dataretrieval="isys_cmdb_dao_category_g_drive::catdata_browser"
				name="C__CMDB__CATG__CLUSTER_SERVICE__DRIVES"
				title="LC__POPUP__BROWSER__DRIVE_TITLE"}]
		</td>
	</tr>
	<tr>
		<td class="key" style="vertical-align: top;">
			[{isys
                type="f_label"
                name="C__CMDB__CATG__CLUSTER_SERVICE__SHARES"
                ident="LC__CMDB__CATG__CLUSTER_SERVICE__SHARES"}]
		</td>
		<td class="value">
			[{isys
				type="f_popup"
				p_strPopupType="browser_cat_data"
				p_strSelectedID=$smarty.post.application_id|strip_tags|escape
				p_preSelection=$preselectionShares
				dataretrieval="isys_cmdb_dao_category_g_shares::catdata_browser"
				name="C__CMDB__CATG__CLUSTER_SERVICE__SHARES"
				title="LC__POPUP__BROWSER__SHARE_TITLE"}]
		</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__CLUSTER_SERVICE_DATABASE_SCHEMATA__VIEW" ident="LC__CMDB__CATS__DATABASE_GATEWAY__TARGET_SCHEMA"}]</td>
		<td class="value">
			[{isys
			title="LC__BROWSER__TITLE__DATABASE_SCHEMATA"
			p_strSelectedID=$preselectionDBMS
			name="C__CATG__CLUSTER_SERVICE_DATABASE_SCHEMATA"
			type="f_popup"
			catFilter="C__CATS__DATABASE_SCHEMA"
			p_strPopupType="browser_object_ng"}]
		</td>
	</tr>
</table>
[{/isys_group}]