<?php

// Hook creado por Linkeia Network (Linkeia Inc.) | Hook created by Linkeia Network (Linkeia Inc.)
// Autor | Author: Carlos Viteri
// Compatibilidad | Compatibility: WHMCS 8.11.2 y Tema TwentyOne
// Changelog: Lanzamiento 01 de enero de 2025 V.1.0 | Release: January 1, 2025 V.1.0
// Requiere | Requires: PHP 8.2 o superior (compatible con PHP 8.1)
// Descripción | Description:
// - Valida el campo TAX ID (Cédula o RUC) solo para usuarios no autenticados.
// - Compatible con el tema TwentyOne y sus formularios de registro, edición de cuenta y checkout.

use WHMCS\User\Client;

add_hook('ShoppingCartValidateCheckout', 1, function ($vars) {
    // Omitir validación si el cliente ha iniciado sesión | Skip validation if the client is logged in
    if (isset($_SESSION['uid']) && $_SESSION['uid'] > 0) {
        return null; 
    }

    // Obtener el país seleccionado en el formulario | Get the selected country
    $country = App::getFromRequest('country');
    
    // Validar solo si el país es Ecuador (EC) | Validate only if the country is Ecuador (EC)
    if (strtoupper($country) !== 'EC') {
        return null;
    }

    // Obtener el TAX ID enviado | Get the TAX ID from the form
    $taxId = App::getFromRequest('tax_id');

    // Verificar si el TAX ID está vacío | Check if the TAX ID is empty
    if (empty($taxId)) {
        return [Lang::trans('taxiderror.empty')];
    }

    // Validar si es un número de cédula o RUC válido | Validate if it's a valid ID or RUC number
    if (!isValidCedula($taxId) && !isValidRUC($taxId)) {
        if (strlen($taxId) == 10) {
            return [Lang::trans('taxiderror.invalidcedula')]; // Número de cédula inválido | Invalid ID number
        } elseif (strlen($taxId) == 13) {
            return [Lang::trans('taxiderror.invalidruc')]; // Número de RUC inválido | Invalid RUC number
        } else {
            return [Lang::trans('taxiderror.invalidformat')]; // Formato inválido | Invalid format
        }
    }

    return null; // Validación exitosa | Validation successful
});

/**
 * Validar número de cédula en Ecuador (10 dígitos, módulo 10).
 */
function isValidCedula($cedula)
{
    if (!preg_match('/^\d{10}$/', $cedula)) return false;

    $provincia = substr($cedula, 0, 2);
    $tercerDigito = (int) $cedula[2];
    $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
    $suma = 0;

    if ($provincia < 1 || $provincia > 24 || $tercerDigito > 5) return false;

    for ($i = 0; $i < 9; $i++) {
        $digito = $cedula[$i] * $coeficientes[$i];
        $suma += ($digito > 9) ? ($digito - 9) : $digito;
    }

    $verificador = (10 - ($suma % 10)) % 10;
    return $verificador == $cedula[9];
}

/**
 * Validar RUC en Ecuador (Natural, Privada, Pública).
 */
function isValidRUC($ruc)
{
    if (!preg_match('/^\d{13}$/', $ruc)) return false;

    $tercerDigito = (int) $ruc[2];

    return match ($tercerDigito) {
        0, 1, 2, 3, 4, 5 => isValidCedula(substr($ruc, 0, 10)) && isValidEstablecimiento(substr($ruc, 10, 3)),
        9 => isValidRUCPrivado($ruc),
        6 => isValidRUCPublico($ruc),
        default => false
    };
}

/**
 * Validar el código de establecimiento (últimos dígitos).
 */
function isValidEstablecimiento($establecimiento)
{
    return (int) $establecimiento > 0;
}
