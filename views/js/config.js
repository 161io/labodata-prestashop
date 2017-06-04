/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

+function($) {
    'use strict';

    $(function() {
        var $form = $('#config-form'),
            $inputEmail = $('#config-input-email'),
            $inputKey = $('#config-input-key');
        $inputEmail.add($inputKey).on('change keyup', function() {
            if ($inputEmail.val() && $inputKey.val()) {
                $form.find('[type="submit"]').prop('disabled', false);
            } else {
                $form.find('[type="submit"]').prop('disabled', true);
            }
        });
    });

}(jQuery);
