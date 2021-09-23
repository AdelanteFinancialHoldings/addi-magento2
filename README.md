# Addi/Payment

Pasarela de Pagos de Addi para Magento 2.


## Instalación

```bash    
composer require addi/magento2-payment: 1.0.*
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

## Capturas de Pantalla


### Configuración del módulo – Panel de administración

Para configurar el módulo desde el panel de administración del comercio diríjase al apartado Stores > Configuration > Sales > Payment Methods > Addi

#### Opciones del módulo (ADDI)

- Activado (Desplegable): Si o No.
- Título: Campo de texto no modificable.
- Estado de nuevo pedido: Estado “Pendiente” por default y no modificable.
- Países que aplican: Campo no modificable.
- Países que aplica: Colombia o Brasil.
- Ambiente de Desarrollo: Si o No.
- Identificador del cliente.
- Identificador secreto del cliente.
- Orden para mostrarse: Orden de visualización del método de pago en el checkout.

#### Proceso de pago – Checkout

Una vez instalado el módulo en Magento procedemos a realizar distintos flujos de compra desde la tienda. Podrá crear una cuenta o realizar el flujo de compra como invitado.
Para el desarrollo de este módulo se utilizó el contenido nativo proporcionado por Magento considerando productos de tipo simple, configurable o virtual.

#### Redirección a la aplicación de Addi

#### Respuesta - Proceso de compra exitoso

#### Respuesta - Error en método de pago

En automático redireccionará al usuario al Carrito de compras y mediante esta vista se le notificará que ocurrió un error de cualquier tipo a través de un “mensaje de alerta”.


#### Orden - Panel de Administración

La orden se creará con el estatus “Pago pendiente”, una vez que Addi apruebe el crédito, el estatus cambiará a “Procesando” o en caso de no ser aprobado se visualizará bajo el estatus “Cancelado”.

Adicionalmente, si el crédito fue aprobado, a través del detalle de la orden visualizará los siguientes datos: ID de pedido de Addi, ID de aplicación de Addi, Cantidad aprobada de Addi, Moneda de Addi, Estado de Addi. Si el crédito no fue aprobado o se generó algún error los campos se guardan darán vacíos. 
