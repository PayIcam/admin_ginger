<hr>

<footer class="footer">
    <p class="clearfix">
        <em>Administration par <a href="mailto:antoine_giraud2@hotmail.fr">Antoine Giraud</a> <small>115</small> pour PayIcam :)</em>
        <a class="pull-right" href="#">Back to top</a>
    </p>
</footer>

</div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    
    
    <?php if (!empty($js_for_layout)): ?>
      <?php foreach ($js_for_layout as $v):?>
        <?php if (file_exists('js/'.$v.'.js')){ ?>
          <script src="js/<?= $v; ?>.js"></script>
        <?php }elseif(file_exists('js/'.$v)){ ?>
          <script src="js/<?= $v; ?>"></script>
        <?php }elseif(false !== strpos($v, '<script type="text/javascript">')){ ?>
            <?= $v ?>
        <?php }else{ ?>
          <script type="text/javascript">

          </script>
        <?php } ?>
      <?php endforeach ?>
    <?php endif ?>
  </body>
</html>