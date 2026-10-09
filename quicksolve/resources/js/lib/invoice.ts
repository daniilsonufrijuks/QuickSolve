import { formatBasisPoints, formatMinor, lineTotal, parseMoney, parsePercent, percentOf, quantityHundredths } from './money';

export interface InvoiceLineInput {
    description: string;
    quantity: string;
    unit_price: string;
}

export interface InvoiceResult {
    currency: string;
    items: Array<{ description: string; quantity: string; unit_price: string; line_total: string }>;
    subtotal: string;
    tax_rate: string;
    tax: string;
    total: string;
}

export function calculateInvoice(input: {
    currency: string;
    tax_rate: string;
    items: InvoiceLineInput[];
}): InvoiceResult | { error: string } {
    if (!['EUR', 'USD', 'GBP'].includes(input.currency)) {
        return { error: 'Choose a supported currency.' };
    }

    if (input.items.length === 0) {
        return { error: 'Add at least one line item.' };
    }

    const taxRate = parsePercent(input.tax_rate);

    if (taxRate === null) {
        return { error: 'Enter a tax rate from 0 to 100.' };
    }

    let subtotal = 0;
    const items = [];

    for (const item of input.items) {
        if (item.description.trim() === '') {
            return { error: 'Each line item needs a description.' };
        }

        const quantity = quantityHundredths(item.quantity);
        const unitPrice = parseMoney(item.unit_price);

        if (quantity === null || unitPrice === null) {
            return { error: 'Check quantities and unit prices. Use up to two decimal places.' };
        }

        const total = lineTotal(quantity, unitPrice);
        subtotal += total;
        items.push({
            description: item.description.trim(),
            quantity: (quantity / 100).toFixed(2),
            unit_price: formatMinor(unitPrice),
            line_total: formatMinor(total),
        });
    }

    const tax = percentOf(subtotal, taxRate);

    return {
        currency: input.currency,
        items,
        subtotal: formatMinor(subtotal),
        tax_rate: formatBasisPoints(taxRate),
        tax: formatMinor(tax),
        total: formatMinor(subtotal + tax),
    };
}
