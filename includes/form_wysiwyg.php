<script type="text/javascript" src="js/tinymce/tiny_mce.js"></script>
<script type="text/javascript">
    tinyMCE.init({
            // General options
            mode : "specific_textareas",
            editor_selector : "wysiwyg",
            theme : "advanced",
            relative_urls : false,
            plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

            // Theme options
            theme_advanced_buttons1 : "bold,italic,underline,strikethrough,forecolor,|,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect",
            theme_advanced_buttons2 : "bullist,numlist,|,indent,outdent,indent,blockquote,|,link,unlink,image,charmap,cleanup,code,|,insertdate,inserttime,preview",
            theme_advanced_buttons3 : "",
            theme_advanced_buttons4 : "",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            theme_advanced_resizing : true,
            width: 600,
            height: 450,

            // Skin options
            skin : "o2k7",
            skin_variant : "silver",
            file_browser_callback : 'fileBrowser',

            // Example content CSS (should be your site CSS)
            content_css : "http://getbootstrap.com/1.3.0/bootstrap.css"
    });

    function fileBrowser(field_name, url, type, win){
        if (type == 'file') {
            var explorer = 'admin_index.php<?php if(isset($_GET['id'])){ echo '?id='.$_GET['id'];} ?>';
        }else{
            var explorer = 'admin_index.php<?php if(isset($_GET['id'])){ echo '?id='.$_GET['id'];} ?>';
        }
        tinyMCE.activeEditor.windowManager.open({
            file : explorer,
            title : 'Gallerie',
            width: 500,
            height: 450,
            resizable : 'yes',
            inline : 'yes',
            close_previous : 'no'
        },{
            window : win,
            input : field_name
        });
        return false;
    }
</script>