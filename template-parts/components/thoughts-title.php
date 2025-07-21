<?php
/**
 * Thoughts Title Component
 * 
 * @param array $args {
 *     Optional. Array of arguments.
 *     @type string $title Title text content.
 * }
 */

$title = $args['title'] ?? '';
?>

<div class="thoughts-title">
    <h2 class="thoughts-title__text">
        <?php 
        // <br>タグで分割して各行を処理
        $lines = preg_split('/<br\s*\/?>/i', $title);
        foreach ($lines as $line) {
            if (!empty(trim($line))) {
                echo '<span class="thoughts-title__line">';
                echo esc_html(trim($line));
                echo '<img class="thoughts-title__underline" src="' . get_template_directory_uri() . '/assets/images/underline.svg" alt="" />';
                echo '</span>';
            }
        }
        ?>
    </h2>
</div>