 <footer>
          &copy; <?php echo date('Y'); ?> FUTA Health Centre Management System
      </footer>

<script src="<?php echo url_wrap('/assets/js/staff.js'); ?>"></script>

<?php if(isset($specificJs)){ ?>

<script src="<?php echo url_wrap($specificJs); ?>"></script>

<?php } ?>

  </body>
</html>

<?php
    db_0($db_1); // closes the database connection
?>