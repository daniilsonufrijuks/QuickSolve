import { formatBasisPoints, formatMinor, parseMoney, parsePercent, percentOf, ratioBasisPoints } from './money';

export interface DiscountInput {
    original_price: string;
    discount_type: 'percent' | 'amount';
    discount_percent: string;
    discount_amount: string;
    successive_percents: string[];
    currency: string;
}

export interface DiscountResult {
    currency: string;
    original_price: string;
    sale_price: string;
    savings: string;
    effective_discount: string;
}

export function calculateDiscount(input: DiscountInput): DiscountResult | { error: string } {
    if (!['EUR', 'USD', 'GBP'].includes(input.currency)) {
        return { error: 'Choose a supported currency.' };
    }

    const original = parseMoney(input.original_price);

    if (original === null) {
        return { error: 'Enter an original price with up to two decimal places.' };
    }

    let price = original;

    if (input.discount_type === 'amount') {
        const amount = parseMoney(input.discount_amount);

        if (amount === null) {
            return { error: 'Enter the discount amount with up to two decimal places.' };
        }

        price = Math.max(0, price - amount);
    } else {
        const applied = applyPercent(price, input.discount_percent);

        if (applied === null) {
            return { error: 'Enter a discount percentage from 0 to 100.' };
        }

        price = applied;
    }

    for (const percent of input.successive_percents) {
        if (percent.trim() === '') {
            continue;
        }

        const applied = applyPercent(price, percent);

        if (applied === null) {
            return { error: 'Each extra discount must be a percentage from 0 to 100.' };
        }

        price = applied;
    }

    const savings = original - price;
    const effective = ratioBasisPoints(savings, original);

    return {
        currency: input.currency,
        original_price: formatMinor(original),
        sale_price: formatMinor(price),
        savings: formatMinor(savings),
        effective_discount: effective === null ? '0.00' : formatBasisPoints(effective),
    };
}

function applyPercent(price: number, percent: string): number | null {
    const rate = parsePercent(percent);

    if (rate === null) {
        return null;
    }

    return price - percentOf(price, rate);
}
