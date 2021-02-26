<style type="text/css">
	#report_view_upcoming_changes div.box {
		width: auto;
		margin-bottom: 10px;
    }

	#report_view_upcoming_changes div.box:last-of-type {
		margin-bottom: 0;
	}

    #report_view_upcoming_changes h2 {
	    cursor: pointer;
    }

    #report_view_upcoming_changes h2:hover {
        color: #c00;
    }

    #report_view_upcoming_changes table.mainTable {
        border: none;
        border-top: 1px solid #aaa;
    }
</style>

<div id="report_view_upcoming_changes" class="p5">
    [{foreach from=$changeData key=key item=change}]
    [{cycle values="CMDBListElementsOdd,CMDBListElementsEven" reset=true print=false}]
	<div class="box">
	    <h2 class="text-shadow gradient p5" onclick="if($('list_[{$key}]')) $('list_[{$key}]').toggle();">[{$titles.$key}]</h2>

		[{if $change}]
	    <table cellpadding="2" cellspacing="0" width="100%" id="list_[{$key}]" class="mainTable" style="display:none;">
	        <tr>
	            <th>[{isys type="lang" ident="LC__CATG__ODEP_OBJ"}]</th>
	            <th>[{isys type="lang" ident="LC__UNIVERSAL__CHANGING_TO"}]</th>
	            <th>[{isys type="lang" ident="LC__CMDB__CATS_CP_CONTRACT__START_DATE"}]</th>
	            <th>[{isys type="lang" ident="LC__CMDB__CATS_CP_CONTRACT__END_DATE"}]</th>
	        </tr>
	        <tbody>
		    [{foreach from=$change item=data}]
		        <tr class=[{cycle values="CMDBListElementsOdd,CMDBListElementsEven"}]>
		            <td><a href="?objID=[{$data.id}]">[{$data.title}]</a></td>
		            <td>[{isys type="lang" ident=$data.status}]</td>
		            <td>[{$data.start|date_format:"%A, %B %e, %Y"}]</td>
		            <td>[{$data.end|date_format:"%A, %B %e, %Y"}]</td>
		        </tr>
		    [{/foreach}]
	        </tbody>
	    </table>
		[{else}]
		<p class="p5">[{isys type="lang" ident="LC__REPORT__VIEW__UPCOMING_STATUS_CHANGES__NO_CHANGES"}]</p>
		[{/if}]
	</div>
    [{/foreach}]
</div>