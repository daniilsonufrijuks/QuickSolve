export function calculateDateDifference(start: string, end: string): { error: string } | {
    days: number;
    weeks: number;
    leftover_days: number;
    months: number;
    years: number;
} {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(start) || !/^\d{4}-\d{2}-\d{2}$/.test(end)) {
        return { error: 'Enter both dates as YYYY-MM-DD.' };
    }

    const from = new Date(`${start}T00:00:00`);
    const to = new Date(`${end}T00:00:00`);

    if (Number.isNaN(from.getTime()) || Number.isNaN(to.getTime())) {
        return { error: 'Enter real calendar dates.' };
    }

    const later = to >= from ? to : from;
    const earlier = to >= from ? from : to;
    const days = Math.round((later.getTime() - earlier.getTime()) / 86400000);
    let years = later.getFullYear() - earlier.getFullYear();
    let months = later.getMonth() - earlier.getMonth();

    if (later.getDate() < earlier.getDate()) {
        months -= 1;
    }

    if (months < 0) {
        years -= 1;
        months += 12;
    }

    return {
        days,
        weeks: Math.floor(days / 7),
        leftover_days: days % 7,
        months: years * 12 + months,
        years,
    };
}
