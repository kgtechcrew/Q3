<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo Yii::t('ui', 'portal_title'); ?></title>
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons">
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/font-awesome.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" />
        <script src="<?php echo Yii::app()->request->baseUrl; ?>/scripts/validation.js" type="text/javascript"></script>
        <script src="<?php echo Yii::app()->request->baseUrl; ?>/scripts/ajaxRequest.js" type="text/javascript"></script>

    </head>
    <body>
        <?php echo $content; ?>
    </body>
    <!-- partial -->
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/scripts/jquery.min.js" type="text/javascript"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/scripts/bootstrap.min.js" type="text/javascript"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/scripts/script.js" type="text/javascript"></script>
</html>
