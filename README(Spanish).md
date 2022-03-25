# Addi/Payment

Pasarela de Pagos de Addi para Magento 2.


## Instalación

```bash    
composer require addi/magento2-payment:1.0.*
php bin/magento module:enable addi_Payment --clear-static-content
php bin/magento setup:upgrade
php bin/magento cache:clean
```


## Actualización

```bash
composer clear-cache
composer update addi/magento2-payment
bin/magento setup:upgrade
php bin/magento cache:clean
```

## Configuración del módulo – Panel de administración

Para configurar el módulo desde el panel de administración del comercio diríjase al apartado **Stores > Configuration > Sales > Payment Methods > Addi**

![magento-admin](https://user-images.githubusercontent.com/90288747/137793232-5cee48f1-5706-4022-aee9-25cfc439a2a0.png)


## Opciones del módulo (ADDI)

![addi-section](https://user-images.githubusercontent.com/90288747/137793224-cd73cd0c-946d-4d23-bac9-468b6e095c02.png)

- Activado (Desplegable): **Si** o **No.**
- Título: Campo de texto no modificable.
- Estado de nuevo pedido: Estado **“Pendiente”** por default y no modificable.
- Países que aplican: Campo no modificable.
- Países que aplica: **Colombia** o **Brasil**.
- Ambiente de Desarrollo: **Si** o **No**.
- Identificador del cliente.
- Identificador secreto del cliente.
- Orden para mostrarse: Orden de visualización del método de pago en el checkout.

![addi-configurations](https://user-images.githubusercontent.com/90288747/137793219-2c39f534-94d2-46f0-91a2-ef267b2b00d2.png)

## Addi en la página de pago

Una vez instalado el módulo en Magento procedemos a realizar distintos flujos de compra desde la tienda. Podrá crear una cuenta o realizar el flujo de compra como invitado.
Para el desarrollo de este módulo se utilizó el contenido nativo proporcionado por Magento considerando productos de tipo simple, configurable o virtual.

## Revisión y Pagos

Visualizará el método de pago en la vista del Checkout -
Revisión de Pagos siempre y cuando la cantidad mínimo y 
máxima disponible en el carrito de compras sea acorde a 
los cantidades establecidas para cada país.

**Colombia:** Cantidad mínima 100000, Cantidad máxima 
2000000

**Brasil:** Cantidad mínima 150, Cantidad máxima 3000

![checkout-addi-payment](https://user-images.githubusercontent.com/90288747/137793231-bc3591d0-e75e-402e-a9f1-8c60368fe0d7.png)

**Descuento:** Visualizará un label de descuento siempre y cuando Addi haya autorizado el descuento previamente al comercio que esta llevando a cabo la integración del método de pago.

![addi-checkout-discount](https://user-images.githubusercontent.com/90288747/137793218-4fd32205-a361-43ae-9261-0f2e9c81f658.png)

## Orden - Panel de Administración

La orden se creará con el estatus “Pago pendiente”, una vez que Addi apruebe el crédito, el estatus cambiará a “Procesando” o en caso de no ser aprobado se visualizará bajo el estatus “Cancelado”.

Si el crédito fue aprobado, a través del detalle de la orden 
visualizará los siguientes datos: **ID de pedido de Addi, 
ID de aplicación de Addi, Cantidad aprobada de 
Addi, Moneda de Addi, Estado de Addi.** Si el crédito 
no fue aprobado o se generó un error los campos se 
visualizarán vacios.

![addi-orders-grid](https://user-images.githubusercontent.com/90288747/137793222-fc744e31-9c8a-4a48-8812-13ce3442f97e.png)

![addi-order-detail](https://user-images.githubusercontent.com/90288747/137793220-69ea67e5-9d5f-49a9-b637-0e1a145ede7b.png)

![addi-order-detail2](https://user-images.githubusercontent.com/90288747/137794787-1164e32a-4dc6-41a7-9d93-94a24290b835.png)

## Addi Widget y Modal – Dellate de producto

Visualizará el Widget y Modal a través de la vista del detalle de producto.

![addi-widget-product-detail](https://user-images.githubusercontent.com/90288747/137793228-3af97602-d42c-4cd5-a93e-fce85712e08d.png)


![addi-widget-product-detail2](https://user-images.githubusercontent.com/90288747/137793229-8681691c-20eb-4c8f-8d4c-f94a110da4e7.png)


En el panel de administración, haga clic en la pestaña **Widget Styles 
Configuration.**

![addi-widget-configurations](https://user-images.githubusercontent.com/90288747/137793226-26f66d9a-ac69-4ba0-b7fd-775fad6ccc34.png)

**Widget Active:** Si o No

Si lo desea puede editar o no el look & feel del widget para que se 
ajuste mejor al de tu comercio, considere los siguientes campos:

**Border Color:** black

**Border Radius:** 5px

**Font Color:** black

**Font Family:** system-ui

**Font Size:** 14px

**Addi icon Background color:** black

**Widget Background color:** transparent

**Margin:** 8px 0px

**Logo Addi Blank:** Si o No

En el panel de administración, haga clic en la pestaña **Modal 
Styles Configuration.**

Si lo desea puede editar o no el look & feel del modal para que se 
ajuste mejor al de tu comercio, considere los siguientes campos de 
la pestaña **Modal Styles Configuration:**

![addi-widget-configurations2](https://user-images.githubusercontent.com/90288747/137793227-47d123dd-85b1-4121-9bd1-f6e9d22f0d16.png)


**Background Color:** #eee

**Font Color:** black

**Font Family:** system-ui

**Price Color:** #3c65ec

**Banner Background Color:** #4cbd99

**Banner Font Color:** white

**Modal Background Color:** white

**Modal Button Border Color:** #4cbd99

**Button Border Radius:** 5px

**Button Background Color:** transparent

**Button Font Color:** #4cbd99 


## **Preguntas y comentarios** ##

Si tiene alguna pregunta sobre este producto o la implementación
póngase en contacto con soporte_aliado@addi.com para obtener 
ayuda.
