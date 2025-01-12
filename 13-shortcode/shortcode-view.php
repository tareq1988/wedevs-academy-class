<div class="shortcode-wrapper form-<?php echo $args['id']; ?>">
    <h2><?php echo esc_html($args['title']); ?></h2>

    <?php if ($content): ?>
        <p><?php echo $content; ?></p>
    <?php endif; ?>
</div>

<style>
    .shortcode-wrapper {
        padding: 20px;
        border-radius: 6px;
    }

    .form-<?php echo $args['id']; ?> {
        border: 1px solid <?php echo $args['border_color']; ?>;
    }

    .columns-wrapper {
        display: flex;
        width: 100%;
        justify-content: space-between;
    }
</style>