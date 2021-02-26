<div class="login">

	[{if $error}]<div class="error p10 mb10">[{$error}]</div>[{/if}]

	<form action="[{$loginAction}]" method="post">
		<table>
			<tr>
				<td>Username:</td>
				<td><input type="text" name="username" class="input input-mini" id="username" value="" /></td>
			</tr>
			<tr>
				<td>Password:</td>
				<td><input type="password" name="password" class="input input-mini" id="password" value="" /></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<button type="submit" name="submit" class="btn">
						<span class="ml5 mr5">Login</span>
					</button>
				</td>
			</tr>
		</table>
	</form>

</div>

<script type="text/javascript">$('username').focus();</script>