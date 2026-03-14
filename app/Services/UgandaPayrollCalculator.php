<?php

namespace App\Services;

/**
 * Uganda Payroll Calculator
 * ─────────────────────────
 * PAYE rates: URA effective 2024/2025
 * NSSF      : NSSF Act 2014 (10% employee + 10% employer)
 * LST       : Local Service Tax — flat tiers per annum
 */
class UgandaPayrollCalculator
{
    // ── PAYE Monthly Thresholds (UGX) ──────────────────────────────
    // Based on annual bands divided by 12
    // Annual: 0–2,820,000 = 0% | 2,820,001–4,020,000 = 10%
    //         4,020,001–4,920,000 = 20% | above = 30% + 40% on >120M

    public static function calculatePAYE(float $grossMonthly): float
    {
        $annual = $grossMonthly * 12;
        $annualPAYE = 0;

        if ($annual <= 2_820_000) {
            $annualPAYE = 0;
        } elseif ($annual <= 4_020_000) {
            $annualPAYE = ($annual - 2_820_000) * 0.10;
        } elseif ($annual <= 4_920_000) {
            $annualPAYE = (1_200_000 * 0.10) + (($annual - 4_020_000) * 0.20);
        } elseif ($annual <= 120_000_000) {
            $annualPAYE = (1_200_000 * 0.10) + (900_000 * 0.20) + (($annual - 4_920_000) * 0.30);
        } else {
            $annualPAYE = (1_200_000 * 0.10) + (900_000 * 0.20) + ((120_000_000 - 4_920_000) * 0.30) + (($annual - 120_000_000) * 0.40);
        }

        return round($annualPAYE / 12, 2);
    }

    // ── NSSF Employee Contribution (5% of gross, capped) ───────────
    // NSSF Act 2014: Employee = 5%, Employer = 10%
    // Contribution is on gross pay, no cap enforced by law currently

    public static function calculateNSSFEmployee(float $grossMonthly): float
    {
        return round($grossMonthly * 0.05, 2);
    }

    public static function calculateNSSFEmployer(float $grossMonthly): float
    {
        return round($grossMonthly * 0.10, 2);
    }

    // ── Local Service Tax (LST) — Monthly equivalent ───────────────
    // Annual tiers (charged quarterly, we divide to monthly):
    // < 200,000/month → 0 | 200k–500k → 5,000/yr | 500k–1M → 20,000/yr
    // 1M–2M → 45,000/yr | > 2M → 100,000/yr

    public static function calculateLST(float $grossMonthly): float
    {
        if ($grossMonthly < 200_000)       return 0;
        if ($grossMonthly < 500_000)       return round(5_000 / 12, 2);
        if ($grossMonthly < 1_000_000)     return round(20_000 / 12, 2);
        if ($grossMonthly < 2_000_000)     return round(45_000 / 12, 2);
        return round(100_000 / 12, 2);
    }

    // ── Full Breakdown ──────────────────────────────────────────────

    public static function calculate(float $grossPay): array
    {
        $paye         = self::calculatePAYE($grossPay);
        $nssfEmployee = self::calculateNSSFEmployee($grossPay);
        $nssfEmployer = self::calculateNSSFEmployer($grossPay);
        $lst          = self::calculateLST($grossPay);

        $totalDeductions = $paye + $nssfEmployee + $lst;
        $netPay          = $grossPay - $totalDeductions;

        return [
            'gross_pay'        => round($grossPay, 2),
            'paye'             => $paye,
            'nssf_employee'    => $nssfEmployee,
            'nssf_employer'    => $nssfEmployer,
            'lst'              => $lst,
            'total_deductions' => round($totalDeductions, 2),
            'net_pay'          => round($netPay, 2),
        ];
    }

    // ── Human-readable summary ──────────────────────────────────────

    public static function summary(float $grossPay): string
    {
        $c = self::calculate($grossPay);
        return implode("\n", [
            "Gross Pay    : UGX " . number_format($c['gross_pay']),
            "PAYE         : UGX " . number_format($c['paye']),
            "NSSF (Emp)   : UGX " . number_format($c['nssf_employee']),
            "NSSF (Emplyr): UGX " . number_format($c['nssf_employer']),
            "LST          : UGX " . number_format($c['lst']),
            "─────────────────────────────",
            "Net Pay      : UGX " . number_format($c['net_pay']),
        ]);
    }
}
