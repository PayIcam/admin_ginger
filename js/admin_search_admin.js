// ---------- Variables propres au formulaire ---------- //
var searchForm = jQuery('#form');
var searchInput1 = jQuery('#recherche1');
var pageHiddenInput = jQuery('input[name="page"]');

// ------------------------------ Fonctions pour les requetes Ajax ------------------------------ //
var xhr = new Array();

function checkXhr(xhrName){
  if(xhr[xhrName]){
    xhr[xhrName].abort();
    delete xhr[xhrName];
  }
}

// ------------------------------ Autres Fonctions ------------------------------ //
function checkPopover () {
  jQuery(function($){
    $('#adminsList a[rel="popover"]').each(function(){
      var nom = $(this).next('.infos').find('.nom').html();
      var message = $(this).next('.infos').find('.message').html();
      $(this).popover({
        'content':message,
        'nom':nom,
        'placement':'left',
        'trigger':'hover',
        'html':true
      });
    });
  });
}
checkPopover();

var loader = jQuery('.loader');
function showLoader () {
  loader.each(function(event) {
    jQuery(this).fadeIn();
  });
}
function hideLoader () {
  loader.each(function(event) {
    jQuery(this).fadeOut();
  });
}

function refreshAdminList() {
  showLoader();
  checkXhr('adminId');
  xhr['adminId'] = jQuery.ajax({
    type : "POST",
    url : "admin_result_admins.php",
    data : searchForm.serialize(),
    success: function(server_response){
      jQuery("#resultat").empty().html(server_response).show();
      checkPopover();
      hideLoader();
    }
  });
}

/**
 * Fonction pour cocher toutes les chekboxes d'un coup
**/
function toggleChecked(status) {
  jQuery(".checkbox").each( function() {
    jQuery(this).attr("checked",status);
  });
}

(function($){


  /**
   * Fonction pour uniformiser les select (qu'ils soient égaux)
  **/
  $(function($){
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
  $('.search-query').each(function(event) {
    var searchInput = $(this);
    var searchInputVal = $(this).val();
    searchInput.keyup(function() {
      var recherche = searchInput.val();
      $('.search-query').each(function(event) {
        $(this).val(recherche)
      });
      refreshAdminList();
    });
  });

  searchForm.submit(function(event) {
    refreshAdminList();
    return false;
  });

  $(".page").click(function(event){
    pageHiddenInput.val($(this).attr('id').replace('p',''));
    refreshAdminList();
    return false;
  });
  
  $("#export").click(function(event){

    document.location.href="export_liste_admins.php?"+searchForm.serialize();
    return false;
  });

  var buttonsRadio = $('.buttons-radio');
  buttonsRadio.change(function(event) {
    var thisAttr = $(this).attr("checked");
    $('.buttons-radio').not('selector expression').each(function(event) {
      $(this).removeAttr("checked");
    });
    $(this).attr("checked",thisAttr);
  });


  /* Si l'on veut une recherche avancée : ici par type
  var selectTypes = $('#selectTypes');
  var selectAllTypesCheckbox = $('#selectAllTypes');

  // Quand on édite le select, on décoche sélectionner toutes les types
  selectTypes.change(function(event) {
    selectAllTypesCheckbox.removeAttr("checked");
    selectTypes.find('option:not(:selected)').each(function() {
      $(this).removeAttr("selected");
    });
  });
  selectAllTypesCheckbox.click(function(event) {
    if ($(this).attr("checked")){
      selectTypes.find('option').each(function() {
        $(this).attr("selected","selected");
      });
    }else{
      selectTypes.find('option').each(function() {
        $(this).removeAttr("selected");
      });
    };
  });
  //*/

})(jQuery);