<script type="text/javascript">
if ($('error')) { 
	$('error').update('[{$error}]');
	if (!$('error').visible()) $('error').show();
	new Effect.Highlight('error');
} else {
    idoit.Notify.error('[{$error}]');
}
</script>