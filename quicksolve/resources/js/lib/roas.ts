import { formatBasisPoints, formatMinor, optionalMoney, parseMoney, ratioBasisPoints } from './money';

export interface RoasInput {
    ad_spend: string;
    revenue: string;
    product_cost: string;
    currency: string;
}

export function calculateRoas(input: RoasInput): { error: string } | {
    currency: string;
    roas: string;
    profit: string;
    break_even_roas: string | null;
} {
    if (!['EUR', 'USD', 'GBP'].includes(input.currency)) {
        return { error: 'Choose a supported currency.' };
    }

    const spend = parseMoney(input.ad_spend);
    const revenue = parseMoney(input.revenue);
    const cost = optionalMoney(input.product_cost);

    if (spend === null || revenue === null || cost === null) {
        return { error: 'Enter ad spend and revenue as amounts with up to two decimals.' };
    }

    if (spend === 0) {
        return { error: 'Ad spend must be greater than zero.' };
    }

    const profit = revenue - cost - spend;
    const contribution = revenue - cost;
    const roasPoints = ratioBasisPoints(revenue, spend) ?? 0;
    const breakEven = contribution <= 0 ? null : ratioBasisPoints(revenue, contribution);

    return {
        currency: input.currency,
        roas: formatMultiplier(roasPoints),
        profit: formatMinor(profit),
        break_even_roas: breakEven === null ? null : formatMultiplier(breakEven),
    };
}

function formatMultiplier(basisPoints: number): string {
    return `${formatBasisPoints(Math.floor(basisPoints / 100))}x`;
}
