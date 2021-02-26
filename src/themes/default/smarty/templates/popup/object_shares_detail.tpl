[{isys_group name="tom"}]
	[{isys_group name="details"}]
		<div id="object_details">	
			<div style="text-align:center;clear: both">
				 <br />
				 [{if $objID || $sharesID}]
				 	[{assign var="buttonDisabled" value="0"}]
				 [{else}]
				 	[{assign var="buttonDisabled" value="1"}]
				 [{/if}]
				 
				 
	       		 [{isys
	 	 			p_bDisabled="$buttonDisabled"
					p_strAccessKey="s"
	 	 			type="f_button"
	 				type="f_button"
	 				id="BUTTON_SAVE"
	 				p_onClick="move_selection_to_parent('selFull', 'selID'); close_window();"
					p_strValue="LC__CMDB__BROWSER_OBJECT__BUTTON_SAVE"}]
		 		 [{isys
		 		 	type="f_button"
		 			type="f_button"
		 			p_onClick="close_window();"
		 			p_strValue="LC__CMDB__BROWSER_OBJECT__BUTTON_CANCEL"}]
			</div>	
		</div>
	[{/isys_group}]
[{/isys_group}]		