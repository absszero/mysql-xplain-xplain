<?php $this->layout('layout'); ?>
<form role="form" method="post">
	<div class="form-group">
		<label for="host">Host</label>
		<input type="text" class="form-control" name="host" id="host" placeholder="localhost" value="<?php if (isset($_SESSION['mysql']['host'])) echo $_SESSION['mysql']['host']; ?>">
	</div>
	<div class="form-group">
		<label for="user">User</label>
		<input type="text" class="form-control" name="user" id="user" placeholder="root" value="<?php if (isset($_SESSION['mysql']['user'])) echo $_SESSION['mysql']['user']; ?>">
	</div>
	<div class="form-group">
		<label for="pass">Password</label>
		<input type="pass" class="form-control" name="pass" id="pass" value="<?php if (isset($_SESSION['mysql']['pass'])) echo $_SESSION['mysql']['pass']; ?>">
	</div>
	<div class="form-group">
		<label for="base">Default database</label>
		<input type="text" class="form-control" name="base" id="base" placeholder="test" value="<?php if (isset($_SESSION['mysql']['base'])) echo $_SESSION['mysql']['base']; ?>">
	</div>
	<div class="checkbox">
		<label>
			<input name="permanent_login" type="checkbox" value="1"> Permanent login
		</label>
	</div>
	<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-ok"></span> Submit</button>
</form>
