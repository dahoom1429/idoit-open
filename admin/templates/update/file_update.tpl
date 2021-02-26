<style type="text/css">
	#files-list {
		position: relative;
	}

	#filelist {
		position: relative;
		overflow: hidden;
	}

	#filelist.truncated {
		height: 200px;
	}

	#filelist-fader {
		position: absolute;
		width: 100%;
		bottom: 0;
		left: 0;
		height: 30px;
		background: transparent url('images/gradient.png') repeat-x;
	}

	#filelist-toggle {
		position: absolute;
		bottom: 5px;
		right: 5px;
	}

	#filelist-toggle:active {
		left: auto;
		top: auto;
		bottom: 4px;
		right: 4px;
	}
</style>

<h2>File-Update</h2>

<h3>Files ([{$files_count|default:'0'}])</h3>
<div id="files-list" class="border p5 mt5">
	<pre id="filelist" [{if $files_count > 14}]class="truncated"[{/if}]>[{$files}]</pre>

	[{if $files_count > 14}]
	<div id="filelist-fader">&nbsp;</div>
	<button type="button" id="filelist-toggle" class="btn">
		<img src="../images/icons/silk/bullet_toggle_plus.png" class="mr5" /><span>Expand</span>
	</button>
	[{/if}]
</div>

[{if ! $writable}]
<div class="red p5 mt10 border border-red">
	<strong>Warning: File copy will not work properly.</strong>
	<p>Please make sure the apache user got write permissions to your i-doit directory and reload this page.</p>
	<p>Linux users can use the following shell script: [{$g_config.base_dir}]idoit-rights.sh</p>
</div>
[{/if}]

<div class="bold red p5 mt10 border">
	<p>We strongly recommend that you do a database backup of your system and tenant databases before starting this update !</p>
</div>

<div class="bold green p5 mt5 border huge-text">
	<label><input type="checkbox" id="yes-i-did-a-backup" /> Yes, I did a backup!</label>
</div>

<script type="text/javascript">
	var filelist_toggler = $('filelist-toggle'),
		yes_i_did_a_backup = $('yes-i-did-a-backup');

	window.next_callback = function () {
		var i_did_a_backup = yes_i_did_a_backup.checked;

		if (! i_did_a_backup) {
			yes_i_did_a_backup.up('div').highlight({endcolor:'#ffffff', restorecolor:'#ffffff'});
		}

		return i_did_a_backup;
	};

	if (filelist_toggler) {
		filelist_toggler.on('click', function (ev) {
			var button = ev.findElement('button');

			if ($('filelist').toggleClassName('truncated').hasClassName('truncated')) {
				$('filelist-fader').removeClassName('hidden');

				button
					.down('img').writeAttribute('src', '../images/icons/silk/bullet_toggle_plus.png')
					.next('span').update('Expand');
			} else {
				$('filelist-fader').addClassName('hidden');

				button
					.down('img').writeAttribute('src', '../images/icons/silk/bullet_toggle_minus.png')
					.next('span').update('Collapse');
			}
		});
	}
</script>