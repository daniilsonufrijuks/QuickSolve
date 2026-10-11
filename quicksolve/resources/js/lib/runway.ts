import { parseMoney } from './money';

export interface RunwayInput {
    cash: string;
    monthly_burn: string;
    monthly_inflow: string;
    currency: string;
}

export function calculateRunway(input: RunwayInput): { error: string } | {
    cash: string;
    net_burn: string;
    months: string;
    currency: string;
    profitable: boolean;
} {
    const cash = parseMoney(input.cash);
    const burn = parseMoney(input.monthly_burn);
    const inflow = parseMoney(input.monthly_inflow);

    if (cash === null || burn === null || inflow === null) {
        return { error: 'Enter cash, monthly costs, and monthly inflow as amounts.' };
    }

    if (cash < 0 || burn < 0 || inflow < 0) {
        return { error: 'Amounts cannot be negative.' };
    }

    const net = burn - inflow;

    if (net <= 0) {
        return {
            cash: (cash / 100).toFixed(2),
            net_burn: (net / 100).toFixed(2),
            months: 'n/a',
            currency: input.currency,
            profitable: true,
        };
    }

    const months = Math.floor((cash / net) * 10) / 10;

    return {
        cash: (cash / 100).toFixed(2),
        net_burn: (net / 100).toFixed(2),
        months: months.toFixed(1),
        currency: input.currency,
        profitable: false,
    };
}
