<div class="gradient p5 text-shadow"><h3>[{isys type="lang" ident="LC__WIDGET__MY_BOOKMARKS"}]</h3></div>

[{if is_array($tabledata)}]
<ol class="rectangle-list">
    [{foreach $tabledata as $bookmark}]
        <li>
            <a [{if $bookmark.new_window}]target="_blank"[{/if}] href="[{$bookmark.link}]">[{$bookmark.title}]</a>
        </li>
    [{/foreach}]
</ol>
[{/if}]

<style type="text/css">
	ol.rectangle-list {
		counter-reset: li; /* Initiate a counter */
		list-style: none; /* Remove default numbering */
		*list-style: decimal; /* Keep using default numbering for IE6/7 */
		font: 15px 'trebuchet MS', 'lucida sans';
		padding: 0;
		text-shadow: 0 1px 0 rgba(255,255,255,.5);
	}

	ol.rectangle-list ol{
		margin: 0 0 0 2em; /* Add some left margin for inner lists */
	}

	.rectangle-list a {
		position: relative;
		display: block;
		padding: .3em .4em .3em .8em;
		*padding: .4em;
		margin: .5em 0 .5em 2.5em;
		background: #ddd;
		color: #000;
		text-decoration: none;
		transition: all .2s ease-out;
		outline:1px solid #808080;
		border:1px solid #fff;
	}

	.rectangle-list a:hover{
		background: #eee;
	}

	.rectangle-list a:before{
		content: counter(li);
		counter-increment: li;
		position: absolute;
		left: -2.6em;
		top: 50%;
		margin-top: -1em;
		background: #808080;
		color: #fff;
		height: 2em;
		width: 2em;
		line-height: 2em;
		text-align: center;
		font-weight: bold;
		text-shadow: 0 1px 0 rgba(0,0,0,.5);
	}

	.rectangle-list a:after{
		position: absolute;
		content: '';
		border: .5em solid transparent;
		left: -1em;
		top: 50%;
		margin-top: -.5em;
		transition: all .2s ease-out;
	}

	.rectangle-list a:hover:after{
		left: -.6em;
		border-left-color: #808080;
	}
</style>