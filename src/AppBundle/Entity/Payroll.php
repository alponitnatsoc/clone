<?php

namespace AppBundle\Entity;

/**
 * Clase abstracta que estará encargada de interactuar con los diferentes sistemas de nómina
 * Para cada sistema nuevo se deberá heredar esta clase y los métodos asociados.
 */
abstract class Payroll {
    
    /**
     * Método para registrar empleador
     * 
     * @param array $info Información del empleador
     * 
     * @return boolean Estado de la transacción
     */
    public static abstract function registerEmployer($info);
    /**
     * Método para registrar empleado
     * 
     * @param array $info Información del empleado
     * 
     * @return boolean Estado de la transacción
     */
    public static abstract function registerEmployee($info);
    
    /**
     * Método para actualizar info del empleador
     * 
     * @param array $info Información del empleador
     * 
     * @return boolean Estado de la transacción
     */
    public static abstract function updateEmployer($info);
    /**
     * Método para actualizar indo del empleado
     * 
     * @param array $info Información del empleado
     * 
     * @return boolean Estado de la transacción
     */
    public static abstract function updateEmployee($info);
    
    /**
     * Método para pagar la nómina
     * 
     * @param array $info Información del pago
     * 
     * @return boolean Estado de la transacción
     */
    public static abstract function pay($info);
    
    /**
     * Método para registrar novedades
     * 
     * @param array $info Información de novedades
     * 
     * @return boolean Estado de la transacción
     */
    public static abstract function newNovelty($info);
    
    /**
     * Método para actualizar salario
     * 
     * @param array $info Información del salario
     * 
     * @return boolean Estado de la transacción
     */
    public static abstract function updateWage($info);
    
    /**
     * Método para solicitar certificador laboral
     * 
     * @param array $info Información del empleado
     * 
     * @return String   URL con ruta al certificado
     */
    public static abstract function requestWorkCertificate($info);
    
    /**
     * Método para solicitar vacaciones del empleado
     * 
     * @param array $info Información del empleado
     * 
     * @return boolean Estado de la transacción
     */
    public static abstract function requestVacations($info);
    
    /**
     * Método para registrar horas extras al empleado
     * 
     * @param array $info Información del empleado
     * 
     * @return boolean Estado de la transacción
     */
    public static abstract function addExtraHours($info);
    
    /**
     * Método para eliminar empleado
     * 
     * @param array $info Información del empleado
     * 
     * @return boolean Estado de la transacción
     */
    public static abstract function deleteEmployee($info);
}