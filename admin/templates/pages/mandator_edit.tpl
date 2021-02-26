<form id="edit_form" action="" method="post">
	<fieldset>
		<legend class="bold text-shadow">Edit Tenant "[{$mandator_data.isys_mandator__title}]"</legend>

		<table cellpadding="2" cellspacing="0" width="100%" class="sortable mt10">
			<colgroup>
				<col width="350" />
			</colgroup>
			<tr>
				<th colspan="2">
					<span>Tenant settings</span>
				</th>
			</tr>
			<tr>
				<td class="bold">
					<label for="mandator_title">
						Tenant GUI title
					</label>
				</td>
				<td>
					<input type="text" id="mandator_title" name="mandator_title" value="[{$mandator_data.isys_mandator__title}]" />
				</td>
			</tr>
			<tr>
				<td class="bold">
					<label for="mandator_description">
						Description
					</label>
				</td>
				<td>
					<textarea cols="45" rows="10" id="mandator_description" name="mandator_description">[{$mandator_data.isys_mandator__description}]</textarea>
				</td>
			</tr>
			<tr>
				<td class="bold">
					<label for="mandator_sort">
						Sort value
					</label>
				</td>
				<td>
					<input type="text" id="mandator_sort" name="mandator_sort" value="[{$mandator_data.isys_mandator__sort}]" />
				</td>
			</tr>
			<tr>
				<td class="bold">
					<label for="mandator_cache_dir">
						Cache dir
					</label>
				</td>
				<td>
					<input type="text" id="mandator_cache_dir" name="mandator_cache_dir" value="[{$mandator_data.isys_mandator__dir_cache|substr:6}]" /> [{* substr:6 is used to remove the "cache_" prefix *}]
				</td>
			</tr>
			<tr>
				<th colspan="2">
					<span class="fr red">Check your MySQL connection before changing this. Note that the MySQL user is NOT an i-doit login!</span>
					<span>MySQL settings</span>
				</th>
			</tr>
			<tr>
				<td class="bold">
					<label for="mandator_db_host">
						Host
					</label>
				</td>
				<td>
					<input type="text" id="mandator_db_host" name="mandator_db_host" value="[{$mandator_data.isys_mandator__db_host}]" />
				</td>
			</tr>
			<tr>
				<td class="bold">
					<label for="mandator_db_port">
						Port
					</label>
				</td>
				<td>
					<input type="text" id="mandator_db_port" name="mandator_db_port" value="[{$mandator_data.isys_mandator__db_port}]" />
				</td>
			</tr>
			<tr>
				<td class="bold">
					<label for="mandator_database">
						Database Name
					</label>
				</td>
				<td>
					<input onblur="CheckDatabaseName(this, 'Be aware that the database name only allow the characters 0-9, a-Z and _. Please correct your value.', '[{$mandator_data.isys_mandator__db_name}]'); return false;" type="text" id="mandator_database" name="mandator_database" placeholder="[{$mandator_data.isys_mandator__db_name}]" value="[{$mandator_data.isys_mandator__db_name}]" />
					(0-9, a-z, A-Z and _)
				</td>
			</tr>
			<tr>
				<td class="bold">
					<label for="mandator_username">
						Username (max. 16 & no special chars)
					</label>
				</td>
				<td>
					<input onblur="CheckDatabaseName(this, 'Your username has got special charactes. Only a-z & A-Z is allowed here. Please correct your value.', '[{$mandator_data.isys_mandator__db_user}]'); return false;" type="text" id="mandator_username" name="mandator_username" placeholder="[{$mandator_data.isys_mandator__db_user}]" value="[{$mandator_data.isys_mandator__db_user}]" />
					(a-z A-Z)
				</td>
			</tr>
			<tr>
				<td class="bold">
					<label for="mandator_password">
						Password
					</label>
				</td>
				<td>
					<input type="password" onfocus="if (this.value='***')this.value='';$('pw_2').show();$('change_pass').value='1';" name="mandator_password" id="mandator_password" value="***" />
					<input type="hidden" name="change_pass" id="change_pass" value="0" />
				</td>
			</tr>
			<tr style="display:none;" id="pw_2">
				<td class="bold">
					<label for="mandator_password2">Retype password</label>
				</td>
				<td>
					<input type="password" name="mandator_password2" id="mandator_password2" value="" />
				</td>
			</tr>
		</table>

		<input type="hidden" name="id" value="[{$mandator_data.isys_mandator__id}]" />

		<div class="toolbar">
			<a class="bold" href="javascript:" onclick="save_mandator();"> Save changes</a>
			<a class="bold" href="javascript:" onclick="new Effect.SlideUp('mandator_edit', {duration:0.3});new Effect.Appear('mandators',{duration:0.4});"> Abort</a>
			<img src="../images/ajax-loading.gif" style="margin-top:1px;margin-left:5px;display:none;" id="edit_loading" />
		</div>
	</fieldset>
</form>