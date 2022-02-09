<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Pengaturan Ulang Sandi</title>
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<link rel="stylesheet" href="<?= base_url('assets/adminlte/') ?>bower_components/bootstrap/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?= base_url('assets/adminlte/') ?>bower_components/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?= base_url('assets/adminlte/') ?>bower_components/Ionicons/css/ionicons.min.css">
	<link rel="stylesheet" href="<?= base_url('assets/adminlte/') ?>dist/css/AdminLTE.min.css">
	<link rel="stylesheet" href="<?= base_url('assets/adminlte/') ?>plugins/iCheck/square/blue.css">
	<!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic"> -->
	<style type="text/css">
		.help-block.error {
			color: red;
		}
	</style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
	<div class="login-logo">
		<a href="<?= base_url() ?>">Welcome to <b>Codeigniter Starter</b></a>
	</div>
	<div class="login-box-body">
		<p class="login-box-msg text-green">Masukkan kata sandi yang baru</p>
		<form action="<?= base_url($this->router->fetch_class().'/reset_password') ?>" method="post">
			<div class="form-group has-feedback">
				<label>New Password</label>
				<input type="text" class="form-control" placeholder="Kata Sandi Baru" name="new_password" value="<?= set_value('new_password') ?>">
				<span class="fa fa-key form-control-feedback"></span>
				<?= form_error('new_password', '<span class="help-block error">', '</span>'); ?>
			</div>
			<div class="form-group has-feedback">
				<label>Confirm New Password</label>
				<input type="text" class="form-control" placeholder="Masukkan Ulang Kata Sandi Baru" name="repeat_new_password" value="<?= set_value('repeat_new_password') ?>">
				<span class="fa fa-key form-control-feedback"></span>
				<?= form_error('repeat_new_password', '<span class="help-block error">', '</span>'); ?>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<button type="submit" class="btn btn-primary btn-block btn-flat">Ubah Kata Sandi</button>
				</div>
			</div>
		</form>
		<br>
		<a href="<?= base_url($this->router->fetch_class().'/register') ?>" class="text-center">Mendaftar</a>
		<a href="<?= base_url($this->router->fetch_class().'/login') ?>" class="text-center pull-right">Masuk</a>

	</div>
</div>

<script src="<?= base_url('assets/adminlte/') ?>bower_components/jquery/dist/jquery.min.js"></script>
<script src="<?= base_url('assets/adminlte/') ?>bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="<?= base_url('assets/adminlte/') ?>plugins/iCheck/icheck.min.js"></script>
</body>
</html>
