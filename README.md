# GPT Product Descriptions

Generación automática descripciones de productos con la API de ChatGPT (OpenAI)

---

## 📋 Descripción

Este módulo permite generar automáticamente descripciones de productos utilizando la API de ChatGPT. Ideal para tiendas con catálogos amplios que necesitan descripciones atractivas, rápidas y automáticas.

---

## ⚙️ Instalación

1. Ve a **Backoffice > Módulos > Módulos y Servicios**.
2. Haz clic en el botón **"Subir un módulo"**.
3. Selecciona el archivo `autodescription.zip` y súbelo.
4. Una vez instalado, haz clic en **"Configurar"** para introducir tu clave de API de ChatGPT.`autodescription` en el directorio `/modules/` de tu instalación de PrestaShop.
2. Accede al Backoffice y ve a **Módulos > Módulos y Servicios**.
3. Busca "AutoDescription" e instálalo.

---

## ⚖️ Configuración

1. En el Backoffice, accede a **Módulos > AutoDescription > Configurar**.
2. Introduce tu **clave de API de ChatGPT**.
3. Guarda los cambios.

### 🔑 ¿Cómo obtener tu API Key de ChatGPT?

1. Accede a [https://platform.openai.com/account/api-keys](https://platform.openai.com/account/api-keys)
2. Inicia sesión con tu cuenta de OpenAI (o crea una si aún no tienes una).
3. Haz clic en el botón **"Create new secret key"**.
4. Copia la clave generada y pégala en el formulario de configuración del módulo en PrestaShop.

💡 Recomendación: Guarda tu clave en un lugar seguro. Una vez generada, no se vuelve a mostrar.

---

## 🧠 Funcionamiento

- El módulo se engancha al evento `actionProductUpdate`.
- Cada vez que se edita y guarda un producto:
  - El módulo recoge el nombre y características del producto.
  - Se conecta con ChatGPT mediante la API.
  - Recibe una descripción generada por inteligencia artificial.
  - Actualiza automáticamente la descripción del producto.

---

## 🔐 Seguridad

- La clave API se almacena en la configuración segura de PrestaShop.
- Solo accesible desde el panel de administración.
- No se expone en el frontend ni se registra en logs.

---

## ⚡ Requisitos

- PrestaShop 8.0 o superior.
- Cuenta activa en OpenAI.
- Acceso a la API de ChatGPT.
- Servidor con acceso a internet saliente.

---

## ❓ Preguntas frecuentes

**¿Puedo editar la descripción generada?**  
Sí, desde el editor de productos como siempre.

**¿La descripción anterior se sobrescribe?**  
Sí. Cada vez que se guarda un producto, se reemplaza por la generada por ChatGPT.

**¿Se puede personalizar el estilo de la descripción?**  
Por ahora no desde la configuración, pero puedes modificar el "prompt" directamente en el archivo `autodescription.php`.

---

## 🚀 Futuras mejoras sugeridas

- Selector de idioma para la descripción.
- Opcion para establecer tono: informal, profesional, creativo...
- Campo para definir longitud de la descripción.
- Botón para generar la descripción manualmente desde la ficha de producto.
- Vista previa antes de guardar.

---
