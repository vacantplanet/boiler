<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="css/style.css">
    <?php if ($this->has('script')) : ?>
        <?php echo $this->section('script'); ?>
    <?php endif ?>
</head>

<body id="home">
    <?= $this->body() ?>
</body>

</html>
