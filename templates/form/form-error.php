<?php
/**
 * @var string $formID
 * @var string $globalErrorClass
 * @var string $errorMessage
 * @var string $inlineCss (sanitized)
 */
?>

<?php if ($inlineCss): ?>
    <style>
        <?php echo $inlineCss ?>
    </style>
<?php endif ?>

<div id="<?php echo $formID ?>" class="lsb-form-global-error <?php echo $globalErrorClass ?>">
    <?php echo esc_html($errorMessage) ?>
</div>