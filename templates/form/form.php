<?php
/**
 * @var string $listId (valid id)
 * @var array $listFields
 * @var string $formID
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
 * - Add `formID` to Form and post `action` to anchor: `#$formID`
 * - Hide honeypot field to AT with `aria-hidden="true"`
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
<form id="<?php echo $formID ?>" action="#<?php echo $formID ?>" class="<?php echo $formClass ?> lsb-list-id-<?php echo $listId ?>" method="post">

    <?php if ($globalError): ?>
        <div class="lsb-form-global-error <?php echo $globalErrorClass ?>">
            <p class="form-feedback-msg"><?php echo $globalError ?></p>
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
        <div class="<?php echo $fieldWrapperClass ?> lsb-field-tag-<?php echo esc_attr($field['key']) ?> lsb-field-type-<?php echo $fieldType ?> <?php if ($field['is_error']): ?> is-invalid<?php endif ?>">

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
                    <?php if ($field['is_error']): ?> aria-invalid="true"<?php endif ?>
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
                    <?php if ($field['is_error']): ?> aria-invalid="true" <?php endif ?>
                    <?php if ($fieldType === 'number'): ?>step="any"<?php endif ?>
                    <?php if ($fieldType === 'date'): ?>placeholder="dd-mm-jjjj"<?php endif ?>
                >
                <?php if ($field['required']): ?><p class="<?php echo $inputClass ?>__error-message" id="<?php echo $uniqueFieldKey ?>__error-message" aria-live="assertive"></p><?php endif ?>
            <?php endif ?>
        </div>
    <?php endforeach; ?>

    <?php $fieldName = "lsb[$listId][$fieldNameHoneypot]"; ?>
    <input aria-hidden="true" tabindex="-1" autocomplete="new-password" type="email" id="<?php echo $fieldName ?>" name="<?php echo $fieldName ?>" placeholder="Your work e-mail here" style="position:absolute;top:-9999px;left:-9999px;">

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

    <?php
    /**
     * JavaScript (client-side) validation
     * See e.g: https://www.smashingmagazine.com/2023/02/guide-accessible-form-validation/
     * 
     * ASSUMPTION:
     * We use this plugin _only_ for very short Newslettter sign-up forms.
     * As such we can suffice with very simple validation, using inline error messages.
     * 
     * - Validate the field on blur
     * - Auto-focus the first field with an error
     * - Show the error message with the field
     * 
     */
    ?>
    <script>
        const emailRegex = /\S+@\S+\.\S+/; // has @ and .

        const elForm   = document.querySelector('.lsb-list-id-<?php echo $listId ?>');
        const elEmail  = elForm.querySelector('[name="lsb[<?php echo $listId ?>][email]"]');
        const arFields = Array.from(elForm.querySelectorAll('.lsb-form-input[name]'));

        // We Require both the form and the email field to be present
        if ( elForm && elEmail ) {

            // Override native validation on form
            elForm.setAttribute('novalidate', true);

            // Laposta checks submit button name/value
            // If we submit through JS we need to add this as a hidden input
            const elSubmitButton = elForm.querySelector('[type="submit"]');
            if (elSubmitButton) {
                const elSubmitButtonHiddenInput = document.createElement('input');
                elSubmitButtonHiddenInput.setAttribute('type', 'hidden');
                elSubmitButtonHiddenInput.setAttribute('name', elSubmitButton.getAttribute('name'));
                elSubmitButtonHiddenInput.setAttribute('value', elSubmitButton.getAttribute('value'));
                elForm.appendChild(elSubmitButtonHiddenInput);
            }

            const formErrors = {
                email: false,
                empty: false
            };

            let hasSubmitted = false;

            // Initially run validation on all fields
            arFields.forEach((elField) => {
                const isEmailField = (elField.type === 'email' || elField.name.indexOf('[email]') > -1);

                setupValidation({
                    elField: elField,
                    validateFn: isEmailField ? validateEmailFields : validateRequiredFields
                });
            });

            // Initially run validation on all fields
            function setupValidation({ elField, validateFn }) {
                let touched = false;

                elField.addEventListener('change', (e) => {
                    touched = true; // mark it as touched so that on blur it shows the error.
                });

                elField.addEventListener('keyup', (e) => {
                    // remove any error on keyup if existent
                    validateFn(e.target, { removeOnly: true });

                    if (hasSubmitted) {
                        updateSubmitSummary();
                    }
                });

                elField.addEventListener('blur', (e) => {
                    if (!touched) return;
                    // show error if touched
                    validateFn(e.target, { live: true });
                });
            }

            // Validate: Required fields
            function validateRequiredFields(el, opts) {
                if (el.hasAttribute('required') || el.hasAttribute('aria-required')) {
                    const _label  = document.querySelector('[for="' + el.id + '"]');
                    const _name   = _label ? _label.innerText.replace(/\s?\*/, '') : 'Dit veld';
                    const isEmpty = el.value.replace(/\s/, '') === '';
                    formErrors.empty = isEmpty;
                    updateFieldDOM(el, !isEmpty, _name + " <?php echo _x('is niet ingevuld. Vul dit veld in.', 'ictuwp-plugin-laposta-signup-basic form', 'gctheme') ?>", opts);
                } else {
                    // Not required, ignore
                    updateFieldDOM(el, true, '', opts);
                }
            }

            // Validate: Email fields (always required)
            function validateEmailFields(el, opts) {
                const isEmpty = el.value.replace(/\s/, '') === '';
                if (isEmpty) {
                    formErrors.email = true;
                    updateFieldDOM(el, !isEmpty, "E-mailadres <?php echo _x('is niet ingevuld. Vul dit veld in.', 'ictuwp-plugin-laposta-signup-basic form', 'gctheme') ?>", opts);
                } else {
                    const isEmailValid = el.value.match(emailRegex);
                    updateFieldDOM(el, isEmailValid, "<?php echo _x('Je e-mailadres is ongeldig. Een geldig e-mailadres is b.v. \'je.naam@bedrijf.nl\'.', 'ictuwp-plugin-laposta-signup-basic validatie: ongeldig email', 'gctheme') ?>", opts);
                    formErrors.email = !isEmailValid;
                }
            }

            // Update DOM with proper validation attributes and messages
            function updateFieldDOM(el, isValid, errorMessage, opts) {
                const removeOnly = opts?.removeOnly;
                const isLive = opts?.live;
                const elWrapp = el.closest(".<?php echo $fieldWrapperClass ?>");
                const elError = elWrapp.querySelector('.lsb-form-input__error-message');

                if (isValid) {
                    elWrapp.classList.remove('is-invalid');
                    elError.innerText = ''; // It's valid
                    el.removeAttribute('aria-invalid');
                } else if (!removeOnly) {
                    elWrapp.classList.add('is-invalid');
                    el.setAttribute('aria-invalid', 'true');
                    elError.setAttribute('aria-live', isLive ? 'assertive' : 'off');
                    elError.innerText = errorMessage;
                }
            }

            // This updates the form summary
            // Not really necessary since we submit through PHP
            // But this is where we'd use AJAX if we wanted to..
            function updateSubmitSummary({ isSubmit } = {}) {
                const elSummary = elForm.querySelector('.form-feedback');
                const elSummaryMsg = elSummary.querySelector('.form-feedback-msg');

                // Clear form feedback
                elSummaryMsg.classList.remove('is-invalid');
                elSummaryMsg.classList.remove('is-success');
                elSummaryMsg.innerText = '';

                const errorsState = Object.values(formErrors);
                const isFormValid = !errorsState.includes(true);

                if (!isFormValid) {
                    elSummaryMsg.innerText = '';
                } else if (isSubmit) {
                    elSummaryMsg.innerText = "<?php echo _x('Je aanmelding wordt verzonden...', 'ictuwp-plugin-laposta-signup-basic form', 'gctheme') ?>";
                    elSummaryMsg.classList.add('is-success');

                    // Finally submit the form
                    elForm.submit();
                }
            }

            elForm.addEventListener('submit', (e) => {
                e.preventDefault();
                hasSubmitted = true;

                // Validate again
                arFields.forEach((elField) => {
                    if (elField.type === 'email' || elField.name.indexOf('[email]') > -1) {
                        validateEmailFields(elField, { live: true });
                    } else {
                        validateRequiredFields(elField, { live: true });
                    }
                });

                if (formErrors.email) {
                    // Focus email input
                    elEmail.focus();
                } else if(formErrors.empty) {
                    // Focus 1st empty input
                    for (const field of arFields) {
                        if ((field.hasAttribute('required') || field.hasAttribute('aria-required')) && field.value.replace(/\s/, '') === '') {
                            field.focus();
                            break;
                        }
                    }
                } else {
                    updateSubmitSummary({ isSubmit: true });
                }
            });
        }
    </script>

</form>