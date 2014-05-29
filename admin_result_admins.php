<?php
  require_once 'includes/_header.php';
  $Auth->allow('admin');

  require_once 'class/Admin.class.php';
  require_once 'class/ListAdmins.class.php';


  $dataForm = array();
  if (isset($_GET['options'],$_GET['action'],$_GET['recherche1'],$_GET['recherche2'])){
    $dataForm = $_GET;$_POST=$_GET;
  }else
    $dataForm = $_POST;
  Admin::check_global_actions();
  $ListAdmins = new ListAdmins($dataForm);

  echo $ListAdmins->getAdminAsTr();
?>

<script>
  jQuery(function() {
    $('.adminCount').each(function(event) {
      $(this).html(<?= '"'.$ListAdmins->countSqlReturnedAdmins.'/'.$ListAdmins->countAdmins.'"' ?>);
    });
    $('.pagination-container').each(function(event) {
      var pagination = '<?= $ListAdmins->getPagination(1); ?>';
      $(this).html(pagination);
    });
    $('input[name="page"]').val(<?= $ListAdmins->page; ?>);

    $(".page").click(function(event){
      pageHiddenInput.val($(this).attr('id').replace('p',''));
      refreshAdminList();
      return false;
    });
  });
</script>