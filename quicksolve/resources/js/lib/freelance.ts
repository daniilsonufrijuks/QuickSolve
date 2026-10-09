import { formatMinor, optionalMoney, parseMoney, quantityHundredths } from './money';

export interface FreelanceInput {
    income_target: string;
    annual_expenses: string;
    hours_per_day: string;
    days_per_week: string;
    weeks_per_year: string;
    unpaid_leave_days: string;
    currency: string;
}

export interface FreelanceResult {
    currency: string;
    required_revenue: string;
    available_days: number;
    billable_hours: string;
    hourly_rate: string;
    daily_rate: string;
}

export function calculateFreelanceRate(input: FreelanceInput): FreelanceResult | { error: string } {
    if (!['EUR', 'USD', 'GBP'].includes(input.currency)) {
        return { error: 'Choose a supported currency.' };
    }

    const income = parseMoney(input.income_target);
    const expenses = optionalMoney(input.annual_expenses);
    const hoursHundredths = quantityHundredths(input.hours_per_day);
    const daysPerWeek = whole(input.days_per_week, 1, 7);
    const weeks = whole(input.weeks_per_year, 1, 52);
    const leave = input.unpaid_leave_days.trim() === '' ? 0 : whole(input.unpaid_leave_days, 0, 366);

    if (income === null || expenses === null) {
        return { error: 'Enter the income target and expenses with up to two decimal places.' };
    }

    if (hoursHundredths === null || hoursHundredths > 2400) {
        return { error: 'Enter billable hours per day from 0.01 to 24.' };
    }

    if (daysPerWeek === null || weeks === null || leave === null) {
        return { error: 'Check the working days, weeks, and unpaid leave.' };
    }

    const availableDays = weeks * daysPerWeek - leave;

    if (availableDays < 1) {
        return { error: 'Unpaid leave uses every working day. Leave at least one billable day.' };
    }

    const revenue = income + expenses;
    const hourHundredths = availableDays * hoursHundredths;
    const hourly = Math.floor((revenue * 100 + Math.floor(hourHundredths / 2)) / hourHundredths);
    const daily = Math.floor((revenue + Math.floor(availableDays / 2)) / availableDays);

    return {
        currency: input.currency,
        required_revenue: formatMinor(revenue),
        available_days: availableDays,
        billable_hours: formatMinor(hourHundredths),
        hourly_rate: formatMinor(hourly),
        daily_rate: formatMinor(daily),
    };
}

function whole(value: string, min: number, max: number): number | null {
    if (!/^\d+$/.test(value.trim())) {
        return null;
    }

    const number = Number(value);

    return number >= min && number <= max ? number : null;
}
