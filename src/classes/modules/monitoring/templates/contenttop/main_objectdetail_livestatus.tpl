<script type="text/javascript">
	$('contentTopTitle').insert(new Element('span', {id: 'monitoring_livestatus_head', className:'hide pl15'}));

	new Ajax.Request('?ajax=1&call=monitoring_livestatus&func=load_livestatus_state', {
		parameters: {'[{$smarty.const.C__CMDB__GET__OBJECT}]': '[{$smarty.get.objID|strip_tags|escape}]'},
		method: "post",
		onSuccess: function (transport) {
			var json = transport.responseJSON,
				el = $('monitoring_livestatus_head').removeClassName('hide'),
				img = new Element('img', {className: 'ml5 vam'});

			if (json.success) {
				display_monitoring_state(json.data, $('monitoring_livestatus_head'));
			} else {
				el.addClassName('red').update(json.message);
			}
		}
	});
</script>