# HOOK de Validación de Cédula y RUC de Ecuador para WHMCS 8.11.2

Este HOOK valida el número de cédula o RUC de Ecuador en el campo TAXID, únicamente cuando el país de dirección del usuario es Ecuador. Es compatible con los temas Lagom 2.3.0, TwentyOne y Six.

# Validaciones disponibles:
- Cédula de Identidad Ecuatoriana
- RUC de Persona Natural
- RUC de Empresa Privada
- RUC de Empresa Pública

# Funcionamiento:
Por defecto, el HOOK se ejecuta únicamente cuando el usuario no ha iniciado sesión. Esto evita interrupciones en el proceso de compra de usuarios registrados y logueados, que podrían enfrentar restricciones si no tienen el TAXID configurado en su perfil.

# Personalización:
Si deseas que el HOOK solicite y valide siempre el campo TAXID (independientemente del estado de sesión), puedes eliminar 3 líneas de código específicas del HOOK. De este modo, la validación será obligatoria cuando la dirección de facturación corresponda a Ecuador.
