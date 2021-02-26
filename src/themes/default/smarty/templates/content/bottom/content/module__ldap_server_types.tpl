[{if isset($g_list)}]
	[{$g_list}]
[{else}]
	<h3 class="p5 gradient">[{isys type="lang" ident="LC__CMDB__TREE__SYSTEM__INTERFACE__LDAP__DIRECTORIES"}]</h3>
	<fieldset class="overview">
		<legend><span>General</span></legend>
		<br/>
		<table class="contentTable">
			<tr>
				<td class="key">[{isys type="lang" ident="LC__UNIVERSAL__TITLE"}]</td>
				<td class="value">[{isys type="f_text" name="C__MODULE__LDAP_TYPE__TITLE"}]</td>
			</tr>
			<tr>
				<td class="key">[{isys type="lang" ident="LC__UNIVERSAL__CONSTANT"}]</td>
				<td class="value">[{isys type="f_text" name="C__MODULE__LDAP_TYPE__CONST"}]</td>
			</tr>
		</table>
	</fieldset>

	<fieldset class="overview">
		<legend><span>Attribute-Mappings</span></legend>
		<br/>
		<table class="contentTable">
			<tr>
				<td class="key"><strong>i-doit</strong></td>
				<td class="value">[{isys type="f_data" name="C__MODULE__LDAP_TYPE__TITLE"}]</td>
			</tr>
			<tr>
				<td class="key">Username</td>
				<td class="value">[{isys type="f_text" name="LDAP_MAP__USERNAME" p_strTitle="defaultcn"}]</td>
			</tr>
			<tr>
				<td class="key">Groups</td>
				<td class="value">[{isys type="f_text" name="LDAP_MAP__GROUP"}]</td>
			</tr>
			<tr>
				<td class="key">Firstname</td>
				<td class="value">[{isys type="f_text" name="LDAP_MAP__GIVENNAME"}]</td>
			</tr>
			<tr>
				<td class="key">Lastname</td>
				<td class="value">[{isys type="f_text" name="LDAP_MAP__SURNAME"}]</td>
			</tr>
			<tr>
				<td class="key">Mail address</td>
				<td class="value">[{isys type="f_text" name="LDAP_MAP__MAIL"}]</td>
			</tr>
			<tr>
				<td class="key">Object class</td>
				<td class="value">[{isys type="f_text" name="LDAP_MAP__OBJECTCLASS"}]</td>
			</tr>
		</table>
	</fieldset>

<input type="hidden" name="id" value="[{$dirID}]">

<div id="ajax_return" class="m5 p5" style="display:none;"></div>
[{/if}]