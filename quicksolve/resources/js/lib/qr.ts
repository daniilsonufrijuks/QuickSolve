export interface QrInput {
    type: 'url' | 'wifi' | 'contact' | 'text';
    url: string;
    wifi_ssid: string;
    wifi_password: string;
    wifi_security: 'WPA' | 'WEP' | 'NOPASS';
    wifi_hidden: boolean;
    first_name: string;
    last_name: string;
    phone: string;
    email: string;
    organization: string;
    text: string;
}

export function qrPayload(input: QrInput): string | { error: string } {
    if (input.type === 'url') {
        const url = input.url.trim();

        if (!/^https?:\/\/\S+$/i.test(url)) {
            return { error: 'Enter a URL that starts with http:// or https://.' };
        }

        return url;
    }

    if (input.type === 'wifi') {
        const ssid = escapeWifi(input.wifi_ssid);

        if (ssid === '') {
            return { error: 'Enter the Wi-Fi network name.' };
        }

        const security = input.wifi_security;
        const password = security === 'NOPASS' ? '' : escapeWifi(input.wifi_password);
        const hidden = input.wifi_hidden ? 'true' : 'false';

        return `WIFI:T:${security};S:${ssid};P:${password};H:${hidden};;`;
    }

    if (input.type === 'contact') {
        const first = vcard(input.first_name);
        const last = vcard(input.last_name);
        const full = `${first} ${last}`.trim();

        if (full === '') {
            return { error: 'Enter a contact name.' };
        }

        const lines = ['BEGIN:VCARD', 'VERSION:3.0', `N:${last};${first}`, `FN:${full}`];
        const organization = vcard(input.organization);
        const phone = vcard(input.phone);
        const email = vcard(input.email);

        if (organization) {
            lines.push(`ORG:${organization}`);
        }

        if (phone) {
            lines.push(`TEL:${phone}`);
        }

        if (email) {
            lines.push(`EMAIL:${email}`);
        }

        lines.push('END:VCARD');

        return lines.join('\n');
    }

    const text = input.text.trim();

    if (text === '') {
        return { error: 'Enter the text to encode.' };
    }

    if (text.length > 500) {
        return { error: 'Keep the text to 500 characters so the code stays reliable.' };
    }

    return text;
}

function escapeWifi(value: string): string {
    return value.trim().replace(/\\/g, '\\\\').replace(/;/g, '\\;').replace(/,/g, '\\,').replace(/:/g, '\\:');
}

function vcard(value: string): string {
    return value.trim().replace(/[\r\n;]/g, ' ');
}
