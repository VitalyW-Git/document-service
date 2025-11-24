<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title><?= $title ?? 'Welcome to CodeIgniter 4!' ?></title>
        <meta name="description" content="<?= $description ?? 'The small framework with powerful features' ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" type="image/png" href="/favicon.ico">
        <link href="/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>

        <?= $content ?>

        <script src="/assets/jquery/jquery.min.js"></script>
        <script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>

