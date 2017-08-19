<?php echo $this->translate('DBname'); ?>:
<?php echo $this->escape($GLOBALS['KIMAI_CONF_VARS']['DB']['database']);?>

<br /><br />

<?php echo $this->translate('DButf8');?>:
<?php if ($GLOBALS['KIMAI_CONF_VARS']['DB']['charset'] === 'utf8') {
    echo $this->translate('yes');
} else {
    echo $this->translate('no');
} ?>

<?php if (file_exists(WEBROOT . '/updater/db_restore.php')) { ?>
    <br /><br />
    <a href="../updater/db_restore.php"><?php echo $this->translate('DBbackup') ?></a>
<?php } ?>
