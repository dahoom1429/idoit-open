<div id="exception" class="blurred-shadow slideDown" style="display:none;">
    <a href="javascript:window.close_exception_box();" style="float:right; padding:2px; color:#f00;" class="bold m5">&times;</a>

    <h2 class="header"><img src="[{$dir_images}]icons/infoicon/error.png" class="vam" /> <span class="vam">[{$error_topic|default:"i-doit system error"}]</span></h2>

    <div id="message">
        <h3>[{isys type="lang" ident="LC__UNIVERSAL__MESSAGE"}]</h3>

        <p>[{$g_error|nl2br}]</p>

        [{if is_object($g_error)}]
        <h3>Trace:</h3>
        <pre>[{$g_error->get_last_trace()}]</pre>
        [{/if}]
    </div>
</div>
<script type="text/javascript">
	if (typeof Effect.Opacity == 'function' && typeof Effect.SlideDown == 'function') {
		show_overlay();

		new Effect.Opacity('overlay', {from: 0, to: 0.4, duration: 0.3});
		$('exception').show();
    }
	else
	{
		$('overlay', 'exception').invoke('show');
    }

	window.close_exception_box = function () {
        if (typeof Effect.Move == 'function' && typeof Effect.Fade == 'function') {
			$('overlay').fade();
	        $('exception').className = 'slideUp';
        } else {
	        $('overlay', 'exception').invoke('hide');
        }


    }
</script>