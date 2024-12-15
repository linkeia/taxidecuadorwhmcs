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

# Traducciones de Mensajes de Error para WHMCS
Se proporcionan las traducciones de los mensajes de error recomendados para incluir en los archivos overrides del idioma de WHMCS. Las traducciones disponibles son:

- Español
- Inglés
- Portugués

Pueden ser usadas en cualquier idioma con el remplazo de los valores en el correcto archivo de idioma deseado.

# Instrucciones de Uso
1) Copia y pega los 3 Hooks definidos para cada tema compatible en el directorio: "/includes/hooks/" de tu instalación de WHMCS 8.11.2.

2) Configuración de Traducciones:
Copia las traducciones de los idiomas en el archivo correspondiente dentro del directorio override de WHMCS:

- /lang/overrides/spanish.php
- /lang/overrides/english.php
- /lang/overrides/portuguese-br.php
- /lang/overrides/portuguese-pt.php

Si la carpeta "overrides" no existe, deberás crearla manualmente y luego copiar dentro los archivos de idioma deseados.
