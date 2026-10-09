export function parseMoney(value: string): number | null {
    const trimmed = value.trim();

    if (trimmed === '') {
        return null;
    }

    if (!/^\d+(\.\d{1,2})?$/.test(trimmed)) {
        return null;
    }

    const [whole, fraction = ''] = trimmed.split('.');

    return Number(whole) * 100 + Number((fraction + '00').slice(0, 2));
}

export function optionalMoney(value: string): number | null {
    return value.trim() === '' ? 0 : parseMoney(value);
}

export function parsePercent(value: string): number | null {
    const trimmed = value.trim();

    if (trimmed === '') {
        return 0;
    }

    if (!/^\d+(\.\d{1,2})?$/.test(trimmed)) {
        return null;
    }

    const [whole, fraction = ''] = trimmed.split('.');
    const basisPoints = Number(whole) * 100 + Number((fraction + '00').slice(0, 2));

    return basisPoints > 10000 ? null : basisPoints;
}

export function formatMinor(minor: number): string {
    const negative = minor < 0;
    const absolute = Math.abs(minor);

    return `${negative ? '-' : ''}${Math.floor(absolute / 100)}.${String(absolute % 100).padStart(2, '0')}`;
}

export function formatBasisPoints(basisPoints: number): string {
    const negative = basisPoints < 0;
    const absolute = Math.abs(basisPoints);

    return `${negative ? '-' : ''}${Math.floor(absolute / 100)}.${String(absolute % 100).padStart(2, '0')}`;
}

export function percentOf(minor: number, basisPoints: number): number {
    return Math.floor((minor * basisPoints + 5000) / 10000);
}

export function ratioBasisPoints(numerator: number, denominator: number): number | null {
    if (denominator === 0) {
        return null;
    }

    const negative = numerator < 0 !== denominator < 0;
    const value = Math.floor((Math.abs(numerator) * 10000 + Math.floor(Math.abs(denominator) / 2)) / Math.abs(denominator));

    return negative ? -value : value;
}

export function quantityHundredths(value: string): number | null {
    if (!/^\d+(\.\d{1,2})?$/.test(value.trim())) {
        return null;
    }

    const [whole, fraction = ''] = value.trim().split('.');
    const hundredths = Number(whole) * 100 + Number((fraction + '00').slice(0, 2));

    return hundredths < 1 ? null : hundredths;
}

export function lineTotal(quantityHundredths: number, unitMinor: number): number {
    return Math.floor((quantityHundredths * unitMinor + 50) / 100);
}
