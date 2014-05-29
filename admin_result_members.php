<?php
  require_once 'includes/_header.php';
  $Auth->allow('member');

  require_once 'class/Member.class.php';
  require_once 'class/ListMembers.class.php';


  $dataForm = array();
  if (isset($_GET['options'],$_GET['action'],$_GET['recherche1'],$_GET['recherche2'])){
    $dataForm = $_GET;$_POST=$_GET;
  }else
    $dataForm = $_POST;
  Member::check_global_actions();
  $ListMembers = new ListMembers($dataForm);

  echo $ListMembers->getMemberAsTr();
?>

<script>
  jQuery(function() {
    $('.memberCount').each(function(event) {
      $(this).html(<?= '"'.$ListMembers->countSqlReturnedMembers.'/'.$ListMembers->countMembers.'"' ?>);
    });
    $('.pagination-container').each(function(event) {
      var pagination = '<?= $ListMembers->getPagination(1); ?>';
      $(this).html(pagination);
    });
    $('input[name="page"]').val(<?= $ListMembers->page; ?>);

    $(".page").click(function(event){
      pageHiddenInput.val($(this).attr('id').replace('p',''));
      refreshMemberList();
      return false;
    });
  });
</script>