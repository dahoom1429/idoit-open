[{isys_group name="tom.popup.interval"}]
	<div id="popup-interval">
		<h3 class="popup-header">
			<img class="fr mouse-pointer popup-closer" alt="x" src="[{$dir_images}]icons/close-circle.png">
			<span>[{isys type="lang" ident="LC__INTERVAL__POPUP_HEADER"}]</span>
		</h3>

		<div class="popup-content">
			<table class="contentTable">
				<tr>
					<td class="key">
						[{isys type="lang" ident="LC__INTERVAL__REPEAT_EVERY"}]
					</td>
					<td class="value">
						[{isys type="f_count" name="C__INTERVAL__REPEAT_EVERY"}] [{isys type="f_dialog" name="C__INTERVAL__REPEAT_EVERY_UNIT"}]
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="value pl20">
						<div id="interval-week-options" class="hide mt5">
							[{isys type="lang" ident="LC__INTERVAL__REPEAT_ON"}]<br />
							[{foreach $days as $day => $dayName}]
							<label class="mr5"><input type="checkbox" name="C__INTERVAL__REPEAT_WEEKLY[]" value="[{$day}]" class="mr5" />[{$dayName}]</label>
							[{/foreach}]
						</div>
						<div id="interval-month-options" class="hide mt5">
							<label class="display-block">
								<input type="radio" name="C__INTERVAL__REPEAT_MONTHLY" value="absolute" class="mr5" checked>[{isys type="lang" ident="LC__INTERVAL__MONTHLY_AT"}] [{date('d')}].
							</label>
							<label class="display-block"><input type="radio" name="C__INTERVAL__REPEAT_MONTHLY" value="relative" class="mr5">[{$relativeDate}]</label>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						&nbsp;
					</td>
				</tr>
				<tr>
					<td class="key vat pt5">[{isys type="lang" ident="LC__INTERVAL__ENDS"}]</td>
					<td class="value pl20">
						<label class="display-block"><input type="radio" name="C__INTERVAL__END_AFTER" value="[{$endAfterNever}]" class="mr5" checked />
							[{isys type="lang" ident="LC__INTERVAL__ENDS_NEVER"}]
						</label>
						<label class="display-block mt5"><input type="radio" name="C__INTERVAL__END_AFTER" value="[{$endAfterDate}]" class="mr5 mt5 fl" />
							<div class="input-group" style="width:220px;">
								<div class="input-group-addon input-group-addon-unstyled">[{isys type="lang" ident="LC__INTERVAL__ENDS_ON"}]</div>
								[{isys type="f_popup" name="C__INTERVAL__END_DATE" p_strPopupType="calendar"}]
							</div>
						</label>
						<label class="display-block mt5"><input type="radio" name="C__INTERVAL__END_AFTER" value="[{$endAfterEvents}]" class="mr5 mt5 fl" />
							<div class="input-group" style="width:220px;">
								<div class="input-group-addon input-group-addon-unstyled" style="border-right:1px solid #aaa;">[{isys type="lang" ident="LC__INTERVAL__ENDS_AFTER"}]</div>
								[{isys type="f_count" name="C__INTERVAL__END_EVENT_AMOUNT"}]
								<div class="input-group-addon input-group-addon-unstyled">[{isys type="lang" ident="LC__INTERVAL__ENDS_AFTER_DATES"}]</div>
							</div>
						</label>
					</td>
				</tr>
			</table>
		</div>

		<div class="popup-footer">
			<button type="button" id="popup-interval-save" class="btn mr5">
				<img src="[{$dir_images}]icons/silk/tick.png" class="mr5" /><span>[{isys type="lang" ident="LC_UNIVERSAL__ACCEPT"}]</span>
			</button>
			<button type="button" class="btn popup-closer">
				<img src="[{$dir_images}]icons/silk/cross.png" class="mr5" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
			</button>
		</div>
	</div>
	<script type="text/javascript">
        (function () {
            "use strict";

            window.idoit.Require.addModule('popupInterval', '[{$dir_tools}]js/popups/interval.js');

            idoit.Require.require('popupInterval', function () {
                var $popup                = $('popup-interval'),
                    config                = JSON.parse('[{$config|json_encode|escape:"javascript"}]'),
                    $selfView             = $('[{$selfView}]'),
                    $selfHidden           = $('[{$selfHidden}]'),
                    $saveButton           = $('popup-interval-save'),
                    popupInterval;

                popupInterval = new PopupInterval($popup, {
                    config: config,
                    intervalEveryUnits: {
                        "[{$repeatUnitDay}]":   [
                            '[{isys type="lang" ident="LC__UNIVERSAL__DAY"}]',
                            '[{isys type="lang" ident="LC__UNIVERSAL__DAYS"}]'
                        ],
                        "[{$repeatUnitWeek}]":  [
                            '[{isys type="lang" ident="LC__UNIVERSAL__WEEK"}]',
                            '[{isys type="lang" ident="LC__UNIVERSAL__WEEKS"}]'
                        ],
                        "[{$repeatUnitMonth}]": [
                            '[{isys type="lang" ident="LC__UNIVERSAL__MONTH"}]',
                            '[{isys type="lang" ident="LC__UNIVERSAL__MONTHS"}]'
                        ],
                        "[{$repeatUnitYear}]":  [
                            '[{isys type="lang" ident="LC__UNIVERSAL__YEAR"}]',
                            '[{isys type="lang" ident="LC__UNIVERSAL__YEARS"}]'
                        ]
                    },
	                repeatUnit:{
                        day: '[{$repeatUnitDay}]',
                        week: '[{$repeatUnitWeek}]',
                        month: '[{$repeatUnitMonth}]',
                        year: '[{$repeatUnitYear}]'
	                },
	                endAfter: {
		                never: '[{$endAfterNever}]',
		                date: '[{$endAfterDate}]',
		                events: '[{$endAfterEvents}]'
	                }
                });

                $saveButton.on('click', function () {
                    config = popupInterval.getConfig();

                    $selfHidden.setValue(JSON.stringify(config));

                    new Ajax.Request('[{$ajaxUrl}]&func=humanReadableInterval', {
                        parameters: {
                            config: $selfHidden.getValue()
                        },
                        onSuccess:  function (xhr) {
                            var json = xhr.responseJSON;

                            if (json.success) {
                                $selfView.setValue(json.data);
                            } else {
                                idoit.Notify.error(json.message || xhr.responseText, {sticky: true});
                            }
                        }
                    });

                    popup_close();
                });

                $popup.on('click', '.popup-closer', function () {
                    popup_close();
                });
            });
        }());
	</script>
	<style type="text/css">
		#popup-interval {
			height: 100%;
		}

		#popup-interval .contentTable .key {
			width: 130px;
		}
	</style>
[{/isys_group}]