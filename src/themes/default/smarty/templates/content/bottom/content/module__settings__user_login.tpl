<h2 class="p5 gradient border-bottom">[{isys type="lang" ident=$title}]</h2>

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__LOGIN__PASSWORD" name="C__CONTACT__PERSON_PASSWORD"}]</td>
		<td class="value">[{isys type="f_password" name="C__CONTACT__PERSON_PASSWORD"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__LOGIN__PASSWORD" name="C__CONTACT__PERSON_PASSWORD_SECOND"}]</td>
		<td class="value">[{isys type="f_password" name="C__CONTACT__PERSON_PASSWORD_SECOND"}]</td>
	</tr>
</table>

[{if $error}]
<div class="box-red p5 mt10">[{$error}]</div>
[{/if}]

<script type="text/javascript">
    (function () {
        'use strict';

        [{if $validationError === true}]
            $$('input[type="password"]')
                .invoke('addClassName', 'input-error')
                .invoke('on', 'keypress', function () {
                    this.removeClassName('input-error');
                });
        [{/if}]
    })();
</script>