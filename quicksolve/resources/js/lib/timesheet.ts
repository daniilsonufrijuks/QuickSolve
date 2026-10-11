export interface TimesheetDay {
    label: string;
    start: string;
    end: string;
    break_minutes: string;
}

export function minutesBetween(start: string, end: string): number | null {
    if (!/^\d{2}:\d{2}$/.test(start) || !/^\d{2}:\d{2}$/.test(end)) {
        return null;
    }

    const [startHour, startMinute] = start.split(':').map(Number);
    const [endHour, endMinute] = end.split(':').map(Number);
    const started = startHour * 60 + startMinute;
    const ended = endHour * 60 + endMinute;

    if (ended < started) {
        return null;
    }

    return ended - started;
}

export function calculateTimesheet(days: TimesheetDay[], overtimeAfterMinutes: number): { error: string } | {
    rows: Array<{ label: string; worked: string; overtime: string }>;
    total: string;
    overtime: string;
} {
    const rows: Array<{ label: string; worked: string; overtime: string }> = [];
    let total = 0;
    let overtime = 0;

    for (const day of days) {
        if (day.start.trim() === '' && day.end.trim() === '') {
            continue;
        }

        const span = minutesBetween(day.start, day.end);

        if (span === null) {
            return { error: `Enter ${day.label} as HH:MM times, with the end after the start.` };
        }

        const pause = day.break_minutes.trim() === '' ? 0 : Number(day.break_minutes);

        if (!Number.isInteger(pause) || pause < 0 || pause > span) {
            return { error: `Break minutes on ${day.label} must be a whole number that fits inside the shift.` };
        }

        const worked = span - pause;
        const extra = Math.max(worked - overtimeAfterMinutes, 0);
        total += worked;
        overtime += extra;
        rows.push({
            label: day.label,
            worked: formatHours(worked),
            overtime: formatHours(extra),
        });
    }

    if (rows.length === 0) {
        return { error: 'Enter at least one day with a start and end time.' };
    }

    return {
        rows,
        total: formatHours(total),
        overtime: formatHours(overtime),
    };
}

export function formatHours(minutes: number): string {
    const hours = Math.floor(minutes / 60);
    const rest = minutes % 60;

    return `${hours}:${String(rest).padStart(2, '0')}`;
}
