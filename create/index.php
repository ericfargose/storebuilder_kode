<?php session_start(); ?>
<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once('../config.php');
require_once('db_connect.php');
require_once('curlhandler.php');

$domain = DOMAIN;

if (isset($_POST['name'])) {
    $name = $_POST['name'];
} else {
    $name = '';
}

if (isset($_POST['password'])) {
    $password = $_POST['password'];
} else {
    $password = '';
}

if (isset($_POST['email'])) {
    $email = $_POST['email'];
} else {
    $email = '';
}

if (isset($_POST['password'])) {
    $password = $_POST['password'];
} else {
    $password = '';
}

if (isset($_POST['username'])) {
    $username = $_POST['username'];
} else {
    $username = '';
}

if(isset($_POST['store_url_type'])){
  $store_url_type = $_POST['store_url_type'];  
} else {
  $store_url_type = '';
}

$storeurl = '';
if($store_url_type != ''){
  if($store_url_type == 'subdir'){
      if(isset($_POST['subdir'])){
          $storeurl = $_POST['subdir'];
      } else {
          $storeurl = '';
      }
  } elseif($store_url_type == 'subdomain'){
      if(isset($_POST['subdomain'])){
          $storeurl = $_POST['subdomain'];
      } else {
          $storeurl = '';
      }
  } elseif($store_url_type == 'tld'){
      if(isset($_POST['tld'])){
          $storeurl = $_POST['tld'];
      } else {
          $storeurl = '';
      }
  }
}

if(isset($_POST['tld'])){
  $tld = $_POST['tld']; 
}else{
  $tld = '';
}



//errors 
$error_status = 0;
$error_name = '';
$error_store_url_type = '';
$error_storeurl = '';
$error_store_url_unique = '';
$error_email = '';
$error_username = '';
$error_password = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

  if ((strlen(utf8_decode($_POST['name'])) < 1) || (strlen(utf8_decode($_POST['name'])) > 64)) {
    $error_status = 1;
    $error_name = 'Store Name must be between 3 and 64 characters!';
  } else {

    $store_url = '';
    if (isset($_POST['subdomain'])) {
        $store_url = $_POST['subdomain'];
    } elseif(isset($_POST['subdir'])) {
        $store_url = $_POST['subdir'];
    } elseif(isset($_POST['tld'])){
        $store_url = $_POST['tld'];
    }

    $domain = DOMAIN;
    
    if(isset($_POST['subdir'])){
        $url = 'http://'.$domain.'/'.$store_url.'/';
        
        $already_store = CheckExists('sb_store', 'url', $url, $dbh1);
        if($already_store){
            $error_status = 1;
            $error_storeurl = 'This URL is taken';
        }

        $already_store = CheckExists('sb_store', 'name', $_POST['name'], $dbh1);
        if($already_store){
            $error_status = 1;
            $error_name = 'Store Name Already Exists';
        }

    } else {
        $already_store = CheckExists('sb_store', 'name', $_POST['name'], $dbh1);
        if($already_store){
            $error_status = 1;
            $error_name = 'Store Name Already Exists';
        }   

        $url = 'http://'.$domain.'/'.$store_url.'/';
        $already_store = CheckExists('sb_store', 'url', $url, $dbh1);
        $already_store = '';
        if($already_store){
            $error_status = 1;
            $error_storeurl = 'This URL is taken';
        }
    }
  }

  if (empty($_POST['store_url_type'])) {
    $error_status = 1;
    $error_store_url_type = 'Please select a url type!';
  } else{
    $pattern = '/[^a-zA-z0-9_\-]/';
    if (isset($_POST['subdomain'])) {
        $store_url = $_POST['subdomain'];
        $re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-._]+[a-zA-Z0-9]$/';
    } elseif(isset($_POST['subdir'])) {
        $store_url = $_POST['subdir'];
        $re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-._]+[a-zA-Z0-9]$/';
    } elseif(isset($_POST['tld'])){
        $store_url = $_POST['tld'];
        $store_url_explode = explode('.', $store_url);
        if(count($store_url_explode) == 2){
            $re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-]+\.[a-zA-Z0-9]{2,20}$/';
        } elseif(count($store_url_explode) == 3){
            $re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-]+\.[a-zA-Z0-9-._]+[^.]+[a-zA-Z0-9]$/';
        } else {
            $re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-]+\.[a-zA-Z0-9-._]+[^.]+[a-zA-Z0-9]{1,20}$/';    
        }
    }

    if (!preg_match($re, $store_url)) {
      $error_status = 1;
      $error_storeurl = 'Please select a valid URL!';
    }
      
  }

  if ((strlen(utf8_decode($_POST['email'])) < 1) || (strlen(utf8_decode($_POST['email'])) > 64)) {
    $error_status = 1;
    $error_email = 'Please enter valid Email ID';
  }

  $pattern = '/^[A-Z0-9-._%-+]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,20}$/i';

  if (!preg_match($pattern, $_POST['email'])) {
    $error_status = 1;
    $error_email = 'Please enter valid Email ID';
  }

  if ((strlen(utf8_decode($_POST['username'])) < 1) || (strlen(utf8_decode($_POST['username'])) > 64)) {
    $error_status = 1;
    $error_username = 'Please enter valid User name ';
  }

  if ((strlen(utf8_decode($_POST['password'])) < 1) || (strlen(utf8_decode($_POST['password'])) > 64)) {
    $error_status = 1;
    $error_password = 'Please enter valid Password';
  }

  if($error_status == 0){
    $data = pre_insert($_POST);
    $store_id = create_store($data, $dbh1);
    create_database($store_id, $data);
    $success = "Store created successfully. Check <a href='".$data['store_url']."''>Store front</a> & <a href='".$data['store_url']."admin'>Store admin</a> here.";
    $password = '';
    $name = '';
    $email = '';
  } 

}

$sub_dir = 'storebuilder.kp';

$text_dns_message = '(Please set your DNS entry to point to storebuilder.kp)';

function create_database($store_id, $data) {
  if($store_id) {
      $store = 'sb_'.$store_id;
      $data['db_new_name'] = $store; 
      $data['store_id'] = $store_id;
      CurlHandler::Request('dbcreationapi', 'createstore', $data);
      CurlHandler::Request('associatefolders', 'createfolders', $data);
  }
}

function create_store($data, $dbh1){
  $default_required = array(
      'name'              => $data['name'],
      'url'               => $data['store_url'],
      'email'             => $data['email'],
      'username'          => $data['username'],
      'password'          => $data['password']
  );

  $columns = array();
  $values = array();

  foreach($default_required as $key=>$value){
      $columns[] = $key ;
      $values[] = "'" . $value ."'";
  }

  $sql = "INSERT INTO `" . DB_PREFIX . "sb_store` (" . implode(", ",$columns) . ")  VALUES ( "  . implode(",",$values) . ")";
  $query = db_query($sql, $dbh1);
  
  $store_id = mysql_insert_id($dbh1);;

  $sql1 = "UPDATE `" . DB_PREFIX . "sb_store` SET `db_name` = 'sb_".$store_id."' WHERE `store_id` = '".$store_id."'";
  $query1 = db_query($sql1, $dbh1);

  $sql2 = "INSERT INTO `" . DB_PREFIX . "sb_xstore_meta` SET store_id = '" . (int)$store_id . "', url_mode = '".mysql_real_escape_string($data['url_type'], $dbh1) ."', domain = '" . mysql_real_escape_string($data['main_domain'], $dbh1) . "'";
  $query2 = db_query($sql2, $dbh1);

  return $store_id;
}

function pre_insert($data) {
  
  $sub_dir = DOMAIN;
  $part = explode('.',DOMAIN);
  if(sizeof($part) == 2){
      $domain = DOMAIN;
  }else{
      $domain = $part[sizeof($part)-2].'.'.$part[sizeof($part)-1];
  }
  $domain = DOMAIN;
  

  if(isset($data['subdir']) AND $data['subdir']){
      $data['subdir'] = 'http://'. $sub_dir .'/'.$data['subdir'].'/' ;
      $data['store_url'] = $data['subdir'];
      $data['url_type'] = 'subdir';
      $data['main_domain'] = $sub_dir;
  }

  if(isset($data['subdomain']) AND $data['subdomain']){
      $data['subdomain'] = 'http://' .$data['subdomain']. '.' . $domain .'/' ;
      $data['store_url'] = $data['subdomain'];
      $data['url_type'] = 'subdomain';
      $data['main_domain'] = $domain;
  }

  if(isset($data['tld']) AND $data['tld']){

      $data['tld'] = 'http://' .strtolower($data['tld']).'/';
      $data['store_url'] = $data['tld'] ;
      $data['url_type'] = 'tld';

      $url_data = parse_url(str_replace('&amp;', '&',$data['tld']));
      $part = explode('.',$url_data['host']);
      if(sizeof($part) == 2){
          $data['main_domain'] = $url_data['host'];
      }else{
          $data['main_domain'] = $part[sizeof($part)-2].'.'.$part[sizeof($part)-1];
      }
  }

  return $data;
}

function CheckExists($table,$field,$value, $dbh1){
  $sql = "SELECT count(*) as total FROM `".$table."` WHERE LOWER(`".$field."`) = '".strtolower($value)."'";
  $query = db_query($sql, $dbh1);
  if(isset($query[0]['total']) && $query[0]['total'] > 0){
    return $query[0]['total'];
  } else {
    return false;
  }
}

function db_query($sql, $db) {
  $query = mysql_query($sql,$db);
  $rows = array();
  if(is_resource($query)) {
    while($r = mysql_fetch_assoc($query)) {
        $rows[] = $r;
    }
  }
  return $rows;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>Store Builder</title>

<!-- Google fonts -->
<link href='http://fonts.googleapis.com/css?family=Raleway:400,800' rel='stylesheet' type='text/css'>

<!-- font awesome -->
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

<!-- bootstrap -->
<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css" />

<!-- animate.css -->
<link rel="stylesheet" href="assets/animate/animate.css" />

<!-- gallery -->
<link rel="stylesheet" href="assets/gallery/blueimp-gallery.min.css">

<!-- favicon -->
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
<link rel="icon" href="images/favicon.ico" type="image/x-icon">


<link rel="stylesheet" href="assets/style.css">

</head>

<body id="home">
<!-- header -->
      
       

      <h1 class="logo">Store Builder</h1>
      <?php /* ?>
      <div class="menu">
      <a href="#contact" class="scroll">Register</a>      
      </div>
      <?php */ ?>
      
      
      <div class="contact1" id="contact">
        <div class="bottom-search-area hide-sm">
          <div class="col-12">
            <?php if(isset($success)) { ?>
              <div class="text-success" style="text-align:center;margin-bottom:10px;color:#000;font-weight:bold"><?php echo $success; ?></div>
              <?php $success = ''; ?>
            <?php } ?>
            <form id="search_form" name="register" action="index.php" method="post" enctype="multipart/form-data" data-reactid=".0">
              <div class="form-group required" style="padding-bottom: 50px;">
                <label class="col-sm-2 control-label" for="input-model"><?php echo 'Store Name'; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="name" value="<?php echo $name ?>" placeholder="<?php echo 'Store Name'; ?>" id="input-model" class="form-control" />
                  <?php if ($error_name) { ?>
                  <div class="text-danger" style="float:left;margin-top:10px;margin-bottom:10px;"><?php echo $error_name; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group required" style="padding-bottom: 50px;">
                <label class="col-sm-2 control-label" for="input-model"><?php echo 'Store Url'; ?></label>
                <div class="col-sm-10">
                  <select name="store_url_type" id="store_url_type" class="form-control" style="background-color: #ffffff;">
                    <option value=""> --- Select Url Type--- </option>

                    <option id="subdir_select" class="subdir" value="subdir" <?php echo ($store_url_type == 'subdir') ? 'selected="selected"' : '' ?>> <?php echo 'Subdirectory' ?> </option>

                    <option id="subdomain_select" class="subdomain" value="subdomain" <?php echo ($store_url_type == 'subdomain') ? 'selected="selected"' : '' ?> > <?php echo 'subdomain' ?> </option>

                    <option id="tld_select" class="tld" value="tld" <?php echo ($store_url_type == 'tld') ? 'selected="selected"' : '' ?> > <?php echo 'External Domain' ?> </option>

                  </select>
                  <br/><br/>
                  <span class="subdir" style="font-weight:normal;display:none;float: left;">http://<?php echo $sub_dir . '/'; ?></span><input disabled="disabled" class="subdir" style="width:225px;display:none;float: left;height: 31px;margin-top: -9px;margin-bottom: 25px;" id="subdir" type="text" name="subdir" value="<?php echo  strtolower($storeurl); ?>" /><span class="subdir" style="font-weight:normal;display:none;float: left;">/</span>

                  <span class="subdomain" style="font-weight:normal;display:none;float: left;">http://</span><input style="width:225px;display:none;float: left;height: 31px;margin-top: -9px;margin-bottom: 25px;" class="subdomain" disabled="disabled" id="subdomain" type="text" name="subdomain" value="<?php echo  strtolower($storeurl); ?>" /><span class="subdomain" style="font-weight:normal;display:none;float: left;margin-right:20px;">.<?php echo $domain . '/'; ?></span><span class="subdomain" style="font-weight:normal;display:none;float:left;"><?php echo $text_dns_message; ?></span>

                  <span class="tld" style="font-weight:normal;display:none;float: left;">http://</span><input style="width:225px;display:none;float: left;height: 31px;margin-top: -9px;margin-bottom: 25px;" class="tld" id="tld" type="text" name="tld" disabled="disabled" value="<?php echo $tld ?>" value="<?php echo  strtolower($storeurl); ?>" /><span class="tld" style="font-weight:normal;display:none;float: left;margin-right:20px;">/</span><span class="tld" style="font-weight:normal;display:none;float:left;"><?php echo $text_dns_message; ?></span>

                  <?php if ($error_store_url_type) { ?>
                  <div class="text-danger" style="float:left;margin-top:10px;margin-bottom:10px;"><?php echo $error_store_url_type; ?></div>
                  <?php } ?>
                  <?php if ($error_storeurl) { ?>
                        <div class="text-danger" style="float:left;margin-left:20px;"><?php echo $error_storeurl; ?></div>
                  <?php }else if($error_store_url_unique) { ?>
                        <div class="text-danger" style="float:left;margin-left:20px;"><?php echo $error_store_url_unique; ?></div>
                  <?php }?>
                </div>
              </div> 
              <div class="form-group required" style="padding-bottom: 50px;">
                <label class="col-sm-2 control-label" for="input-model"><?php echo 'Email'; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="email" value="<?php echo $email; ?>" placeholder="<?php echo 'Email'; ?>" id="input-model" class="form-control" />
                  <?php if ($error_email) { ?>
                  <div class="text-danger" style="float:left;margin-top:10px;margin-bottom:10px;"><?php echo $error_email; ?></div>
                  <?php } ?>
                </div>
              </div> 
              <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
<input style="display:none" type="text" name="fakeusernameremembered"/>
<input style="display:none" type="password" name="fakepasswordremembered"/>
              <div class="form-group required" style="padding-bottom: 50px;">
                <label class="col-sm-2 control-label" for="input-model"><?php echo 'Username'; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="username" value="<?php echo $username; ?>" placeholder="<?php echo 'Username'; ?>" id="input-model" class="form-control" />
                  <?php if ($error_username) { ?>
                  <div class="text-danger" style="float:left;margin-top:10px;margin-bottom:10px;"><?php echo $error_username; ?></div>
                  <?php } ?>
                </div>
              </div> 

              <div class="form-group required" style="padding-bottom: 50px;">
                <label class="col-sm-2 control-label" for="input-model"><?php echo 'Password'; ?></label>
                <div class="col-sm-10">
                  <input type="password" name="password" value="<?php echo $password; ?>" placeholder="<?php echo 'Password'; ?>" id="input-model" class="form-control" autocomplete="false" />
                  <?php if ($error_password) { ?>
                  <div class="text-danger" style="float:left;margin-top:10px;margin-bottom:10px;"><?php echo $error_password; ?></div>
                  <?php } ?>
                </div>
              </div>
              <button type="submit" style="margin-top:40px;" class="search-button form-inline btn btn-primary btn-large" id="submit_location" data-reactid=".0.2">Register</button>
            </form>
          </div>
        </div>
      </div>
      <!-- footer -->
      <div class="footer text-center">
        <div class="social">
        <a href="#"><i class="fa fa-facebook fa-2x"></i></a>
        <a href="#"><i class="fa fa-instagram fa-2x"></i></a>
        <a href="#"><i class="fa fa-twitter fa-2x"></i></a>
        <a href="#"><i class="fa fa-pinterest fa-2x"></i></a>
        </div>
      Powered by: <a href="http://www.kodeplay.com">www.kodeplay.com</a>
      </div>
      <!-- footer -->


<?php /* ?>
<a href="#home" class="gototop scroll"><i class="fa fa-angle-up  fa-3x"></i></a>
<?php */ ?>

<!-- jquery -->
<script src="assets/jquery.js"></script>

<!-- boostrap -->
<script src="assets/bootstrap/js/bootstrap.js" type="text/javascript" ></script>
 

<script type="text/javascript"><!--

$(document).ready(function(){
  
  $('#store_type').change();

  var store_type = '<?php echo $store_url_type;?>';
  if(store_type != '')
  {
    $('.'+store_type).show();
    $('.'+store_type).attr('disabled', false);    
  }
  
});


$('#store_url_type').change(function() {
  if($(this).val() === ''){
    $(this).siblings().hide();
    $(this).siblings().filter("input:text").removeClass('require');
  }
  else{
    var name = '';
    if($('#subdir').val() != ''){
      var name = $('#subdir').val();
      $('#subdir').val('');
    } else if($('#subdomain').val() != ''){
      var name = $('#subdomain').val();
      $('#subdomain').val('');
    } else if($('#tld').val() != ''){
      var name = $('#tld').val();
      $('#tld').val('');
    }
    
    var url_name = removeSpaces(name);
    $(this).siblings().hide();
    $(this).siblings().filter("br").show();
    $(this).siblings().filter("input:text").removeClass('require');
    $(this).siblings().filter("input:text").attr('disabled', 'disabled');
    $(this).siblings().filter("input:text").val(url_name.toLowerCase());
    $(this).siblings().filter("."+$(this).val()).show();
    $(this).siblings().filter("#"+$(this).val()).addClass('require');
    $(this).siblings().filter("#"+$(this).val()).attr('disabled', false).focus(); 
    
    if($(this).val() == 'subdomain'){
      $('#finalurl').text('http://' +url_name.toLowerCase()+ '.<?php echo $domain;?>/');
    }
    else if($(this).val() == 'subdir'){
      $('#finalurl').text('http://<?php echo $sub_dir;?>/'+url_name.toLowerCase()+'/');
    }else{
      $('#finalurl').text('http://'+url_name.toLowerCase()+'/');
    }
    $('#finaldiv').show();
  }
  
  
    
});

$('#subdomain, #subdir').keyup(function(e) {
  var val = $(this).val();
  var url_name = removeSpaces(val);
  $("input[name='subdomain']").val(url_name.toLowerCase());
  $("input[name='subdir']").val(url_name.toLowerCase());
  var type = $('#store_url_type').val();
  if(type == 'subdomain'){
    $('#finalurl').text('http://' +url_name.toLowerCase()+ '.<?php echo $domain;?>/');
  }
  else if(type == 'subdir'){
    $('#finalurl').text('http://<?php echo $sub_dir;?>/'+url_name.toLowerCase()+'/');
  }
  
});

$('#tld').keyup(function(e) {
  var val = $(this).val();
  var url_name = removeSpaces(val);
  $("input[name='subdomain']").val(url_name.toLowerCase());
  $("input[name='subdir']").val(url_name.toLowerCase());
  $('#finalurl').text('http://'+url_name.toLowerCase()+'/');
});

function removeSpaces(string) {
 return string.split(' ').join('');
}

//--></script> 

</body>
</html>