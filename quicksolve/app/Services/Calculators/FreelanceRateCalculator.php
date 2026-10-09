<?php

namespace App\Services\Calculators;

use App\Support\Money;
use InvalidArgumentException;

class FreelanceRateCalculator
{
    /**
     * The income target is the amount you want left after business expenses.
     * Required revenue is that target plus the expenses.
     * Available days are working weeks times days per week, minus unpaid leave.
     * The hourly rate divides required revenue by billable hours. The daily rate divides it by available days.
     *
     * @param  array{
     *     income_target: string,
     *     annual_expenses?: string,
     *     hours_per_day: string,
     *     days_per_week: int|string,
     *     weeks_per_year: int|string,
     *     unpaid_leave_days?: int|string,
     *     currency?: string
     * }  $input
     * @return array<string, mixed>
     */
    public function calculate(array $input): array
    {
        $currency = strtoupper($input['currency'] ?? 'EUR');

        if (! in_array($currency, ['EUR', 'USD', 'GBP'], true)) {
            throw new InvalidArgumentException('Choose a supported currency.');
        }

        $income = Money::parse($input['income_target']);
        $expenses = Money::optional($input['annual_expenses'] ?? '0');
        $hoursHundredths = Money::quantityHundredths($input['hours_per_day']);

        if ($hoursHundredths > 2400) {
            throw new InvalidArgumentException('Billable hours per day cannot exceed 24.');
        }

        $daysPerWeek = $this->whole($input['days_per_week'], 1, 7, 'Days per week');
        $weeks = $this->whole($input['weeks_per_year'], 1, 52, 'Weeks per year');
        $leave = $this->whole($input['unpaid_leave_days'] ?? 0, 0, 366, 'Unpaid leave');
        $availableDays = ($weeks * $daysPerWeek) - $leave;

        if ($availableDays < 1) {
            throw new InvalidArgumentException('Unpaid leave uses every working day. Leave at least one billable day.');
        }

        $revenue = $income + $expenses;
        $hourHundredths = $availableDays * $hoursHundredths;
        $hourly = intdiv(($revenue * 100) + intdiv($hourHundredths, 2), $hourHundredths);
        $daily = intdiv($revenue + intdiv($availableDays, 2), $availableDays);

        return [
            'currency' => $currency,
            'required_revenue' => Money::format($revenue),
            'available_days' => $availableDays,
            'billable_hours' => Money::format($hourHundredths),
            'hourly_rate' => Money::format($hourly),
            'daily_rate' => Money::format($daily),
        ];
    }

    private function whole(int|string $value, int $min, int $max, string $label): int
    {
        $number = filter_var($value, FILTER_VALIDATE_INT);

        if ($number === false || $number < $min || $number > $max) {
            throw new InvalidArgumentException($label.' must be a whole number from '.$min.' to '.$max.'.');
        }

        return $number;
    }
}
