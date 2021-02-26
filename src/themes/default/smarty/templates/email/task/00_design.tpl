[{include file=$g_email_template}]


[{if isset($g_link)}]
[{$g_http}]://[{$g_hostname}][{$g_link}]
[{/if}]

-- 
i-doit ([{$g_hostname}], [{$g_ip}])
[{$smarty.now|date_format:"%A, %B %e, %Y"}], [{$smarty.now|date_format:"%H:%M:%S"}]
[{$gProductInfo.version}][{$gProductInfo.step}]