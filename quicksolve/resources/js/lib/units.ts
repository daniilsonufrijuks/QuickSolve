export const unitGroups: Record<string, Record<string, number>> = {
    length: { m: 1, km: 1000, cm: 0.01, mm: 0.001, in: 0.0254, ft: 0.3048, yd: 0.9144, mi: 1609.344 },
    weight: { kg: 1, g: 0.001, lb: 0.45359237, oz: 0.028349523125 },
    temperature: { C: 1, F: 1, K: 1 },
    volume: { l: 1, ml: 0.001, gal: 3.785411784, cup: 0.2365882365 },
    speed: { 'm/s': 1, 'km/h': 1 / 3.6, mph: 0.44704 },
};

export function convertUnit(group: string, from: string, to: string, amount: string): { error: string } | { value: string } {
    if (!/^-?\d+(\.\d{1,6})?$/.test(amount.trim())) {
        return { error: 'Enter a number with up to six decimals.' };
    }

    const quantity = Number(amount);

    if (group === 'temperature') {
        const celsius = from === 'C' ? quantity : from === 'F' ? (quantity - 32) * (5 / 9) : quantity - 273.15;
        const result = to === 'C' ? celsius : to === 'F' ? celsius * (9 / 5) + 32 : celsius + 273.15;

        return { value: result.toFixed(2) };
    }

    const table = unitGroups[group];

    if (!table || table[from] === undefined || table[to] === undefined) {
        return { error: 'Choose units from the same group.' };
    }

    const base = quantity * table[from];

    return { value: (base / table[to]).toFixed(4).replace(/\.?0+$/, '') };
}
