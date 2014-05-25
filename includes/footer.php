<hr>

<footer class="footer">
    <div class="clearfix">
        <em>Administration par Antoine Giraud <small>115</small> pour IDiiL :)</em>
        <a class="pull-right" href="#">Back to top</a>
    </div>
</footer>

</div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    
    <?php if (!empty($required_script)): ?>
        <?php foreach ($required_script as $v): ?>
            <script src="js/<?= $v; ?>"></script>
        <?php endforeach ?>
    <?php endif ?>
  </body>
</html>