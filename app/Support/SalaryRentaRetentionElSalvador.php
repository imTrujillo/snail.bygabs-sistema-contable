<?php

namespace App\Support;

/**
 * Retención de renta sobre salarios (tablas de abril 2025, Decreto vigente MH).
 * Base: remuneración gravada del período = bruto − ISSS laboral − AFP laboral.
 *
 * Referencia orientativa para planillas rutinarias; junio/diciembre requieren recálculos oficiales.
 */
final class SalaryRentaRetentionElSalvador
{
    /** @param  numeric-string|float  $gravada */
    private static function fromGravadaMonthly(float $gravada): float
    {
        $b = round(max(0, $gravada), 2);
        if ($b <= 550.0) {
            return 0.0;
        }
        if ($b <= 895.24) {
            return round(17.67 + ($b - 550.0) * 0.10, 2);
        }
        if ($b <= 2038.10) {
            return round(60.0 + ($b - 895.24) * 0.20, 2);
        }

        return round(288.57 + ($b - 2038.10) * 0.30, 2);
    }

    /** @param  numeric-string|float  $gravada */
    private static function fromGravadaQuincenal(float $gravada): float
    {
        $b = round(max(0, $gravada), 2);
        if ($b <= 275.0) {
            return 0.0;
        }
        if ($b <= 447.62) {
            return round(8.83 + ($b - 275.0) * 0.10, 2);
        }
        if ($b <= 1019.05) {
            return round(30.0 + ($b - 447.62) * 0.20, 2);
        }

        return round(144.28 + ($b - 1019.05) * 0.30, 2);
    }

    /** @param  numeric-string|float  $gravada */
    private static function fromGravadaSemanal(float $gravada): float
    {
        $b = round(max(0, $gravada), 2);
        if ($b <= 137.5) {
            return 0.0;
        }
        if ($b <= 223.81) {
            return round(4.42 + ($b - 137.5) * 0.10, 2);
        }
        if ($b <= 509.52) {
            return round(15.0 + ($b - 223.81) * 0.20, 2);
        }

        return round(72.14 + ($b - 509.52) * 0.30, 2);
    }

    /**
     * Retención del período de pago (mismo bruto/des cuentas del recibo que se está liquidando).
     */
    public static function retentionForPeriod(
        float $grossSalary,
        float $isssDeduction,
        float $afpDeduction,
        string $periodType,
    ): float {
        $gravada = round(max(0.0, $grossSalary - $isssDeduction - $afpDeduction), 2);

        return match ($periodType) {
            'Mensual' => self::fromGravadaMonthly($gravada),
            'Quincenal' => self::fromGravadaQuincenal($gravada),
            'Semanal' => self::fromGravadaSemanal($gravada),
            default => self::fromGravadaMonthly($gravada),
        };
    }
}
