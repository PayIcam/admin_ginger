<?php

/**
 * Permet de de gérer l'ajout de média via TinyMCE à un article
 */

    require_once 'includes/_header.php';
    $ID_BDS=1;
    $title='Ajouter un évènement';
    require_once 'includes/Forms.class.php';
    $form = new form();

    $id=isset($_GET['id'])?$_GET['id']:0;
    $dir_base = '../img/uploads/';
    if (!file_exists($dir_base)) mkdir($dir_base,0777);

    if (isset ($_FILES['file'],$_POST['name']) && !empty($_FILES['file']['name'])) {
        if (strpos($_FILES['file']['type'], 'image') !== false) {
            $dir = $dir_base.(($id==12)?'':date('Y-m'));
            if (!file_exists($dir)) mkdir($dir,0777);
            move_uploaded_file($_FILES['file']['tmp_name'], $dir.DS.$_FILES['file']['name']);
            $DB->save('medias',
                array('name'=>$_POST['name'],'file'=> (($id==12)?'':date('Y-m').'/').$_FILES['file']['name'],'post_id'=>$id,'type'=>'img'),'insert');
            Functions::setFlash("L'image a bien été uplodée");
        }else{
            $form->errors['file'] = 'le fichier n\'est pas une image !';
        }
    }

    if (isset($_GET['delete']) && !empty($_GET['delete'])) {
        $media = $DB->findFirst('medias',array('conditions'=>array('id'=>$_GET['delete'])));
        unlink($dir_base.$media['file']);
        $DB->delete('medias',array('id'=>$_GET['delete']));
        Functions::setFlash('Le media <em>'.$media['name'].'</em> a bien été supprimé');
    }
    echo Functions::flash();

    $images = $DB->find('medias',array('conditions'=>array('type'=>'img',(($id==12)?'post_id = 12':'post_id != 12'))));
?>
<table>
    <thead>
        <tr>
            <th></th>
            <th>Titre</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty ($images)){ foreach ($images as $k => $v): ?>
            <tr>
                <td>
                    <a href="#" onclick="FileBrowserDialogue.sendURL('<?= $dir_base.$v['file']; ?>');">
                        <img src="<?= $dir_base.$v['file']; ?>" title="<?= $v['name']; ?>" style="max-height: 100px;max-width: 200px;">
                    </a>
                </td>
                <td><?= $v['name']; ?></td>
                <td>
                    <a onclick="return confirm('Voulez vous vraiment supprimer cette image'); " href="<?= 'admin_index.php?id='.$id.'&delete='.$v['id']; ?>">Supprimer</a>
                </td>
            </tr>
        <?php endforeach;} ?>
    </tbody>
</table>


<div class="page-header">
	<h1>Ajouter une image</h1>
</div>

<form action="<?= 'admin_index.php?id='.$id; ?>" method="post" enctype="multipart/form-data">
    <?= $form->input('file','Image',array('type'=>'file')); ?>
    <?= $form->input('name','Nom'); ?>

    <div class="actions">
        <button class="btn primary" type="submit">Envoyer</button>
        &nbsp;
        <button class="btn btn-default" type="reset">Cancel</button>
    </div>
</form>

<script type="text/javascript" src="<?= 'js/tinymce/tiny_mce_popup.js'; ?>"></script>
<script type="text/javascript">
    var FileBrowserDialogue = {
        init : function () {
            // Here goes your code for setting your custom things onLoad.
        },
        sendURL : function (URL) {
            var win = tinyMCEPopup.getWindowArg("window");

            // insert information now
            win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

            // are we an image browser
            if (typeof(win.ImageDialog) != "undefined") {
                // we are, so update image dimensions...
                if (win.ImageDialog.getImageData)
                    win.ImageDialog.getImageData();

                // ... and preview if necessary
                if (win.ImageDialog.showPreviewImage)
                    win.ImageDialog.showPreviewImage(URL);
            }

            // close popup window
            tinyMCEPopup.close();
        }
    }
</script>