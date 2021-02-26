<div id="system-licence-overview">
	<h2 class="p10 header gradient text-shadow">[{isys type="lang" ident="LC__UNIVERSAL__LICENE_OVERVIEW"}]</h2>

	<div class="p5 fl">
		<table class="mainTable border mb10" style="width: 500px;">
			<thead>
				<tr>
					<th>[{isys type="lang" ident="LC__UNIVERTSAL__QUERY"}]</th>
					<th>[{isys type="lang" ident="LC__LICENCE_OVERVIEW__CURRENT_VALUE"}]</th>
					<th>[{isys type="lang" ident="LC__LICENCE_OVERVIEW__LICENCE_EXCEEDING"}]</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong>[{isys type="lang" ident="LC__LICENCE__DOCUMENTED_OBJECTS"}]</strong></td>
					<td>[{$stat_counts.objects}]</td>
					<td><span class="[{$exceeding.objects_class|default:"text-green"}]">[{$exceeding.objects}]</span></td>
				</tr>
				<tr>
					<td><strong>[{isys type="lang" ident="LC__LICENCE__FREE_OBJECTS"}]</strong></td>
					<td[{if $stat_counts.free_objects eq 0}] class="text-red text-bold"[{/if}]>[{$stat_counts.free_objects}]</td>
					<td><span class="[{$exceeding.objects_class|default:"text-green"}]">[{$exceeding.objects}]</span></td>
				</tr>
			</tbody>
		</table>

        <table class="mainTable border mb10" style="width: 500px;">
            <thead>
            <tr style="border-bottom:1px solid #888;">
                <th>[{isys type="lang" ident="LC__LICENCE_OVERVIEW__STATISTIC"}]</th>
                <th>[{isys type="lang" ident="LC__LICENCE_OVERVIEW__CURRENT_VALUE"}]</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><strong>[{isys type="lang" ident="LC__LICENCE_OVERVIEW__CMDB_REFERENCES"}]</strong></td>
                <td>[{$stat_counts.cmdb_references}]</td>
            </tr>
            <tr>
                <td><strong>[{isys type="lang" ident="LC__DASHBOAD__LAST_IDOIT_UPDATE"}]</strong></td>
                <td>[{$stat_stats.last_idoit_update}]</td>
            </tr>
            <tr>
                <td><strong>Version</strong></td>
                <td>[{$gProductInfo.version}]</td>
            </tr>
            </tbody>
        </table>

		[{if isset($note)}]
		<div class="box-green p5 mt10">
			<span>[{$note}]</span>
		</div>
		[{/if}]

		[{if isset($error)}]
		<div class="box-red p5 mt10">
			<strong>[{$error}]</strong>
		</div>
		[{/if}]
	</div>

    <div class="p5 fl">
        <div class="border" style="width: 500px;">
            <h3 class="p5 gradient border-bottom text-shadow">[{isys type="lang" ident="LC__LICENCE__OBJECT_COUNTER"}]</h3>
            <table class="mainTable border-none">
                <thead>
                <tr>
                    <th style="width: 400px">[{isys type="lang" ident="LC__REPORT__FORM__OBJECT_TYPE"}]</th>
                    <th>[{isys type="lang" ident="LC__CMDB__CATG__QUANTITY"}]</th>
                </tr>
                </thead>
            </table>

            <div style="height: 300px; overflow: auto">
                <table class="mainTable border-none">
                    <tbody>
                    [{foreach $stat_counts.objects_by_type as $count}]
                        <tr>
                            <td style="width: 400px"><strong>[{$count.type}]</strong></td>
                            <td>[{$count.count}]</td>
                        </tr>
                        [{/foreach}]
                    </tbody>
                </table>
            </div>
        </div>
    </div>

	<div class="cb"></div>

    <h2 class="mt5 p5 border-top header gradient text-shadow">Licenses</h2>

    <div class="p5 fl">
        <strong>Licensed Add-ons</strong>

        <br /><br />

        [{foreach from=$licensedAddOns key=$addOnKey item=$addOn}]
            [{if $addOn.licensed}]<img src="[{$dir_images}]icons/silk/tick.png" class="vam" /><span class="ml5 mr5">[{$addOn.label}]</span>[{/if}]
        [{/foreach}]

        <br /><br />

        [{$stringTimeLimit}]

        [{if $expiresWithinSixMonths}]
        <div class="progress mt5">
            <div id="remaining_time_percent_[{$uid}]" class="progress-bar" data-width-percent="[{$remainingTimePercentage}]" style="width:0; background-color:transparent;"></div>
        </div>
        [{/if}]
    </div>
</div>

<script type="text/javascript">
    (function () {
        "use strict";

        idoit.Require.require('smartyProgress', function () {
            progressBarInit(true);
        });
    }());
</script>