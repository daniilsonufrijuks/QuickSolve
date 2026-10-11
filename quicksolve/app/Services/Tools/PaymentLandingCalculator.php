<?php

namespace App\Services\Tools;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

class PaymentLandingCalculator
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function calculate(array $input): array
    {
        $issue = $this->date($input['issue_date'] ?? null, 'Issue date');
        $due = $this->contractualDue($issue, (string) ($input['terms'] ?? 'net_30'));
        $holidays = $this->holidayDates($input['holiday_dates'] ?? []);
        $skipWeekends = (bool) ($input['skip_weekends'] ?? true);
        $payRun = (string) ($input['pay_run'] ?? 'any');

        $likelyPay = $this->applyPayRun($due, $payRun, $input);
        $likelyPay = $this->nextOpenDay($likelyPay, $skipWeekends, $holidays, 1);

        $settlement = max(0, min(14, (int) ($input['settlement_days'] ?? 0)));
        $likelyLand = $this->nextOpenDay($likelyPay->addDays($settlement), $skipWeekends, $holidays, 1);

        $gap = $due->diffInDays($likelyLand, false);

        return [
            'issue_date' => $issue->toDateString(),
            'contractual_due' => $due->toDateString(),
            'likely_pay_date' => $likelyPay->toDateString(),
            'likely_land_date' => $likelyLand->toDateString(),
            'gap_days' => (int) $gap,
            'settlement_days' => $settlement,
            'notice' => 'This is a calendar estimate from the dates and pay-run you entered. It is not a bank forecast and it does not know the client’s actual process.',
        ];
    }

    private function contractualDue(CarbonImmutable $issue, string $terms): CarbonImmutable
    {
        return match ($terms) {
            'net_7' => $issue->addDays(7),
            'net_14' => $issue->addDays(14),
            'net_30' => $issue->addDays(30),
            'net_45' => $issue->addDays(45),
            'net_60' => $issue->addDays(60),
            'eom' => $issue->endOfMonth()->startOfDay(),
            'eom_plus_15' => $issue->endOfMonth()->startOfDay()->addDays(15),
            default => throw new InvalidArgumentException('Choose a payment term QuickSolve supports.'),
        };
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function applyPayRun(CarbonImmutable $due, string $payRun, array $input): CarbonImmutable
    {
        return match ($payRun) {
            'any' => $due,
            'weekly' => $this->nextWeekdayOnOrAfter($due, (int) ($input['weekday'] ?? 5)),
            'monthly_day' => $this->nextMonthDayOnOrAfter($due, (int) ($input['month_day'] ?? 28)),
            'last_weekday' => $this->lastOpenWeekdayOnOrAfter($due),
            default => throw new InvalidArgumentException('Choose how this client pays.'),
        };
    }

    private function nextWeekdayOnOrAfter(CarbonImmutable $from, int $isoWeekday): CarbonImmutable
    {
        if ($isoWeekday < 1 || $isoWeekday > 5) {
            throw new InvalidArgumentException('Weekly pay-run must be Monday to Friday.');
        }

        $date = $from;

        while ((int) $date->isoWeekday() !== $isoWeekday) {
            $date = $date->addDay();
        }

        return $date;
    }

    private function nextMonthDayOnOrAfter(CarbonImmutable $from, int $day): CarbonImmutable
    {
        if ($day < 1 || $day > 28) {
            throw new InvalidArgumentException('Monthly pay-run day must be between 1 and 28.');
        }

        $candidate = $from->day($day)->startOfDay();

        if ($candidate->lt($from)) {
            $candidate = $from->addMonthNoOverflow()->day($day)->startOfDay();
        }

        return $candidate;
    }

    private function lastOpenWeekdayOnOrAfter(CarbonImmutable $from): CarbonImmutable
    {
        $candidate = $this->lastOpenWeekdayOfMonth($from);

        if ($candidate->lt($from)) {
            $candidate = $this->lastOpenWeekdayOfMonth($from->addMonthNoOverflow()->startOfMonth());
        }

        return $candidate;
    }

    private function lastOpenWeekdayOfMonth(CarbonImmutable $inMonth): CarbonImmutable
    {
        return $this->nextOpenDay($inMonth->endOfMonth()->startOfDay(), true, [], -1);
    }

    /**
     * @param  list<string>  $holidays
     */
    private function nextOpenDay(CarbonImmutable $date, bool $skipWeekends, array $holidays, int $step): CarbonImmutable
    {
        $cursor = $date->startOfDay();

        for ($i = 0; $i < 40; $i++) {
            $blocked = ($skipWeekends && $cursor->isWeekend())
                || in_array($cursor->toDateString(), $holidays, true);

            if (! $blocked) {
                return $cursor;
            }

            $cursor = $step < 0 ? $cursor->subDay() : $cursor->addDay();
        }

        throw new InvalidArgumentException('Could not find an open calendar day from the dates entered.');
    }

    /**
     * @return list<string>
     */
    private function holidayDates(mixed $dates): array
    {
        if (! is_array($dates)) {
            return [];
        }

        $parsed = [];

        foreach (array_slice($dates, 0, 24) as $date) {
            if (! is_string($date) || $date === '') {
                continue;
            }

            $parsed[] = $this->date($date, 'Holiday')->toDateString();
        }

        return array_values(array_unique($parsed));
    }

    private function date(mixed $value, string $label): CarbonImmutable
    {
        if (! is_string($value) || $value === '') {
            throw new InvalidArgumentException($label.' is required.');
        }

        $date = CarbonImmutable::createFromFormat('Y-m-d', $value);

        if (! $date instanceof CarbonImmutable || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException($label.' must be a calendar date.');
        }

        return $date->startOfDay();
    }
}
