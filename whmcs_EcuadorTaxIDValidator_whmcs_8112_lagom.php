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

add_hook('ShoppingCartValidateCheckout', 1, function ($vars) {
    // Omitir validación si el cliente ha iniciado sesión | Skip validation if the client is logged in
    if (isset($_SESSION['uid']) && $_SESSION['uid'] > 0) {
        return null; 
    }

    // Obtener el país seleccionado | Get the selected country
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
 * Validate Ecuadorian ID number (10 digits, modulus 10).
 */
function isValidCedula($cedula)
{
    if (!preg_match('/^\d{10}$/', $cedula)) return false;

    $provincia = substr($cedula, 0, 2);
    $tercerDigito = (int) $cedula[2];
    $coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
    $suma = 0;

    // Verificar provincia y tercer dígito | Check province and third digit
    if ($provincia < 1 || $provincia > 24 || $tercerDigito > 5) return false;

    // Cálculo de módulo 10 | Modulus 10 calculation
    for ($i = 0; $i < 9; $i++) {
        $digito = $cedula[$i] * $coeficientes[$i];
        $suma += ($digito > 9) ? ($digito - 9) : $digito;
    }

    $verificador = (10 - ($suma % 10)) % 10;
    return $verificador == $cedula[9];
}

/**
 * Validar RUC en Ecuador (Natural, Privada, Pública).
 * Validate Ecuadorian RUC (Natural, Private, Public).
 */
function isValidRUC($ruc)
{
    if (!preg_match('/^\d{13}$/', $ruc)) return false;

    $tercerDigito = (int) $ruc[2];

    // Determinar el tipo de RUC | Determine the RUC type
    return match ($tercerDigito) {
        0, 1, 2, 3, 4, 5 => isValidCedula(substr($ruc, 0, 10)) && isValidEstablecimiento(substr($ruc, 10, 3)), // Persona natural | Natural person
        9 => isValidRUCPrivado($ruc), // Sociedad privada | Private company
        6 => isValidRUCPublico($ruc), // Sociedad pública | Public company
        default => false
    };
}

/**
 * Validar RUC de Sociedades Privadas (módulo 11).
 * Validate RUC for Private Companies (modulus 11).
 */
function isValidRUCPrivado($ruc)
{
    $coeficientes = [4, 3, 2, 7, 6, 5, 4, 3, 2];
    $provincia = substr($ruc, 0, 2);
    $establecimiento = substr($ruc, 10, 3);

    if ($provincia < 1 || $provincia > 24 || $ruc[2] != '9') return false;

    $suma = array_reduce(array_keys($coeficientes), fn($carry, $i) => $carry + $ruc[$i] * $coeficientes[$i], 0);
    $verificador = 11 - ($suma % 11);
    return (($verificador == 11 ? 0 : $verificador) == $ruc[9]) && $establecimiento > 0;
}

/**
 * Validar RUC de Sociedades Públicas (módulo 11).
 * Validate RUC for Public Companies (modulus 11).
 */
function isValidRUCPublico($ruc)
{
    $coeficientes = [3, 2, 7, 6, 5, 4, 3, 2];
    $provincia = substr($ruc, 0, 2);
    $establecimiento = substr($ruc, 9, 4);

    if ($provincia < 1 || $provincia > 24 || $ruc[2] != '6') return false;

    $suma = array_reduce(array_keys($coeficientes), fn($carry, $i) => $carry + $ruc[$i] * $coeficientes[$i], 0);
    $verificador = 11 - ($suma % 11);
    return (($verificador == 11 ? 0 : $verificador) == $ruc[8]) && $establecimiento > 0;
}

/**
 * Validar el código de establecimiento (últimos dígitos).
 * Validate establishment code (last digits).
 */
function isValidEstablecimiento($establecimiento)
{
    return (int) $establecimiento > 0;
}
