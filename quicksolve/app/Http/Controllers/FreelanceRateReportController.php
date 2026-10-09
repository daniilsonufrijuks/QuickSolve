<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFreelanceRateReportRequest;
use App\Services\Calculators\FreelanceRateCalculator;

class FreelanceRateReportController extends Controller
{
    public function __invoke(StoreFreelanceRateReportRequest $request, FreelanceRateCalculator $calculator)
    {
        $result = $calculator->calculate($request->validated());
        $lines = [
            'QuickSolve freelance rate report',
            'This report estimates a rate. It is not tax, legal, or accounting advice.',
            '',
            'Currency: '.$result['currency'],
            'Required revenue: '.$result['required_revenue'],
            'Available days: '.$result['available_days'],
            'Billable hours: '.$result['billable_hours'],
            'Hourly rate: '.$result['hourly_rate'],
            'Daily rate: '.$result['daily_rate'],
            '',
            'Required revenue is the income target plus annual business expenses.',
            'Available days are working weeks times days per week, minus unpaid leave.',
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="freelance-rate-report.txt"',
        ]);
    }
}
