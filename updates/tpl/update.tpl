<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title>i-doit Update</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="[{$g_config.www_dir}]updates/update.css">
	<script type="text/javascript" language="JavaScript" src="src/tools/js/prototype/prototype.js"></script>
	<script type="text/javascript" language="JavaScript"
	        src="src/tools/js/scriptaculous/src/scriptaculous.js?load=effects"></script>
</head>
<body>

<form id="isys_form" name="install_form" enctype="multipart/form-data" method="POST">
	<div id="mainContainer" [{if $g_current_step == 1}]class="fadeIn"[{/if}]>
		<table id="mainTable" cellpadding="0" cellspacing="0">
			<tr>
				<td colspan="2" id="mainBanner">
					<img src="[{$g_config.www_dir}]updates/images/logo.png" alt="i-doit" />
				</td>
			</tr>
			<tr>
				<td id="mainMenu" nowrap="nowrap">

					<div class="mainMenuButton" id="mainStep1">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. i-doit Update
					</div>

					<div class="mainMenuButton" id="mainStep2">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. Available Updates
					</div>

					<div class="mainMenuButton" id="mainStep3">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. Database(s)
					</div>

					<div class="mainMenuButton" id="mainStep4">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. File-Update
					</div>

					<div class="mainMenuButton" id="mainStep5">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. Overview (Log)
					</div>

					<div class="mainMenuButton" id="mainStep6">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. Migration
					</div>

					<div class="mainMenuButton" id="mainStep7">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. Attribute migration
					</div>

					<div class="mainMenuButton" id="mainStep8">
						<img src="images/icons/silk/page_white_gear.png" alt="" />[{counter}]. Completion
					</div>

				</td>
				<td id="mainContent">
					<div id="mainContentInner">
						[{include file=$g_steps.$g_current_step|default:"steps/1.tpl"}]
					</div>
				</td>
			</tr>
			<tr>
				<td id="mainCopyright" class="p10">
					copyright&nbsp;[{$smarty.now|date_format:"%Y"}]<br />
					synetics&nbsp;gmbh
				</td>
				<td id="mainDialog" nowrap="nowrap" class="p10">
					<img src="[{$g_config.www_dir}]setup/images/main_installing.gif" style="display:none;float:left;margin-top:3px;" id="loadingGif" />
					<input type="hidden" name="step" value="[{$g_current_step}]" />
					[{if $g_current_step > 1 && $g_current_step < count($g_steps)-1}]
						<input type="button" class="button" name="prev" id="btn_prev" value="&laquo; Previous" />
					[{/if}]
					[{if $g_current_step > 0 && $g_current_step < count($g_steps)-1}]
						[{if !$g_stop}]
							<input type="button" class="button" name="next" id="btn_next" value="Next &raquo;" />
							<input type="hidden" name="debug_log" value="[{$debug_log}]" />
							<input type="hidden" name="debug_log_www" value="[{$debug_log_www}]" />
							<input type="hidden" name="migration_log_file" value="[{$migration_log_file}]" />
						[{/if}]
					[{/if}]
				</td>
			</tr>
		</table>
	</div>
</form>

<script language="JavaScript" type="text/javascript">
    var $form           = $('isys_form'),
        currentStep     = parseInt('[{$g_current_step|default:'0'}]'),
        $previousButton = $('btn_prev'),
        $nextButton     = $('btn_next'),
        $currentStep    = $('mainStep' + currentStep),
        $contentArea    = $('content'),
        $contentTable;

    if ($previousButton) {
        $previousButton.on('click', function () {
            $form.down('[name="step"]').setValue(currentStep - 1);
            $form.submit();
        })
    }

    if ($nextButton) {
        $nextButton.focus();

        $nextButton.on('click', function () {
            $$('.remove-me').invoke('remove');

            if (currentStep === 4 || currentStep === 5 || currentStep === 6) {
                new Effect.Fade('content', {duration: 0.2});

                if ($('loadingTable')) {
                    $('loadingTable').show();
                }

                $nextButton.addClassName('disabled').disable();
            }

            if (currentStep === 4) {
                new Ajax.Request('?load=update', {
                    parameters: {
                        "copy-files": 1
                    },
                    method:     "post",
                    onFailure: function (xhr) {
                        notifyError(xhr);
                    },
                    onComplete:  function (xhr) {
                        var json = xhr.responseJSON;

                        if (json && json.success) {
                            // We add a 'pause' here because in the i-doit 1.16 update we had problems regarding
                            // method parameter signature mismatches (due to a symfony update).
                            // The technical issue here seemed to be PHPs 'opcache', not correctly reloading newly copied files leading to a 500'er.

                            // The 'timeout' helped to resolve this.
                            setTimeout(function () {
                                submitToNextStep();
                            }, 2000);
                        } else {
                            notifyError(xhr);
                        }
                    }
                });
            } else {
                submitToNextStep();
            }

        });
    }

    function notifyError(xhr) {
        var json = xhr.responseJSON;

        if ($('loadingTable')) {
            $('loadingTable').hide();
        }

        if (json === null || !json.hasOwnProperty('message')) {
            json = {message:xhr.responseText};
        }

        $('mainContentInner').insert(new Element('div', {className:'loadingTable remove-me red'})
            .update(new Element('strong').update('A problem occured during update:'))
            .insert(new Element('p').update(json.message || 'Please retry this step!')));

        $nextButton.removeClassName('disabled').enable();
    }

    function submitToNextStep() {
        $form.down('[name="step"]').setValue(currentStep + 1);
        $form.submit();
    }

    if ($currentStep) {
        $currentStep.addClassName('mainMenuButtonActive');
    }

    if ($$('.red').length > 0 && $contentArea) {
        $contentTable = $contentArea.down('table')

        if ($contentTable) {
            $contentArea.scrollTop = $contentTable.getHeight();
        }
    }
</script>

</body>
</html>
