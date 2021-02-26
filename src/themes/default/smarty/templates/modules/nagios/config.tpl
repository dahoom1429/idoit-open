<style type="text/css">
	#nagios-config ul.multiconfig {
		[{if isys_glob_is_edit_mode()}]
		list-style: none;
		margin: 0;
		[{else}]
		list-style:square;
		margin:5px 0 5px 15px;
		[{/if}]
		padding: 0;
	}

	[{if isys_glob_is_edit_mode()}]
	#nagios-config ul.multiconfig li {
		margin-top: 3px;
	}
	[{/if}]
</style>

<table id="nagios-config" class="contentTable">
	<tr>
	    <td class="key">[{isys type="f_label" name="C__MODULE__NAGIOS__PERSON_NAME_OPTION" ident="LC__MODULE__NAGIOS__CONFIG__PERSON_NAME_OPTION"}]</td>
	    <td class="value">[{isys type="f_dialog" name="C__MODULE__NAGIOS__PERSON_NAME_OPTION" p_bDbFieldNN=1}]</td>
	</tr>
	<tr>
	    <td colspan="2">
	        <hr class="mb5 mt5"/>
	    </td>
	</tr>
	<tr>
	    <td class="key">log_file</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__log_file"}]</td>
	</tr>
	<tr>
	    <td class="key">object_cache_file</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__object_cache_file"}]</td>
	</tr>
	<tr>
	    <td class="key">precached_object_file</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__precached_object_file"}]</td>
	</tr>
	<tr>
		<td class="key">resource_file</td>
		<td class="value">
			[{if isys_glob_is_edit_mode()}]
			[{isys type="f_text" id="resource_file-textfield" p_strStyle="padding-top: 3px; padding-bottom: 5px;"}]
			<button type="button" id="new_resource_file" class="btn ml20">[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}] <img src="[{$dir_images}]icons/silk/add.png" alt="+" title="[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]" /></button>
			[{/if}]

			<div id="resource_file-container" style="padding-left:20px;">
				<ul id="resource_file-list" class="multiconfig">
					[{foreach from=$resource_files item="module"}]
					<li>
						[{if isys_glob_is_edit_mode()}]<button class="btn btn-small" type="button"><img src="[{$dir_images}]icons/silk/delete.png" alt="x" title="[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]" /></button>[{/if}]
						<input type="hidden" value="[{$module}]" name="C__MODULE__NAGIOS__resource_file[]" />[{$module}]
					</li>
					[{/foreach}]
				</ul>
			</div>
		</td>
	</tr>
	<tr>
	    <td class="key">temp_file</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__temp_file"}]</td>
	</tr>
	<tr>
	    <td class="key">temp_path</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__temp_path"}]</td>
	</tr>
	<tr>
	    <td class="key">status_file</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__status_file"}]</td>
	</tr>
	<tr>
	    <td class="key">status_update_interval</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__status_update_interval"}]</td>
	</tr>
	<tr>
	    <td class="key">nagios_user</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__nagios_user"}]</td>
	</tr>
	<tr>
	    <td class="key">nagios_group</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__nagios_group"}]</td>
	</tr>
	<tr>
	    <td class="key">enable_notifications</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__enable_notifications"}]</td>
	</tr>
	<tr>
	    <td class="key">execute_service_checks</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__execute_service_checks"}]</td>
	</tr>
	<tr>
	    <td class="key">accept_passive_service_checks</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__accept_passive_service_checks"}]</td>
	</tr>
	<tr>
	    <td class="key">execute_host_checks</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__execute_host_checks"}]</td>
	</tr>
	<tr>
	    <td class="key">accept_passive_host_checks</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__accept_passive_host_checks"}]</td>
	</tr>
	<tr>
	    <td class="key">enable_event_handlers</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__enable_event_handlers"}]</td>
	</tr>
	<tr>
	    <td class="key">log_rotation_method</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__log_rotation_method"}]</td>
	</tr>
	<tr>
	    <td class="key">log_archive_path</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__log_archive_path"}]</td>
	</tr>
	<tr>
	    <td class="key">check_external_commands</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__check_external_commands"}]</td>
	</tr>
	<tr>
	    <td class="key">command_check_interval</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__command_check_interval"}]</td>
	</tr>
	<tr>
	    <td class="key">command_file</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__command_file"}]</td>
	</tr>
	<tr>
	    <td class="key">external_command_buffer_slots</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__external_command_buffer_slots"}]</td>
	</tr>
	<tr>
	    <td class="key">lock_file</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__lock_file"}]</td>
	</tr>
	<tr>
	    <td class="key">retain_state_information</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__retain_state_information"}]</td>
	</tr>
	<tr>
	    <td class="key">state_retention_file</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__state_retention_file"}]</td>
	</tr>
	<tr>
	    <td class="key">retention_update_interval</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__retention_update_interval"}]</td>
	</tr>
	<tr>
	    <td class="key">use_retained_program_state</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__use_retained_program_state"}]</td>
	</tr>
	<tr>
	    <td class="key">use_retained_scheduling_info</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__use_retained_scheduling_info"}]</td>
	</tr>
	<tr>
	    <td class="key">retained_host_attribute_mask</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__retained_host_attribute_mask"}]</td>
	</tr>
	<tr>
	    <td class="key">retained_service_attribute_mask</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__retained_service_attribute_mask"}]</td>
	</tr>
	<tr>
	    <td class="key">retained_process_host_attribute_mask</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__retained_process_host_attribute_mask"}]</td>
	</tr>
	<tr>
	    <td class="key">retained_process_service_attribute_mask</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__retained_process_service_attribute_mask"}]</td>
	</tr>
	<tr>
	    <td class="key">retained_contact_host_attribute_mask</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__retained_contact_host_attribute_mask"}]</td>
	</tr>
	<tr>
	    <td class="key">retained_contact_service_attribute_mask</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__retained_contact_service_attribute_mask"}]</td>
	</tr>
	<tr>
	    <td class="key">use_syslog</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__use_syslog"}]</td>
	</tr>
	<tr>
	    <td class="key">log_notifications</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__log_notifications"}]</td>
	</tr>
	<tr>
	    <td class="key">log_service_retries</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__log_service_retries"}]</td>
	</tr>
	<tr>
	    <td class="key">log_host_retries</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__log_host_retries"}]</td>
	</tr>
	<tr>
	    <td class="key">log_event_handlers</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__log_event_handlers"}]</td>
	</tr>
	<tr>
	    <td class="key">log_initial_states</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__log_initial_states"}]</td>
	</tr>
	<tr>
	    <td class="key">log_external_commands</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__log_external_commands"}]</td>
	</tr>
	<tr>
	    <td class="key">log_passive_checks</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__log_passive_checks"}]</td>
	</tr>
	<tr>
	    <td class="key">global_host_event_handler</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__global_host_event_handler"}]</td>
	</tr>
	<tr>
	    <td class="key">global_service_event_handler</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__global_service_event_handler"}]</td>
	</tr>
	<tr>
	    <td class="key">sleep_time</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__sleep_time"}]</td>
	</tr>
	<tr>
	    <td class="key">service_inter_check_delay_method</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__service_inter_check_delay_method"}]</td>
	</tr>
	<tr>
	    <td class="key">max_service_check_spread</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__max_service_check_spread"}]</td>
	</tr>
	<tr>
	    <td class="key">service_interleave_factor</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__service_interleave_factor"}]</td>
	</tr>
	<tr>
	    <td class="key">max_concurrent_checks</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__max_concurrent_checks"}]</td>
	</tr>
	<tr>
	    <td class="key">check_result_reaper_frequency</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__check_result_reaper_frequency"}]</td>
	</tr>
	<tr>
	    <td class="key">max_check_result_reaper_time</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__max_check_result_reaper_time"}]</td>
	</tr>
	<tr>
	    <td class="key">check_result_path</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__check_result_path"}]</td>
	</tr>
	<tr>
	    <td class="key">max_check_result_file_age</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__max_check_result_file_age"}]</td>
	</tr>
	<tr>
	    <td class="key">host_inter_check_delay_method</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__host_inter_check_delay_method"}]</td>
	</tr>
	<tr>
	    <td class="key">max_host_check_spread</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__max_host_check_spread"}]</td>
	</tr>
	<tr>
	    <td class="key">interval_length</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__interval_length"}]</td>
	</tr>
	<tr>
	    <td class="key">auto_reschedule_checks</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__auto_reschedule_checks"}]</td>
	</tr>
	<tr>
	    <td class="key">auto_rescheduling_interval</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__auto_rescheduling_interval"}]</td>
	</tr>
	<tr>
	    <td class="key">auto_rescheduling_window</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__auto_rescheduling_window"}]</td>
	</tr>
	<tr>
	    <td class="key">use_aggressive_host_checking</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__use_aggressive_host_checking"}]</td>
	</tr>
	<tr>
	    <td class="key">translate_passive_host_checks</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__translate_passive_host_checks"}]</td>
	</tr>
	<tr>
	    <td class="key">passive_host_checks_are_soft</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__passive_host_checks_are_soft"}]</td>
	</tr>
	<tr>
	    <td class="key">enable_predictive_host_dependency_checks</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__enable_predictive_host_dependency_checks"}]</td>
	</tr>
	<tr>
	    <td class="key">enable_predictive_service_dependency_checks</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__enable_predictive_service_dependency_checks"}]</td>
	</tr>
	<tr>
	    <td class="key">cached_host_check_horizon</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__cached_host_check_horizon"}]</td>
	</tr>
	<tr>
	    <td class="key">cached_service_check_horizon</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__cached_service_check_horizon"}]</td>
	</tr>
	<tr>
	    <td class="key">use_large_installation_tweaks</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__use_large_installation_tweaks"}]</td>
	</tr>
	<tr>
	    <td class="key">free_child_process_memory</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__free_child_process_memory"}]</td>
	</tr>
	<tr>
	    <td class="key">child_processes_fork_twice</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__child_processes_fork_twice"}]</td>
	</tr>
	<tr>
	    <td class="key">enable_environment_macros</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__enable_environment_macros"}]</td>
	</tr>
	<tr>
	    <td class="key">enable_flap_detection</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__enable_flap_detection"}]</td>
	</tr>
	<tr>
	    <td class="key">low_service_flap_threshold</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__low_service_flap_threshold"}]</td>
	</tr>
	<tr>
	    <td class="key">high_service_flap_threshold</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__high_service_flap_threshold"}]</td>
	</tr>
	<tr>
	    <td class="key">low_host_flap_threshold</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__low_host_flap_threshold"}]</td>
	</tr>
	<tr>
	    <td class="key">high_host_flap_threshold</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__high_host_flap_threshold"}]</td>
	</tr>
	<tr>
	    <td class="key">soft_state_dependencies</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__soft_state_dependencies"}]</td>
	</tr>
	<tr>
	    <td class="key">service_check_timeout</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__service_check_timeout"}]</td>
	</tr>
	<tr>
	    <td class="key">host_check_timeout</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__host_check_timeout"}]</td>
	</tr>
	<tr>
	    <td class="key">event_handler_timeout</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__event_handler_timeout"}]</td>
	</tr>
	<tr>
	    <td class="key">notification_timeout</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__notification_timeout"}]</td>
	</tr>
	<tr>
	    <td class="key">ocsp_timeout</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__ocsp_timeout"}]</td>
	</tr>
	<tr>
	    <td class="key">ochp_timeout</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__ochp_timeout"}]</td>
	</tr>
	<tr>
	    <td class="key">perfdata_timeout</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__perfdata_timeout"}]</td>
	</tr>
	<tr>
	    <td class="key">obsess_over_services</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__obsess_over_services"}]</td>
	</tr>
	<tr>
	    <td class="key">ocsp_command</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__ocsp_command"}]</td>
	</tr>
	<tr>
	    <td class="key">obsess_over_hosts</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__obsess_over_hosts"}]</td>
	</tr>
	<tr>
	    <td class="key">ochp_command</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__ochp_command"}]</td>
	</tr>
	<tr>
	    <td class="key">process_performance_data</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__process_performance_data"}]</td>
	</tr>
	<tr>
	    <td class="key">host_perfdata_command</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__host_perfdata_command"}]</td>
	</tr>
	<tr>
	    <td class="key">service_perfdata_command</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__service_perfdata_command"}]</td>
	</tr>
	<tr>
	    <td class="key">host_perfdata_file</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__host_perfdata_file"}]</td>
	</tr>
	<tr>
	    <td class="key">service_perfdata_file</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__service_perfdata_file"}]</td>
	</tr>
	<tr>
	    <td class="key">host_perfdata_file_template</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__host_perfdata_file_template"}]</td>
	</tr>
	<tr>
	    <td class="key">service_perfdata_file_template</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__service_perfdata_file_template"}]</td>
	</tr>
	<tr>
	    <td class="key">host_perfdata_file_mode</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__host_perfdata_file_mode"}]</td>
	</tr>
	<tr>
	    <td class="key">service_perfdata_file_mode</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__service_perfdata_file_mode"}]</td>
	</tr>
	<tr>
	    <td class="key">host_perfdata_file_processing_interval</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__host_perfdata_file_processing_interval"}]</td>
	</tr>
	<tr>
	    <td class="key">service_perfdata_file_processing_interval</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__service_perfdata_file_processing_interval"}]</td>
	</tr>
	<tr>
	    <td class="key">host_perfdata_file_processing_command</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__host_perfdata_file_processing_command"}]</td>
	</tr>
	<tr>
	    <td class="key">service_perfdata_file_processing_command</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__service_perfdata_file_processing_command"}]</td>
	</tr>
	<tr>
	    <td class="key">check_for_orphaned_services</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__check_for_orphaned_services"}]</td>
	</tr>
	<tr>
	    <td class="key">check_for_orphaned_hosts</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__check_for_orphaned_hosts"}]</td>
	</tr>
	<tr>
	    <td class="key">check_service_freshness</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__check_service_freshness"}]</td>
	</tr>
	<tr>
	    <td class="key">service_freshness_check_interval</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__service_freshness_check_interval"}]</td>
	</tr>
	<tr>
	    <td class="key">check_host_freshness</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__check_host_freshness"}]</td>
	</tr>
	<tr>
	    <td class="key">host_freshness_check_interval</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__host_freshness_check_interval"}]</td>
	</tr>
	<tr>
	    <td class="key">additional_freshness_latency</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__additional_freshness_latency"}]</td>
	</tr>
	<tr>
	    <td class="key">enable_embedded_perl</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__enable_embedded_perl"}]</td>
	</tr>
	<tr>
	    <td class="key">use_embedded_perl_implicitly</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__use_embedded_perl_implicitly"}]</td>
	</tr>
	<tr>
	    <td class="key">date_format</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__date_format"}]</td>
	</tr>
	<tr>
	    <td class="key">use_timezone</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__use_timezone"}]</td>
	</tr>
	<tr>
	    <td class="key">p1_file</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__p1_file"}]</td>
	</tr>
	<tr>
	    <td class="key">illegal_object_name_chars</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__illegal_object_name_chars"}]</td>
	</tr>
	<tr>
	    <td class="key">illegal_macro_output_chars</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__illegal_macro_output_chars"}]</td>
	</tr>
	<tr>
	    <td class="key">use_regexp_matching</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__use_regexp_matching"}]</td>
	</tr>
	<tr>
	    <td class="key">use_true_regexp_matching</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__use_true_regexp_matching"}]</td>
	</tr>
	<tr>
	    <td class="key">admin_email</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__admin_email"}]</td>
	</tr>
	<tr>
	    <td class="key">admin_pager</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__admin_pager"}]</td>
	</tr>
	<tr>
	    <td class="key">event_broker_options</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__event_broker_options"}]</td>
	</tr>
    <tr>
	    <td class="key">broker_module</td>
        <td class="value">
	        [{if isys_glob_is_edit_mode()}]
            [{isys type="f_text" id="broker-textfield" p_strStyle="padding-top: 3px; padding-bottom: 5px;"}]
            <button type="button" id="new_broker" class="btn ml20">[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}] <img src="[{$dir_images}]icons/silk/add.png" alt="+" title="[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]" /></button>
	        [{/if}]

            <div id="broker-container" style="padding-left:20px;">
                <ul id="broker-list" class="multiconfig">
                    [{foreach from=$broker_modules item="module"}]
                    <li>
                        [{if isys_glob_is_edit_mode()}]<button class="btn btn-small" type="button"><img src="[{$dir_images}]icons/silk/delete.png" alt="x" title="[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]" /></button>[{/if}]
	                    <input type="hidden" value="[{$module}]" name="C__MODULE__NAGIOS__broker_module[]" />[{$module}]
                    </li>
                    [{/foreach}]
                </ul>
            </div>
        </td>
	</tr>
	<tr>
	    <td class="key">debug_file</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__debug_file"}]</td>
	</tr>
	<tr>
	    <td class="key">debug_level</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__debug_level"}]</td>
	</tr>
	<tr>
	    <td class="key">debug_verbosity</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__debug_verbosity"}]</td>
	</tr>
	<tr>
	    <td class="key">max_debug_file_size</td>
	    <td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__max_debug_file_size"}]</td>
	</tr>

	<tr>
		<td class="key">cfg_file</td>
		<td class="value">
			[{if isys_glob_is_edit_mode()}]
			[{isys type="f_text" id="cfg_file-textfield" p_strStyle="padding-top: 3px; padding-bottom: 5px;"}]
			<button type="button" id="new_cfg_file" class="btn ml20">[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}] <img src="[{$dir_images}]icons/silk/add.png" alt="+" title="[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]" /></button>
			[{/if}]

			<div id="cfg_file-container" style="padding-left:20px;">
				<ul id="cfg_file-list" class="multiconfig">
					[{foreach from=$cfg_files item="module"}]
					<li>
						[{if isys_glob_is_edit_mode()}]<button class="btn btn-small" type="button"><img src="[{$dir_images}]icons/silk/delete.png" alt="x" title="[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]" /></button>[{/if}]
						<input type="hidden" value="[{$module}]" name="C__MODULE__NAGIOS__cfg_file[]" />[{$module}]
					</li>
					[{/foreach}]
				</ul>
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">cfg_dir</td>
		<td class="value">
			[{if isys_glob_is_edit_mode()}]
			[{isys type="f_text" id="cfg_dir-textfield" p_strStyle="padding-top: 3px; padding-bottom: 5px;"}]
			<button type="button" id="new_cfg_dir" class="btn ml20">[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}] <img src="[{$dir_images}]icons/silk/add.png" alt="+" title="[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]" /></button>
			[{/if}]

			<div id="cfg_dir-container" style="padding-left:20px;">
				<ul id="cfg_dir-list" class="multiconfig">
					<li>[{$object_dir}]</li>
					[{foreach from=$cfg_dirs item="module"}]
					<li>
						[{if isys_glob_is_edit_mode()}]<button class="btn btn-small" type="button"><img src="[{$dir_images}]icons/silk/delete.png" alt="x" title="[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]" /></button>[{/if}]
						<input type="hidden" value="[{$module}]" name="C__MODULE__NAGIOS__cfg_dir[]" />[{$module}]
					</li>
					[{/foreach}]
				</ul>
			</div>
		</td>
	</tr>
	<tr>
		<td class="key">check_for_updates</td>
		<td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__check_for_updates"}]</td>
	</tr>
	<tr>
		<td class="key">bare_update_checks</td>
		<td class="value">[{isys type="f_text" name="C__MODULE__NAGIOS__bare_update_checks"}]</td>
	</tr>
</table>
[{if ($smarty.post.navMode == "2")}]
<script type="text/javascript">
	$('new_resource_file').observe("click", function () {
		var resource_file_textfield = $('resource_file-textfield'),
			resource_file_value = resource_file_textfield.getValue();

		if (! resource_file_value.blank()) {
			$('resource_file-list').insert({
				top: new Element('li').update(new Element('button', {className:'btn btn-small', type:'button'}).update(new Element('img', {src:'[{$dir_images}]icons/silk/delete.png', alt:'x', title:'[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]'})))
					.insert(new Element('input', {type:'hidden', name:'C__MODULE__NAGIOS__resource_file[]', value:resource_file_value}))
					.insert(' ' + resource_file_value)
			});

			resource_file_textfield.setValue('');
		} else {
			resource_file_textfield.highlight({startcolor:'#ff4343', endcolor:'#fbfbfb', restorecolor:'#fbfbfb'});
		}
	});

	$('new_broker').observe("click", function () {
		var broker_textfield = $('broker-textfield'),
			broker_value = broker_textfield.getValue();

		if (! broker_value.blank()) {
			$('broker-list').insert({
				top: new Element('li').update(new Element('button', {className:'btn btn-small', type:'button'}).update(new Element('img', {src:'[{$dir_images}]icons/silk/delete.png', alt:'x', title:'[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]'})))
					.insert(new Element('input', {type:'hidden', name:'C__MODULE__NAGIOS__broker_module[]', value:broker_value}))
					.insert(' ' + broker_value)
			});

			broker_textfield.setValue('');
		} else {
			broker_textfield.highlight({startcolor:'#ff4343', endcolor:'#fbfbfb', restorecolor:'#fbfbfb'});
		}
	});

	$('new_cfg_file').observe("click", function () {
		var cfg_file_textfield = $('cfg_file-textfield'),
			cfg_file_value = cfg_file_textfield.getValue();

		if (! cfg_file_value.blank()) {
			$('cfg_file-list').insert({
				top: new Element('li').update(new Element('button', {className:'btn btn-small', type:'button'}).update(new Element('img', {src:'[{$dir_images}]icons/silk/delete.png', alt:'x', title:'[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]'})))
					.insert(new Element('input', {type:'hidden', name:'C__MODULE__NAGIOS__cfg_file[]', value:cfg_file_value}))
					.insert(' ' + cfg_file_value)
			});

			cfg_file_textfield.setValue('');
		} else {
			cfg_file_textfield.highlight({startcolor:'#ff4343', endcolor:'#fbfbfb', restorecolor:'#fbfbfb'});
		}
	});

	$('new_cfg_dir').observe("click", function () {
		var cfg_dir_textfield = $('cfg_dir-textfield'),
			cfg_dir_value = cfg_dir_textfield.getValue();

		if (! cfg_dir_value.blank()) {
			$('cfg_dir-list').insert({
				top: new Element('li').update(new Element('button', {className:'btn btn-small', type:'button'}).update(new Element('img', {src:'[{$dir_images}]icons/silk/delete.png', alt:'x', title:'[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]'})))
					.insert(new Element('input', {type:'hidden', name:'C__MODULE__NAGIOS__cfg_dir[]', value:cfg_dir_value}))
					.insert(' ' + cfg_dir_value)
			});

			cfg_dir_textfield.setValue('');
		} else {
			cfg_dir_textfield.highlight({startcolor:'#ff4343', endcolor:'#fbfbfb', restorecolor:'#fbfbfb'});
		}
	});

	// This will cover all "remove" buttons on the page.
	$$('ul.multiconfig').invoke('on', 'click', 'li button', function (ev) {
		ev.findElement().up('li').remove()
	});
</script>
[{/if}]