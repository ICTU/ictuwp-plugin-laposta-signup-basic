<?php
/**
 * @var string $listId (valid id)
 * @var array $listFields
 * @var string $formClass (sanitized)
 * @var string $fieldWrapperClass (sanitized)
 * @var string $labelClass (sanitized)
 * @var string $inputClass (sanitized)
 * @var string $selectClass (sanitized)
 * @var string $checksWrapperClass (sanitized)
 * @var string $checkWrapperClass (sanitized)
 * @var string $checkInputClass (sanitized)
 * @var string $checkLabelClass (sanitized)
 * @var string $submitButtonClass (sanitized)
 * @var string $submitButtonText (sanitized)
 * @var string $inlineCss (sanitized)
 * @var array $fieldValues (sanitized)
 * @var bool $hasErrors
 * @var string $globalError (sanitized)
 * @var bool $hasDateFields
 * @var string $globalErrorClass (sanitized)
 * @var string $fieldNameHoneypot
 * @var string $fieldNameNonce
 * @var string $nonce
 *
 */

/**
 * NOTE: GC CHANGES / OVERRIDES
 * We changed the following to (mainly) improve accessibility:
 * Much rationale is explained in https://www.smashingmagazine.com/2023/02/guide-accessible-form-validation/
 * - Replace `required` attribute with `aria-required="true"` on all required fields
 * - Add aria-hidden to 'required' star
 * - Add `aria-describedby` pointing to error message on all required fields
 * - Add `aria-live="assertive"` to error messages
 * 
 * TODO:
 * - Add basic JS validation (email and empty only?)
 * - Add global GC classnames to all form fields
 */
?>

<?php if ($inlineCss): ?>
    <style>
        <?php echo $inlineCss ?>
    </style>
<?php endif ?>
<form class="<?php echo $formClass ?> lsb-list-id-<?php echo $listId ?>" method="post">

    <?php if ($globalError): ?>
        <div class="lsb-form-global-error <?php echo $globalErrorClass ?>">
            <p class="form-feedback-msg" aria-live="assertive"><?php echo $globalError ?></p>
        </div>
    <?php endif ?>

    <?php foreach ($listFields as $field): ?>
        <?php
            $fieldType = $field['datatype'];
            if ($field['is_email']) {
                $fieldType = 'email';
            }
            elseif ($fieldType === 'select_single') {
                $fieldType = $field['datatype_display'] === 'radio' ? 'radio' : 'select';
            }
            elseif ($fieldType === 'select_multiple') {
                $fieldType = 'checkbox';
            }
            elseif ($fieldType === 'numeric') {
                $fieldType = 'number';
            }
            $fieldId = esc_attr($field['field_id']);
            $fieldKey = esc_attr($field['key']);
            $uniqueFieldKey = $listId.$fieldId;
            $fieldName = "lsb[$listId][$fieldKey]";
            if ($fieldType === 'checkbox') {
                $fieldName = $fieldName.'[]';
            }
            $fieldValue = $fieldValues[$field['key']]; // already sanitized
            $label = esc_html($field['name']);
        ?>
        <div class="<?php echo $fieldWrapperClass ?> lsb-field-tag-<?php echo esc_attr($field['key']) ?> lsb-field-type-<?php echo $fieldType ?>">

            <?php if ($fieldType === 'select'): ?>
                <label for="<?php echo $uniqueFieldKey ?>" class="<?php echo $labelClass ?>">
                    <?php echo $label ?>
                    <?php if ($field['required']): ?>
                        <span class="<?php echo $labelClass ?>__required-star" aria-hidden="true">*</span>
                    <?php endif; ?>
                </label>
                <select
                    class="<?php echo $selectClass ?>"
                    id="<?php echo $uniqueFieldKey ?>"
                    name="<?php echo $fieldName ?>"
                    <?php if ($field['required']): ?>aria-required="true" aria-describedby="<?php echo $uniqueFieldKey ?>__error-message"<?php endif ?>
                >
                    <option value=""><?php _x('Maak een keuze', 'ictuwp-plugin-laposta-signup-basic field: select option', 'gctheme') ?></option>
                    <?php foreach ($field['options_full'] as $option): ?>
                        <option
                            value="<?php echo esc_html($option['value']) ?>"
                            <?php if ($fieldValue === $option['value']): ?>selected="selected"<?php endif ?>>
                            <?php echo esc_html($option['value']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
                <?php if ($field['required']): ?><p class="<?php echo $selectClass ?>__error-message" id="<?php echo $uniqueFieldKey ?>__error-message" aria-live="assertive"></p><?php endif ?>

            <?php elseif ($fieldType === 'radio' || $fieldType === 'checkbox'): ?>
                <p class="<?php echo $labelClass ?>">
                    <?php echo $label ?>
                    <?php if ($field['required']): ?>
                        <span class="<?php echo $labelClass ?>__required-star" aria-hidden="true">*</span>
                    <?php endif; ?>
                </p>
                <div class="<?php echo $checksWrapperClass ?>">
                    <?php foreach ($field['options_full'] as $check): ?>
                        <?php
                            $checked =
                                ($fieldValue === $check['value']) ||
                                (is_array($fieldValue) && in_array($check['value'], $fieldValue));
                        ?>
                        <div class="<?php echo $checkWrapperClass ?> id-<?php echo esc_attr($check['id']) ?>">
                            <input
                                class="<?php echo $checkInputClass ?>"
                                id="<?php echo esc_attr($uniqueFieldKey.$check['id']) ?>"
                                type="<?php echo $fieldType ?>"
                                value="<?php echo esc_attr($check['value']) ?>"
                                name="<?php echo $fieldName ?>"
                                <?php if ($checked): ?>checked="checked"<?php endif ?>
                            >
                            <label
                                for="<?php echo esc_attr($uniqueFieldKey.$check['id']) ?>"
                                class="<?php echo $checkLabelClass ?>">
                                <?php echo esc_html($check['value']) ?>
                            </label>
                        </div>
                    <?php endforeach ?>
                </div>

            <?php else: ?>
                <?php
                    if (!in_array($fieldType, ['text', 'email', 'number', 'date'])) {
                        // fallback to text
                        $fieldType = 'text';
                    }
                ?>
                <label for="<?php echo $uniqueFieldKey ?>" class="<?php echo $labelClass ?>">
                    <?php echo $label ?>
                    <?php if ($field['required']): ?>
                        <span class="<?php echo $labelClass ?>__required-star" aria-hidden="true">*</span>
                    <?php endif; ?>
                </label>
                <input
                    id="<?php echo $uniqueFieldKey ?>"
                    type="<?php echo $fieldType === 'date' ? 'text' : $fieldType // avoid browser specific behavior ?>"
                    class="<?php echo $inputClass ?> <?php if ($fieldType === 'date'): ?>js-lsb-datepicker<?php endif ?>"
                    value="<?php echo $fieldValue ?>"
                    name="<?php echo $fieldName ?>"
                    <?php if ($field['required']): ?>aria-required="true" aria-describedby="<?php echo $uniqueFieldKey ?>__error-message"<?php endif ?>
                    <?php if ($fieldType === 'number'): ?>step="any"<?php endif ?>
                    <?php if ($fieldType === 'date'): ?>placeholder="dd-mm-jjjj"<?php endif ?>
                >
                <?php if ($field['required']): ?><p class="<?php echo $inputClass ?>__error-message" id="<?php echo $uniqueFieldKey ?>__error-message" aria-live="assertive"></p><?php endif ?>
            <?php endif ?>
        </div>
    <?php endforeach; ?>

    <?php $fieldName = "lsb[$listId][$fieldNameHoneypot]"; ?>
    <input autocomplete="new-password" type="email" id="<?php echo $fieldName ?>" name="<?php echo $fieldName ?>" placeholder="Your work e-mail here" style="position:absolute;top:-9999px;left:-9999px;">

    <?php $fieldName = "lsb[$listId][$fieldNameNonce]"; ?>
    <input type="hidden" name="<?php echo $fieldName ?>" value="<?php echo $nonce ?>">

    <div class="form-feedback">
        <p class="form-feedback-msg" aria-live="assertive"></p>
    </div>

    <button class="<?php echo $submitButtonClass ?>" type="submit" name="lsb_form_submit" value="<?php echo $listId ?>"><?php echo $submitButtonText ?></button>

    <?php if ($hasDateFields): ?>
        <script>
          document.addEventListener("DOMContentLoaded", function() {
            flatpickr('.js-lsb-datepicker', {locale: 'nl', altInput: true, altFormat: 'd-m-Y'});
          });
        </script>
    <?php endif ?>

</form>