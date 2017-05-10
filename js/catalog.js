/*! @license Copyright (c) 161 SARL, https://161.io */

+function($) {
    'use strict';

    var COOKIE_MODAL_IGNORE = 'LaboDataModalIgnore';
    var IMPORT_TYPE_FULL    = 'full',
        IMPORT_TYPE_IMAGE   = 'image',
        IMPORT_TYPE_CONTENT = 'content';
    var IMPORT_ACTION_EDIT  = 'edit',
        IMPORT_ACTION_ADD   = 'add',
        IMPORT_ACTION_BUY   = 'buy';

    var $laboDataCredit,
        $laboDataResult,
        $modalLaboDataImport,
        $modalLaboDataCredit;

    // Dernier action d'imporation // @see IMPORT_ACTION_XXX
    var lastAction = IMPORT_ACTION_EDIT,
        currency = '&euro;';


    /**
     * @param {Boolean} show
     */
    function ajaxRunning(show) {
        $('#ajax_running').css('display', (show ? '' : 'none'));
    }

    /**
     * @param {String|Number} str
     * @returns {Number}
     */
    function toFloat(str) {
        str = ('' + str).replace(',', '.');
        var val = parseFloat(str);
        if (isNaN(val) || val < 0) { val = 0; }
        return val;
    }

    /**
     * Formater le nombre de credits
     * @param {Number} nbr
     * @returns {String}
     */
    function creditFormat(nbr) {
        return nbr.toFixed(2).replace('.', ',');
    }

    /**
     * Mise a jour du cochon
     * @param {Number} credit
     */
    function updatePiggy(credit) {
        credit = toFloat(credit);
        $laboDataCredit.html(creditFormat(credit));
    }

    /**
     * Importer les donnees en AJAX
     * @param {Object} options
     * @param {Function=} onComplete
     */
    function importProduct(options, onComplete) {
        options = $.extend({ // Donnees POST
            action: lastAction,
            id    : 0,
            type  : ''
        }, options);

        options.id = parseInt(options.id);
        var $tr = $laboDataResult.find('[data-product="' + options.id + '"]');
        if (!$tr.length) { // Produit introuvable
            alert('Product not found : ' + options.id);
            return;
        }

        var $state = $tr.find('.label-state');

        // Requete AJAX
        var xhr = $.ajax({
            url     : $laboDataResult.data('url-import'),
            type    : 'POST',
            cache   : false,
            data    : options,
            dataType: 'json'
        });
        xhr.done(function(data) {
            if (data.success) {
                // L'importation a fonctionne
                var type = data.type;
                //var action = data.action;

                // Ajustement des boutons
                var $btnClick = $tr.find('.btn[data-type="' + type + '"]');
                $btnClick
                    .removeClass('btn-default')
                    .addClass('btn-success')
                    .data('credit', 0);
                if (type == IMPORT_TYPE_FULL || $tr.find('.btn[data-type].btn-success').length > 1) {
                    $tr.find('.btn[data-type]')
                        .removeClass('btn-default')
                        .addClass('btn-success')
                        .data('credit', 0);
                } else {
                    // Ajustement du prix sur un achat partiel
                    $tr.find('.btn[data-type="full"]').data('credit', $btnClick.data('init-credit'));
                }
                changeState($state, 'success', $state.data('msg-done'));
                updatePiggy(data.apiResponse.credit);

                if (onComplete) { onComplete(); }
                return;
            }

            // L'importation a echoue
            changeState($state, 'danger');
            if (data.apiResponse ) {
                updatePiggy(data.apiResponse.credit);
                if (data.apiResponse.error) {
                    $.growl.error({
                        title: '',
                        size: 'large',
                        message: (data.apiResponse.error.message || 'Message not found')
                    });
                }
            }
            //setImportGroup(false, true);
        });
        xhr.fail(function(data) {
            changeState($state, 'danger');
            //setImportGroup(false, true);
        });
        xhr.always(function() {
            if (!isImportGroup()) {
                $tr.find('button').prop('disabled', false).attr('disabled', '');
            }
        });

        changeState($state, 'default', $state.data('msg-progress'));
        $tr.find('button').prop('disabled', true).attr('disabled', 'disabled');
    }

    /**
     * @param {jQuery} $state
     * @param {String} labelName
     * @param {String=} message
     * @returns {jQuery}
     */
    function changeState($state, labelName, message) {
        if ('danger' == labelName && !message) {
            message = 'Error';
        }

        return $state
            .removeClass('label-default label-danger label-success')
            .addClass('label-' + labelName)
            .html(message);
    }

    /**
     * Importation par lot
     * @type {Boolean}
     */
    var importGroup = false;
    /**
     * Importation par lot en cours
     * @returns {Boolean}
     */
    function isImportGroup() {
        return importGroup;
    }



    // Demarrage
    $(function() {

        $laboDataCredit = $('#labodata-credit');
        $laboDataResult = $('#labodata-result');
        $modalLaboDataImport = $('#modal-labodata-import');
        $modalLaboDataCredit = $('#modal-labodata-credit');
        currency = $('#labodata-currency').html();

        //$modalLaboDataImport.modal(); // preview
        //$modalLaboDataCredit.modal(); // preview

        // Tooltip
        $laboDataResult.find('[data-type][title]').tooltip({
            container: $laboDataResult
        });

        // Approvisionner mon compte
        $modalLaboDataCredit.find('[data-submit="modal"]').on('click', function() {
            window.location.href = $('#labodata-autopay').attr('href');
        });


        // Importer
        $laboDataResult.find('tbody .btn[data-credit]').on('click', function() {
            var $btn = $(this),
                $tr = $btn.parents('tr[data-product]');
            var dataType = $btn.data('type'),
                credit = toFloat($btn.data('credit')),
                alreadyBought = !credit;

            $btn.tooltip('hide');
            var modalCloneId = $modalLaboDataImport.attr('id') + '-clone';
            $('#' + modalCloneId).remove();

            // Credit insuffisant
            if (!alreadyBought && credit > toFloat($laboDataCredit.text())) {
                $modalLaboDataCredit.modal('show');
                return;
            }

            if (alreadyBought && lastAction == IMPORT_ACTION_BUY) {
                // Abiguite possible sur le traitement : on ne peut pas acheter un produit deja achete...
                Cookies.set(COOKIE_MODAL_IGNORE, false);
            }
            if (Cookies.get(COOKIE_MODAL_IGNORE) == 'true') {
                importProduct({
                    id  : $tr.data('product'),
                    type: dataType
                });
                return;
            }

            var productTitle = $tr.find('[data-prod-title]').html(),
                price = $btn.data('credit') + currency;

            var $modalClone = $modalLaboDataImport.clone();
            $modalClone.attr('id', modalCloneId);
            $modalClone.find('[data-bought="' + (alreadyBought ? '0' : '1') + '"]').addClass('hide');
            $modalClone.find('[data-type]').not('[data-type="' + dataType + '"]').addClass('hide');
            $modalClone.find('[data-val="title"]').html(productTitle);
            $modalClone.find('[data-val="price"]').html(price);

            // Events
            $modalClone.find('[data-submit="modal"]').on('click', function() {
                lastAction = $modalClone.find('[name="modal-action"]:checked').val();

                var ignore = $modalClone.find('[name="modal-ignore"]').prop('checked');
                Cookies.set(COOKIE_MODAL_IGNORE, ignore);
                importProduct({
                    id  : $tr.data('product'),
                    type: dataType
                });
                $modalClone.modal('hide');
            });

            $modalClone.insertAfter($laboDataResult).modal('show');
        });


    });

}(jQuery);
