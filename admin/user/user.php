<?php include('../../tilpark.php'); ?>
<?php get_header(); ?>

<?php
// aktif olan kullanıcın tüm bilgilerini cekelim
if( !$user = get_user($_GET['id'], true) ) { echo get_alert('Kullanıcı hesabı bulunamadı.', 'warning', false); get_footer(); return false; }
if($user->account_id) { $account = get_account($user->account_id); }




## hesap silme
if(isset($_GET['status']) and user_access('admin')) {
	if($_GET['status'] == '0' OR $_GET['status'] == '1') {
		$_args = array();
		$_args['add_log'] = false;
		$_args['add_alert'] = false;
		$_args['update']['status'] = $_GET['status'];
		if(update_user($user->id, $_args)) {
			if($_GET['status'] == '0') { 
				add_log(array('table_id'=>'users:'.$user->id, 'log_key'=>__FUNCTION__, 'log_text'=>_b($user->display_name).' kullanıcı hesabını <u>pasife</u> aldı.')); }
			else { 
				add_log(array('table_id'=>'users:'.$user->id, 'log_key'=>__FUNCTION__, 'log_text'=>_b($user->display_name).' kullanıcı hesabını <u>aktife</u> aldı.')); }
		}
	}
}


### fotofraf yukleme ###

## fotoraf silme
if(isset($_GET['delete_avatar']) and user_access('admin')) {
	if($user->avatar) {
		if(delete_image($user->avatar)) {
			update_user($user->id, array('avatar'=>''));
		}
	}
}

## fotoraf yukleme
if(isset($_POST['update_profile']) and user_access('admin')) {

	$_user['username'] 	= $_POST['username'];
	$_user['name'] 		= $_POST['name'];
	$_user['surname'] 	= $_POST['surname'];
	$_user['gsm']		= get_set_gsm($_POST['gsm']);
	$_user['role']		= $_POST['role'];
	$_user['gender']	= $_POST['gender'];
	$_user['citizenship_no']		= $_POST['citizenship_no'];

	if(!isset($_POST['til_login'])) {
		$_user['til_login'] = false;
	}

	// resim yukleme
	if($_FILES['avatar']['size'] > 0) {

		$args['uniquetime'] = get_uniquetime();
		$args['sizing']['width'] 	= '150';
		$args['sizing']['height'] 	= '150';
		if($upload_image = upload_image($_FILES['avatar'], $args)) {
			$_user['avatar'] = $upload_image;

			// yeni resim yuklendigi icin eski resmi sunucudan silelim
			delete_image($user->avatar);
		}
	}

	// eger sifre kutusu bos degilse sifre guncellenmek isteniyordur.
	if(!empty($_POST['password'])) {
		if($_POST['password'] != $_POST['password_again']) {
			add_alert('Yeni oluşturduğunuz şifre ile şifre tekrarı aynı değil. Lütfen tekrar deneyin.', 'danger', 'update_profile');
		} else {
			$_user['password'] = $_POST['password'];
		}
	}

	if(!is_alert('update_profile')) {
		if(update_user($user->id, $_user)) {

		}
	}	
}







## eger yukarıdai guncelle yapilmis ise bilgileri tekrar alalim diye bilgileri tekrar aliyoruz
$user = get_user($_GET['id'], false);

add_page_info( 'title', $user->name.' '.$user->surname );
add_page_info( 'nav', array('name'=>'Personeller', 'url'=>get_site_url('admin/user/list.php')) );
add_page_info( 'nav', array('name'=>$user->name.' '.$user->surname) );
?>


<?php if($user->id AND @$user->status == '0'): ?>
	<?php echo get_alert('<i class="fa fa-trash-o"></i> <b>Dikkat!</b> Kullanıcı hesabı pasif durumda.', 'warning', false); ?>
	<div class="h-20 visible-xs"></div>
<?php else: ?>
	<?php create_modal(array('id'=>'status', 
		'title'=>'Personel hesabı <u>pasifleştirme</u>', 
		'content'=>_b($user->display_name).' personel hesabını pasifleştirmek istiyor musun? <br /> <small>Personel hesabı veritabanından <u>silinmez</u>. Fakat arama ve listelemelerde bulunamaz.</small>', 
		'btn'=>'<a href="?id='.$user->id.'&status=0" class="btn btn-danger">Evet, onaylıyorum</a>')); ?>
<?php endif; ?>


<ul class="nav nav-tabs til-nav-page" role="tablist"> 
	<li role="presentation" class="active"><a href="#home" id="home-tab" role="tab" data-toggle="tab" aria-controls="home" aria-expanded="true"><i class="fa fa-user-o"></i><span class="hidden-xs"> Profil</span></a></li> 
	<?php if(user_access('admin')): ?><li role="presentation" class="hidden-xs"><a href="#cv" id="cv-tab" role="tab" data-toggle="tab" aria-controls="cv" aria-expanded="true"><i class="fa fa-address-book-o"></i><span class="hidden-xs"> CV</span></a></li><?php endif; ?>
	<?php if(user_access('admin')): ?><li role="presentation"><a href="#salary" id="salary-tab" role="tab" data-toggle="tab" aria-controls="salary" aria-expanded="true"><i class="fa fa-money"></i><span class="hidden-xs"> Maaş</span></a></li><?php endif; ?>
	<li role="presentation" class=""><a href="#logs" role="tab" id="logs-tab" data-toggle="tab" aria-controls="logs" aria-expanded="false"><i class="fa fa-database"></i><span class="hidden-xs"> Geçmiş</span></a></li> 

	<li role="presentation" class="dropdown pull-right til-menu-right"> <a href="#" class="dropdown-toggle" id="myTabDrop1" data-toggle="dropdown" aria-controls="myTabDrop1-contents" aria-expanded="false"><i class="fa fa-cogs"></i><span class="hidden-xs"> Seçenekler </span><span class="caret"></span></a> 
		<ul class="dropdown-menu" aria-labelledby="myTabDrop1" id="myTabDrop1-contents"> 
			<li><a href="<?php site_url('admin/user/task/add.php?rec_u_id='.$user->id); ?>">Görev Ata</a></li> 
			<li><a href="<?php site_url('admin/user/message/add.php?rec_u_id='.$user->id); ?>">Mesaj Gönder</a></li>
			<?php if(user_access('admin')): ?>
				<li role="separator" class="divider"></li>
				<?php if($user->status == '1'): ?>
					<li><a href="#" data-toggle="modal" data-target="#status"><i class="fa fa-trash-o fa-fw text-danger"></i> Sil</a></li>
				<?php else: ?>
					<li><a href="?id=<?php echo $user->id; ?>&status=1"><i class="fa fa-undo fa-fw text-success"></i> Aktifleştir</a></li>
				<?php endif; ?> 
			<?php endif; ?>
		</ul> 
	</li>
	<li role="presentation" class="dropdown pull-right til-menu-right"> <a href="#" class="dropdown-toggle" id="myTabDrop1" data-toggle="dropdown" aria-controls="myTabDrop1-contents" aria-expanded="false"><i class="fa fa-print"></i><span class="hidden-xs"> Yazdır </span><span class="caret"></span></a> 
		<ul class="dropdown-menu" aria-labelledby="myTabDrop1" id="myTabDrop1-contents"> 
			<li><a href="print_cv.php?id=<?php echo $user->id; ?>" target="_blank">Özgeçmiş (CV) Yazdır</a></li> 
			<li class="divider"></li>
			<li><a href="print_salary.php?id=<?php echo $user->id; ?>" target="_blank">Maaş Ekstresi Yazdır</a></li> 
			<li><a href="print_salary_detail.php?id=<?php echo $user->id; ?>" target="_blank">Detaylı Maaş Ekstresi Yazdır</a></li> 
		</ul> 
	</li>
</ul>

<div class="tab-content"> 

	<!-- tab:home -->
	<div class="tab-pane active fade in" role="tabpanel" id="home" aria-labelledby="home-tab"> 

		<?php print_alert(); ?>

		<form name="form-profile" id="form-profile" method="POST" action="?id=<?php echo $user->id; ?>" class="validate" enctype="multipart/form-data">
		<div class="row">
			<div class="col-md-6">

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="username">E-posta</label>
							<input type="email" name="username" id="username" value="<?php echo $user->username; ?>" class="form-control email">
						</div> <!-- /.form-group -->
					</div> <!-- /.col-md-6 -->
					<div class="col-md-6">
						<div class="form-group">
							<label for="gsm">Cep Telefonu</label>
							<input type="tel" name="gsm" id="gsm" value="<?php echo $user->gsm; ?>" class="form-control digits" minlength="10" maxlength="11">
						</div> <!-- /.form-group -->
					</div> <!-- /.col-md-6 -->
				</div> <!-- /.row -->


				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="name">Ad</label>
							<input type="text" name="name" id="name" value="<?php echo $user->name; ?>" class="form-control" minlength="3" maxlength="20">
						</div> <!-- /.form-group -->
					</div> <!--- /.col-md-6 -->
					<div class="col-md-6">
						<div class="form-group">
							<label for="surname">Soyad</label>
							<input type="text" name="surname" id="surname" value="<?php echo $user->surname; ?>" class="form-control" minlength="3" maxlength="20">
						</div> <!-- /.form-group -->
					</div> <!-- /.col-md-6 -->
				</div> <!-- /.row -->

				<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="username">Cinsiyet</label>
						<select name="gender" id="gender" class="form-control">
							<option value="1" <?php selected('1', $user->gender); ?>>Bay</option>
							<option value="0" <?php selected('0', $user->gender); ?>>Bayan</option>
						</select>
					</div> <!-- /.form-group -->
				</div> <!-- /.col-md-6 -->
				<div class="col-md-6">
					<div class="form-group">
						<label for="citizenship_no">T.C. Kimlik No</label>
						<input type="tel" name="citizenship_no" id="citizenship_no" class="form-control digits" minlength="11" maxlength="11" value="<?php echo $user->citizenship_no; ?>">
					</div> <!-- /.form-group -->
				</div> <!-- /.col-md-6 -->
			</div> <!-- /.row -->


				<?php if(user_access('admin')): ?>
					<div class="form-group">
						<label for="til_login" id="label_til_login">
							<input type="checkbox" name="til_login" id="til_login" value="1" <?php echo $user->til_login ? 'checked' : ''; ?> data-toggle="switch">
							Bu personel <b>Tilpark!</b> sistemine giriş yapabilir mi?
						</label>
					</div> <!-- /.form-group -->

					<script>
					$(document).ready(function() {
			
						$('#til_login').change(function() {
							if($('#til_login').is(':checked')) {
								$('#is_til_login_div').removeClass('hidden');
							} else {
								$('#is_til_login_div').addClass('hidden');
							}
						});

						<?php if($user->til_login): ?>
							$('#is_til_login_div').removeClass('hidden');
						<?php endif; ?>
					});
					</script>
				<?php endif; ?>


				<div id="is_til_login_div" class="hidden">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="role">Yetki</label>
								<select name="role" id="role" class="select form-control" <?php if(!user_access('admin')): echo 'disabled'; endif; ?>>
									<option value="5" <?php selected($user->role, '5'); ?>>Personel</option>
									<option value="4" <?php selected($user->role, '4'); ?>>Kıdemli Personel</option>
									<option value="3" <?php selected($user->role, '3'); ?>>Birim Amiri</option>
									<option value="2" <?php selected($user->role, '2'); ?>>Yönetici</option>
									<?php if(user_access('superadmin')): ?><option value="1" <?php selected($user->role, '1'); ?>>Süper Yönetici</option><?php endif; ?>
								</select>
							</div> <!-- /.form-group -->
						</div> <!-- /.col-md-6 -->
					</div> <!-- /.row -->




					<?php if(user_access('admin')): ?>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="password">Şifre</label>
									<input type="password" name="password" id="password" class="form-control">
								</div> <!-- /.form-group -->
							</div> <!-- /.col-md-6 -->
							<div class="col-md-6">
								<div class="form-group">
									<label for="password_again">Şifre Tekrar</label>
									<input type="password" name="password_again" id="password_again" class="form-control">
								</div> <!-- /.form-group -->
							</div> <!-- /.col-md-6 -->
						</div> <!-- /.row -->

						
					<?php endif; ?>
				</div> <!-- /#is_til_login_div -->


				<div class="text-right">
					<input type="hidden" name="update_profile">
					<input type="hidden" name="uniquetime" value="<?php uniquetime(); ?>">
					<button class="btn btn-success btn-xs-block btn-save"><i class="fa fa-save"></i> Kaydet</button>
				</div> <!-- /.pull-right -->

				

			</div> <!-- /.col-md-6 -->
			<div class="col-md-2 hidden-xs">

				<label>&nbsp;</label>
				<br />

				<div class="img-thumbnail">
					<img src="<?php echo $user->avatar; ?>" class="img-responsive" style="width:180px; height:180px;">
					<?php if(user_access('admin')): ?>
						<div class="h-10"></div>
						<a href="?id=<?php echo $user->id; ?>&delete_avatar" class="pull-right text-danger fs-12"><i class="fa fa-trash"></i> Bu fotoğrafı sil</a>
					<?php endif; ?>
				</div>
				
				<?php if(user_access('admin')): ?>
					<div class="h-20"></div>
					<div class="form-group">
						<label for="avatar">Yeni bir fotoğraf yükle.</label>
						<input type="hidden" name="img_uniquetime" value="<?php usleep(1000); uniquetime(); ?>">
						<input type="file" name="avatar" id="avatar">
					</div> <!-- /.form-group -->
				<?php endif; ?>

			</div> <!-- /.col-md-2 -->
		</div> <!-- /.row -->


				

		</form>




	</div> <!-- /#home -->
	<!-- /tab:home -->

	<!-- tab:cv -->
	<div class="tab-pane" role="tabpanel" id="cv" aria-labelledy="cv-tab">
		<?php include('inc_user_cv.php'); ?>
	</div>
	<!-- /tab:cv -->

	<!-- tab:cv -->
	<div class="tab-pane" role="tabpanel" id="salary" aria-labelledy="salary-tab">
		<?php include('inc_user_salary.php'); ?>
	</div>
	<!-- /tab:cv -->


	<!-- tab:home -->
	<div class="tab-pane" role="tabpanel" id="logs" aria-labelledby="logs-tab"> 
		<?php theme_get_logs(" user_id='".$user->id."' "); ?>
	</div>
	<!-- tab:logs -->

</div> <!-- /.tab-content -->


<?php get_footer(); ?>