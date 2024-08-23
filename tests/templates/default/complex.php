<?php require 'header.php'; ?>

<body>
    <h1><?= $headline; ?></h1>
    <table>
        <?php foreach ($array as $key => $value): ?>
            <tr>
                <td><?= $this->esc($key); ?></td>
                <?php foreach ($value as $item): ?>
                    <td><?= $item; ?></td>
                <?php endforeach ?>
            </tr>
        <?php endforeach ?>
    </table><?= $html->unwrap(); ?>
</body>

</html>
