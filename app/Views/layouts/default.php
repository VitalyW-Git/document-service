<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title><?= $this->renderSection('title') ?></title>
        <meta name="description" content="<?= $description ?? 'The small framework with powerful features' ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" type="image/png" href="/favicon.ico">
        <link href="/assets/global/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>

        <?= $this->renderSection('content') ?>

        <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>

        <script src="/assets/global/jquery/jquery.min.js"></script>
        <script src="/assets/global/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script>
            window.appConfig = { baseUrl: <?= json_encode(base_url()) ?> };
        </script>
        <?= $this->renderSection('scripts') ?>
    </body>
</html>

