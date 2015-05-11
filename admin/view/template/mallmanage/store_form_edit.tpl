<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-store" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_form; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-store" class="form-horizontal">
          <div class="tab-content">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-model"><?php echo $entry_name; ?></label>
                <div class="col-sm-10">
                  <input type="text" name="name" value="<?php echo $name; ?>" placeholder="<?php echo $entry_name; ?>" id="input-model" class="form-control" readonly="readonly" />
                  <?php if ($error_name) { ?>
                  <div class="text-danger"><?php echo $error_name; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-model"><?php echo $entry_url; ?></label>
                <div class="col-sm-10">
                  <select name="store_url_type" id="store_url_type" class="form-control" disabled="disabled">
                    <option value=""> --- Select Url Type--- </option>
                    <option id="subdir_select" class="subdir" value="subdir" <?php echo ($store_url_type == 'subdir') ? 'selected="selected"' : '' ?>> <?php echo $text_subdirectory ?> </option>
                    <option id="subdomain_select" class="subdomain" value="subdomain" <?php echo ($store_url_type == 'subdomain') ? 'selected="selected"' : '' ?> > <?php echo $text_subdomain ?> </option>
                    <option id="tld_select" class="tld" value="tld" <?php echo ($store_url_type == 'tld') ? 'selected="selected"' : '' ?> > <?php echo $text_external_domain ?> </option>
                  </select>
                  <input type="hidden" name="store_url_type" value="<?php echo $store_url_type; ?>" id="input-store_url_type" class="form-control" />
                  <br/><br/>
                  
                  <?php echo  strtolower($storeurl); ?>

                  <?php /* ?>
                  <span class="subdir" style="font-weight:normal;display:none;">http://<?php echo $sub_dir . '/'; ?></span><input disabled="disabled" class="subdir" style="width:225px;display:none;" id="subdir" type="text" name="subdir" value="<?php echo  strtolower($storeurl); ?>" /><span class="subdir" style="font-weight:normal;display:none;">/</span>
                  <span class="subdomain" style="font-weight:normal;display:none;">http://</span><input style="display:none;" class="subdomain" disabled="disabled" id="subdomain" type="text" name="subdomain" value="<?php echo  strtolower($storeurl); ?>" /><span class="subdomain" style="font-weight:normal;display:none;">.<?php echo $domain . '/'; ?><br /><?php echo $text_dns_message; ?></span>
                  <span class="tld" style="font-weight:normal;display:none;">http://</span><input style="display:none;" class="tld" id="tld" type="text" name="tld" disabled="disabled" value="<?php echo $tld ?>" value="<?php echo  strtolower($storeurl); ?>" /><span class="tld" style="font-weight:normal;display:none;">/<br /><?php echo $text_dns_message; ?></span>
                  <?php if ($error_store_url_type) { ?>
                  <div class="text-danger"><?php echo $error_store_url_type; ?></div>
                  <?php } ?>
                  <?php if ($error_storeurl) { ?>
                        <div class="text-danger"><?php echo $error_storeurl; ?></div>
                  <?php }else if($error_store_url_unique) { ?>
                        <div class="text-danger"><?php echo $error_store_url_unique; ?></div>
                  <?php }?>
                  <?php */ ?>
                </div>
              </div> 
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-model"><?php echo $entry_status; ?></label>
                <div class="col-sm-10">
                  <select name="store_status" id="store_status" class="form-control">
                    <option value="active" <?php echo ($store_status == 'active') ? 'selected="selected"' : '' ?>> <?php echo $text_active ?> </option>
                    <option value="disabled" <?php echo ($store_status == 'disabled') ? 'selected="selected"' : '' ?> > <?php echo $text_inactive ?> </option>
                  </select>
                </div>
              </div>      
          </div>
        </form>
      </div>
    </div>
  </div>
  <script type="text/javascript"><!--

$(document).ready(function(){
    //$('.plan,.payment').hide();

    $('#store_type').change();

    var store_type = '<?php echo $store_url_type;?>';
    if(store_type != '')
    {
      $('.'+store_type).show();
      $('.'+store_type).attr('disabled', false);    
    }
    // if(store_type == 'Light Store'){
    //   $('.tld').hide();
    //         console.log($('input[name=email]'));
    // }
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
  
  </div>
<?php echo $footer; ?> 