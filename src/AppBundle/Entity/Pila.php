<?php

namespace AppBundle\Entity;

/**
 * Clase abstracta que estará encargada de interactuar con los diferentes proveedores de PILA
 * Para cada proveedor nuevo se deberá heredar esta clase y los métodos asociados.
 */
abstract class Pila {
    
    /**
     * Método para el envío de la estructura de la información y los datos requeridos en cada escenario
     * 
     * @param array $info Array que contendrá la información asociada a la PILA
     * 
     * @return int    Identificador de la transacción, false en caso de error.
     */
    public static abstract function proccess($info);
    
    /**
     * En caso que la PILA tenga la capacidad de trasferir el dinero de nómina al empleado, debemos tener un método para enviar la información correspondiente, esto depende de una negociación previa
     * 
     * @param array $info Array que contendrá la información asociada al pago
     * 
     * @return int    Identificador de la transacción, false en caso de error.
     */
    public static abstract function pay($info);
    
    /**
     * Uno de los puntos críticos a definir es si hay un método único para novedades o uno por cada una, se despliegan las transacciones registradas hasta el momento a continuación
     * ING - Ingreso
     * ING - Ingreso a riesgos laborales
     * VST - Variación transitoria de salario
     * AVP - Aporte voluntario a pensiones
     * IGE - Incapacidad general
     * IRP - Incapacidad por accidente de trabajo
     * LMA - Licencia de maternidad
     * RET - Retiro
     * TAE - Traslado a otra EPS
     * RET - Retiro de riesgos laborales
     * TDE - Traslado desde otra EPS
     * TAP - Traslado a otro fondo de pensiones
     * VAC - Vacaciones
     * TDP - Traslado desde otro fondo de pensiones
     * VSP - Variación permanente de salario
     * VCT - Variación centros de trabajo
     * RET - Retiro de pensiones
     * SLN X - Suspensión temporal del contrato de trabajo o Licencia no remunerada
     * SLN C - Comisión de servicios
     * Beneficiarios
     * 
     * @param array $info Array que contendrá la información asociada a la novedad
     * 
     * @return int    Identificador de la transacción, false en caso de error.
     */
    public static abstract function notify($info);
    
}