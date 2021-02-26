<style type="text/css">

td.connection {
	background-image:url([{$dir_images}]/cable.png);
	background-repeat: repeat-x;
	height:30px;
	width:15px;
	border:0px solid black;
	clear:none;
}
 
td.connection_object {
	background-image:url([{$dir_images}]/object_r1_c2_s1.png);
	background-repeat: repeat-x;
	height:30px;
	min-width:80px;
	border:0px solid black;
	clear:none;
}

td.start_object {
	background-image:url([{$dir_images}]/start_object_r1_c2_s1.png);
	background-repeat: repeat-x;
	height:30px;
	width:80px;
	border:0px solid black;
	clear:none;
}

td.start_connector {
	background-image:url([{$dir_images}]/start_port1_r1_c2_s1.png);
	background-repeat: repeat-x;
	height:30px;
	min-width:70px;
	border:0px solid black;
	clear:none;
}

</style>

<script>
show_cable_run = function(id){
	if($(id).style.display == "none"){
		 new Effect.Appear(id, {duration:0.2});
	} else{
		new Effect.Fade(id, { duration:0.2});
	}
}
</script>

<table>
[{if $viewContent}]
[{foreach from=$viewContent item=object key=object_id}]
	[{if is_array($object.connection) && count($object.connection) > 0}]
	<tr style="display:; border: 1px solid #000000;height:30px;" id="column_[{$object_id}]" bgcolor="#FFFFFF">
		<td style="height:30px;">
	[{foreach from=$object.connection item=connector key=connector_id}]
		
	<!-- CABLE RUN  -->
		<table cellpadding="0" cellspacing="0" style="border-style:collapse;border:0px solid #000000;clear:none;">
			<tr style="border:0px solid black;height:32px;margin:5px;" align="center">
			
				<td style="background-image:url([{$dir_images}]/start_object_r1_c1_s1.png);width:5px;background-repeat: no-repeat;">
				</td>
				<td class="start_object">
					[{$connector.object_title}]
				</td>
				
				<td style="background-image:url([{$dir_images}]/start_port1_r1_c1_s1.png);width:5px;background-repeat: no-repeat;">
				</td>				
				<td class="start_connector" align="right" title="[{if $connector.type == "2"}][{isys type="lang" ident="LC__CATG__CONNECTOR__OUTPUT"}][{else}][{isys type="lang" ident="LC__CATG__CONNECTOR__INPUT"}][{/if}]">
					[{$connector.title}]
				</td>
				<td style="background-image:url([{$dir_images}]/object_r1_c3_s1.png);width:5px;background-repeat: no-repeat;">				
				</td>
				
				<td class="connection">					
				</td>
				[{assign var=object_count value=$connector.connection|@count}]
				[{assign var=counter value=1}]
			[{foreach from=$connector.connection item=conn key=conn_title}]
				
				[{if $counter == $object_count}]
					<td style="background-image:url([{$dir_images}]/start_object_r1_c1_s1.png);width:5px;background-repeat:no-repeat;">				
					</td>
					<td class="start_object">
						[{$conn.title}]
					<td style="background-image:url([{$dir_images}]/start_port1_r1_c1_s1.png);width:5px;background-repeat:no-repeat;">				
					</td>				
					<td class="start_connector" align="right" title="[{if $conn.connection[1].cable_set === false}][{isys type="lang" ident="LC__CATG__CONNECTOR__INPUT"}][{else}][{isys type="lang" ident="LC__CATG__CONNECTOR__OUTPUT"}][{/if}]">
					[{if $conn.connection[1].cable_set === false}]
						<nobr>[{$conn.connection[1].title}] ([{isys type="lang" ident="LC__CATG__CONNECTOR__INPUT"}])</nobr> 
					[{else}]
						<nobr>[{$conn.connection[2].title}] ([{isys type="lang" ident="LC__CATG__CONNECTOR__OUTPUT"}])</nobr> 
					[{/if}]
					</td>
					<td style="background-image:url([{$dir_images}]/start_port1_r1_c3_s1.png);width:5px;background-repeat: no-repeat;">				
					</td>
									
					
				[{else}]
					<td style="background-image:url([{$dir_images}]/object_r1_c1_s1.png);width:5px;background-repeat:no-repeat;">				
					</td>
					<td class="connection_object">
						[{$conn.title}]
					<td style="background-image:url([{$dir_images}]/object_r1_c3_s1.png);width:5px;background-repeat:no-repeat;">				
					</td>				
					
					
					<td class="connection" >
					</td>
				[{/if}]
				[{assign var=counter value=$counter+1}]
			[{/foreach}]
				
			</tr>
			
		</table>
	<br>

	[{/foreach}]
		</td>
	</tr>
	[{/if}]
[{/foreach}]
[{else}]
    <p class="p10">
        <img class="vam" src="images/icons/infobox/blue.png">
        [{isys type="lang" ident="LC__REPORT__VIEW__OPEN_CABLE_COONECTIONS__EMPTY_OPEN_CABLE_CONNECTIONS"}]
    </p>
[{/if}]
</table>