/**
 * LaboData for Prestashop
 *
 * @author 161 SARL <contact@161.io>
 * @copyright (c) 161 SARL, https://161.io
 * @license https://161.io
 */

(function($) {
    'use strict';

    var Translate;

    /**
     * @param {Boolean} show
     */
    function ajaxRunning(show) {
        $('#ajax_running').css('display', (show ? '' : 'none'));
    }

    /**
     * Message d'information
     * @param {String} title
     * @param {String} message
     */
    function growlNotice(title, message) {
        $.growl.notice({
            title  : title,
            size   : 'large',
            message: message
        });
    }

    /**
     * Message d'erreur
     * @param {String} title
     * @param {String} message
     */
    function growlError(title, message) {
        $.growl.error({
            title  : title,
            size   : 'large',
            message: message
        });
    }

    /**
     * [disabled]
     * @param {jQuery} $elt
     * @param {Boolean} flag
     */
    function disabled($elt, flag) {
        var param = 'disabled';
        if (flag) {
            $elt.prop(param, true).attr(param, param);
        } else {
            $elt.prop(param, false).removeAttr(param);
        }
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

    /**
     * Importer une category
     * @param {jQuery} $btn
     * @param {Function} onComplete
     * @returns {Boolean}
     */
    function importCategory($btn, onComplete) {
        var xhr = $btn.data('xhr');
        if (xhr) { return false; }

        ajaxRunning(true);
        xhr = $.ajax({
            url     : $btn.data('action'),
            type    : 'POST',
            dataType: 'json'
        });
        xhr.done(function(data) {
            switch(data.growlType) {
                case 'notice' :
                    disabled($btn, true);

                    if (data.psIdObject) {
                        // Mode "tree"
                        var $idPrest = $('[name="id_prestashop_' + data.id + '"]'); // input
                        $idPrest.val(data.psIdObject);
                        var $titlePrest = $idPrest.parent().next(); // td
                        $titlePrest.text(data.psNameObject);
                        if (data.ldCategory.parent_id) {
                            var $idPrestParent = $('[name="id_prestashop_' + data.ldCategory.parent_id + '"]'); // input
                            var $trParent = $idPrestParent.parent().parent();
                            var $btnParent = $trParent.find('.btn');
                            disabled($btnParent, true);
                            $idPrestParent.val(data.psIdParentObject);
                            var $titlePrestParent = $idPrestParent.parent().next();
                            $titlePrestParent.text(data.psNameParentObject);
                        }
                    }

                    growlNotice('', data.growlMessage);
                    if (onComplete) {
                        onComplete();
                    }
                    break;
                default :
                    importGroup = false;
                    growlError('', data.growlMessage || 'Message not found');
                    break;
            }
        });
        xhr.fail(function() {
            importGroup = false;
            growlError(Translate.errorTitle, Translate.errorMessage);
        });
        xhr.always(function() {
            xhr = null;
            $btn.data('xhr', xhr);
            ajaxRunning(false);
        });
        $btn.data('xhr', xhr);

        return false;
    }

    /**
     * Importer en loop
     */
    function importLoop() {
        var $btn = $('.btn[data-action]').not('[disabled]').first();
        if ($btn.length) {
            importGroup = true;
            ajaxRunning(true);
            importCategory($btn, importLoop);
        } else {
            // Fin
            disabled($('#btn-add-all'), true);
            importGroup = false;
        }
    }

    /**
     * Lier une categories LaboData a une categories existante
     * @param {jQuery} $input
     */
    function bindCategory($input) {
        var ID_PRESTASHOP_REMOVE = -1;
        var $form = $input.parents('form'),
            $tr = $input.parent().parent(),
            $tdIdLaboData = $tr.children().first(), // .eq(0)
            $tdTitlePrest = $tr.children().eq(3),
            $btn = $tr.find('.btn');

        var idLabodata = parseInt($tdIdLaboData.text());
        if (isNaN(idLabodata) || idLabodata < 0) {
            // Erreur DOM
            $input.val($input.data('prevVal'));
            return;
        }

        var idPrestashop = $input.val() === '' ? ID_PRESTASHOP_REMOVE : parseInt($input.val());
        if (idPrestashop === ID_PRESTASHOP_REMOVE) {
            $input.val('');
        } else if (isNaN(idPrestashop) || idPrestashop < 1) {
            $input.val($input.data('prevVal'));
            return;
        } else {
            $input.val(idPrestashop);
        }

        var dataAction = $btn.data('action').replace(/^(.+action=add)(\w+)$/i, 'bind$2');

        disabled($input.add($btn), true);
        ajaxRunning(true);
        var xhr = $.ajax({
            url     : $form.data('action'),
            type    : 'POST',
            data    : {
                action      : dataAction,  // String: "bindCategory", "bindManufacturer", "bindFeatureValue"
                id          : idLabodata,  // Number
                idPrestashop: idPrestashop // Number
            },
            dataType: 'json'
        });
        xhr.done(function(data) {
            var success = false;
            if (data.psIdObject == ID_PRESTASHOP_REMOVE) {
                success = true;
                $tdTitlePrest.text('');
                disabled($btn, false);
            } else if (data.psIdObject && data.psIdObject > 0) {
                success = true;
                $tdTitlePrest.text(data.psNameObject);
            }

            if (success) {
                $input.data('prevVal', $input.val());
                if (data.growlMessage) {
                    growlNotice('', data.growlMessage);
                }
            } else {
                $input.val($input.data('prevVal'));
                if ($input.val() === '') {
                    disabled($btn, false);
                }
                if (data.growlMessage) {
                    growlError('', data.growlMessage);
                }
            }
        });
        xhr.fail(function() {
            importGroup = false;
            growlError(Translate.errorTitle, Translate.errorMessage);
        });
        xhr.always(function() {
            xhr = null;
            disabled($input, false);
            ajaxRunning(false);
        });
    }

    $(function() {
        Translate = window.LaboDataTranslate;

        $('.table.configuration').find('td.pointer')
            .removeAttr('onclick')
            .removeClass('pointer');

        // Ajouter tout
        $('#btn-add-all').on('click', function() {
            if (isImportGroup()) {
                return false;
            }
            if (!confirm($(this).data('confirm'))) {
                return;
            }
            importLoop();
        });

        // Ajouter une categorie
        $('.btn[data-action]').on('click', function() {
            if (isImportGroup()) {
                return false;
            }
            importCategory($(this));
            return false;
        });

        // Modifier une categorie ( arborescence )
        $('input.id_prestashop')
            .each(function() {
                var $input = $(this);
                $input.data('prevVal', $input.val());
            })
            .on('change.labodata', function() {
                bindCategory($(this));
            });

    });

})(jQuery);
