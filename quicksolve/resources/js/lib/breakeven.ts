import { formatMinor, optionalMoney, parseMoney } from './money';

export interface BreakEvenInput {
    fixed_costs: string;
    variable_cost: string;
    selling_price: string;
    currency: string;
}

export function calculateBreakEven(input: BreakEvenInput): { error: string } | {
    currency: string;
    contribution: string;
    units: string;
    revenue: string;
} {
    if (!['EUR', 'USD', 'GBP'].includes(input.currency)) {
        return { error: 'Choose a supported currency.' };
    }

    const fixed = parseMoney(input.fixed_costs);
    const variable = optionalMoney(input.variable_cost);
    const price = parseMoney(input.selling_price);

    if (fixed === null || variable === null || price === null) {
        return { error: 'Enter fixed costs, variable cost per unit, and selling price as amounts with up to two decimals.' };
    }

    const contribution = price - variable;

    if (contribution <= 0) {
        return { error: 'The selling price must be higher than the variable cost per unit.' };
    }

    const units = Math.ceil(fixed / contribution);

    return {
        currency: input.currency,
        contribution: formatMinor(contribution),
        units: String(units),
        revenue: formatMinor(units * price),
    };
}
