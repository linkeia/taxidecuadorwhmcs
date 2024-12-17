<?php

// Hook creado por Linkeia Network (Linkeia Inc.) | Hook created by Linkeia Network (Linkeia Inc.)
// Autor | Author: Carlos Viteri
// Compatibilidad | Compatibility: WHMCS 8.11.2 y Tema Lagom 2.3.0
// Changelog: Lanzamiento 01 de enero de 2025 V.1.0 | Release: January 1, 2025 V.1.0
// Requiere | Requires: PHP 8.2 o superior (compatible con PHP 8.1) | PHP 8.2 or higher (compatible with PHP 8.1)
// Descripción | Description:
// - Valida el campo TAX ID (Cédula o RUC) solo para usuarios no autenticados.
//   Validates the TAX ID field (Cédula or RUC) only for unauthenticated users.
// - Aplica la validación únicamente cuando el país seleccionado sea Ecuador (EC).
//   Applies validation only when the selected country is Ecuador (EC).
// - La validación cubre | Validation covers:
//      * Cédulas (10 dígitos con algoritmo módulo 10) | IDs (10 digits with modulus 10 algorithm).
//      * RUC de personas naturales | Natural person RUC (13 digits).
//      * RUC de sociedades privadas | Private company RUC (third digit = 9, modulus 11).
//      * RUC de sociedades públicas | Public company RUC (third digit = 6, modulus 11).

use WHMCS\User\Client;

class TaxIdValidator
{
    public static function validate($country, $taxId)
    {
        // Validar solo si el país es Ecuador
        if (strtoupper($country) !== 'EC') {
            return null;
        }

        // Verificar si el TAX ID está vacío
        if (empty($taxId)) {
            return ['tax_id' => Lang::trans('taxiderror.empty')];
        }

        // Validar TAX ID: Cédula o RUC
        if (!self::isValidCedula($taxId) && !self::isValidRUC($taxId)) {
            if (strlen($taxId) == 10) {
                return ['tax_id' => Lang::trans('taxiderror.invalidcedula')];
            } elseif (strlen($taxId) == 13) {
                return ['tax_id' => Lang::trans('taxiderror.invalidruc')];
            } else {
                return ['tax_id' => Lang::trans('taxiderror.invalidformat')];
            }
        }

        return null;
    }

    private static function isValidCedula($cedula)
    {
        if (!preg_match('/^\d{10}$/', $cedula)) return false;

        $provincia = substr($cedula, 0, 2);
        $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $suma = 0;

        if ($provincia < 1 || $provincia > 24) return false;

        for ($i = 0; $i < 9; $i++) {
            $digito = $cedula[$i] * $coeficientes[$i];
            $suma += ($digito > 9) ? ($digito - 9) : $digito;
        }

        $verificador = (10 - ($suma % 10)) % 10;
        return $verificador == $cedula[9];
    }

    private static function isValidRUC($ruc)
    {
        if (!preg_match('/^\d{13}$/', $ruc)) return false;

        $tercerDigito = (int) $ruc[2];

        if (in_array($tercerDigito, [0, 1, 2, 3, 4, 5])) {
            return self::isValidCedula(substr($ruc, 0, 10)) && self::isValidEstablecimiento(substr($ruc, 10, 3));
        } elseif ($tercerDigito == 9) {
            return self::isValidRUCPrivado($ruc);
        } elseif ($tercerDigito == 6) {
            return self::isValidRUCPublico($ruc);
        }

        return false;
    }

    private static function isValidEstablecimiento($establecimiento)
    {
        return (int)$establecimiento > 0;
    }

    private static function isValidRUCPrivado($ruc)
    {
        $coeficientes = [4, 3, 2, 7, 6, 5, 4, 3, 2];
        $suma = 0;

        for ($i = 0; $i < 9; $i++) {
            $suma += $ruc[$i] * $coeficientes[$i];
        }

        $verificador = 11 - ($suma % 11);
        return ($verificador == 11 ? 0 : $verificador) == $ruc[9];
    }

    private static function isValidRUCPublico($ruc)
    {
        $coeficientes = [3, 2, 7, 6, 5, 4, 3, 2];
        $suma = 0;

        for ($i = 0; $i < 8; $i++) {
            $suma += $ruc[$i] * $coeficientes[$i];
        }

        $verificador = 11 - ($suma % 11);
        return ($verificador == 11 ? 0 : $verificador) == $ruc[8];
    }
}

// Hook para Registro
add_hook('ClientDetailsValidation', 1, function ($vars) {
    return TaxIdValidator::validate($vars['country'] ?? '', $vars['tax_id'] ?? '');
});

// Hook para Checkout
add_hook('ShoppingCartValidateCheckout', 1, function ($vars) {
    $country = App::getFromRequest('country');
    $taxId = App::getFromRequest('tax_id');
    return TaxIdValidator::validate($country, $taxId);
});
