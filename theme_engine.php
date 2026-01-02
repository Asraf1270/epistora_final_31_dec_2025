<?php
// Included at the top of all public pages

require_once 'db_engine.php';

$theme = [
    'bg' => '#f4f4f4',
    'font' => 'sans-serif',
    'size' => '16px'
];

if (isset($_SESSION['user_id'])) {
    $vault = DBEngine::readJSON("user_data/" . $_SESSION['user_id'] . ".json");
    if ($vault && isset($vault['settings'])) {
        $theme['bg'] = $vault['settings']['bg_color'];
        $theme['font'] = $vault['settings']['font_style'];
        $theme['size'] = $vault['settings']['font_size'];
    }
}
?>
<style>
:root {
    --bg-color: <?= $theme['bg'] ?>;
    --main-font: <?= $theme['font'] ?>;
    --main-size: <?= $theme['size'] ?>;
}

body {
    background-color: var(--bg-color);
    font-family: var(--main-font);
    font-size: var(--main-size);
    transition: background 0.3s ease;
}
</style>