/**
 * Fonction pour cocher toutes les chekboxes d'un coup
**/
function toggleChecked(status) {
  jQuery(".checkbox").each( function() {
    jQuery(this).attr("checked",status);
  });
}
/**
 * Fonction pour uniformiser les select (qu'ils soient égaux)
**/
jQuery(function($){
  $("#action1").change(function() {
    $("#action2").val($(this).val()).attr("selected","selected");
  });
  $("#action2").change(function() {
    $("#action1").val($(this).val()).attr("selected","selected");
  });
});
/**
 * Fonction de recherche
**/
jQuery('#recherche').keyup(function() {
  var recherche = $(this).val();
  var data = 'motclef=' + recherche;
  $.ajax({
    type : "GET",
    url : "admin_result_users.php",
    data : data,
    success: function(server_response){
      $("#resultat").html(server_response).show();
      $("#recherche2").val(recherche);
    }
  });
});

jQuery('#recherche2').keyup(function() {
  var recherche = $(this).val();
  var data = 'motclef=' + recherche;
  $.ajax({
    type : "GET",
    url : "admin_result_users.php",
    data : data,
    success: function(server_response){
      $("#resultat").html(server_response).show();
      $("#recherche").val(recherche);
    }
  });
});