export async function postJson<T>(url: string, body: unknown): Promise<{ ok: boolean; status: number; data: T | null; message: string }> {
    const token = decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '');

    try {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': token,
            },
            body: JSON.stringify(body),
        });

        const payload = (await response.json().catch(() => null)) as { data?: T; message?: string; errors?: Record<string, string[]> } | null;
        const firstError = payload?.errors ? Object.values(payload.errors)[0]?.[0] : undefined;

        return {
            ok: response.ok,
            status: response.status,
            data: (payload?.data ?? null) as T | null,
            message: payload?.message || firstError || 'The request could not be completed.',
        };
    } catch {
        return { ok: false, status: 0, data: null, message: 'The request could not be completed.' };
    }
}

export function track(type: string, slug: string): void {
    const token = decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '');

    fetch('/api/v1/analytics/events', {
        method: 'POST',
        credentials: 'same-origin',
        keepalive: true,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': token,
        },
        body: JSON.stringify({ type, slug }),
    }).catch(() => undefined);
}
