import { formatBasisPoints, formatMinor, optionalMoney, parseMoney, parsePercent, percentOf, ratioBasisPoints } from './money';

export interface ProfitInput {
    selling_price: string;
    product_cost: string;
    shipping_cost: string;
    packaging_cost: string;
    marketplace_fee_percent: string;
    marketplace_fee_fixed: string;
    processing_fee_percent: string;
    processing_fee_fixed: string;
    currency: string;
}

export interface ProfitResult {
    currency: string;
    selling_price: string;
    marketplace_fee: string;
    processing_fee: string;
    total_cost: string;
    profit: string;
    profit_margin: string | null;
    markup: string | null;
    break_even_price: string | null;
}

export function calculateProfit(input: ProfitInput): ProfitResult | { error: string } {
    if (!['EUR', 'USD', 'GBP'].includes(input.currency)) {
        return { error: 'Choose a supported currency.' };
    }

    const selling = parseMoney(input.selling_price);
    const product = parseMoney(input.product_cost);
    const shipping = optionalMoney(input.shipping_cost);
    const packaging = optionalMoney(input.packaging_cost);
    const marketplaceRate = parsePercent(input.marketplace_fee_percent);
    const marketplaceFixed = optionalMoney(input.marketplace_fee_fixed);
    const processingRate = parsePercent(input.processing_fee_percent);
    const processingFixed = optionalMoney(input.processing_fee_fixed);

    if (
        selling === null ||
        product === null ||
        shipping === null ||
        packaging === null ||
        marketplaceRate === null ||
        marketplaceFixed === null ||
        processingRate === null ||
        processingFixed === null
    ) {
        return { error: 'Enter amounts with up to two decimal places. Percentages cannot exceed 100.' };
    }

    const marketplaceFee = percentOf(selling, marketplaceRate) + marketplaceFixed;
    const processingFee = percentOf(selling, processingRate) + processingFixed;
    const totalCost = product + shipping + packaging + marketplaceFee + processingFee;
    const profit = selling - totalCost;
    const margin = ratioBasisPoints(profit, selling);
    const markup = ratioBasisPoints(profit, totalCost);
    const fixedCosts = product + shipping + packaging + marketplaceFixed + processingFixed;
    const combinedRate = marketplaceRate + processingRate;
    const breakEven =
        combinedRate < 10000
            ? Math.floor((fixedCosts * 10000 + Math.floor((10000 - combinedRate) / 2)) / (10000 - combinedRate))
            : null;

    return {
        currency: input.currency,
        selling_price: formatMinor(selling),
        marketplace_fee: formatMinor(marketplaceFee),
        processing_fee: formatMinor(processingFee),
        total_cost: formatMinor(totalCost),
        profit: formatMinor(profit),
        profit_margin: margin === null ? null : formatBasisPoints(margin),
        markup: markup === null ? null : formatBasisPoints(markup),
        break_even_price: breakEven === null ? null : formatMinor(breakEven),
    };
}
