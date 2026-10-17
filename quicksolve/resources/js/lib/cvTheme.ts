export type CvTheme = {
    id: string;
    name: string;
    layout: 'sidebar_left' | 'sidebar_right' | 'header_band' | 'stacked' | 'photo_top';
    paper: string;
    ink: string;
    muted: string;
    accent: string;
    sidebar: string;
    sidebar_ink: string;
    heading: string;
    type: 'sans' | 'serif';
    name_size: 'md' | 'lg' | 'xl';
    photo: 'circle' | 'rounded' | 'hidden';
    header_align: 'left' | 'center';
    skills: 'chips' | 'list';
};

const STORAGE_KEY = 'quicksolve.cv.custom-themes';
const MAX_SAVED = 12;

const LAYOUTS: CvTheme['layout'][] = ['sidebar_left', 'sidebar_right', 'header_band', 'stacked', 'photo_top'];
const TYPES: CvTheme['type'][] = ['sans', 'serif'];
const NAME_SIZES: CvTheme['name_size'][] = ['md', 'lg', 'xl'];
const PHOTOS: CvTheme['photo'][] = ['circle', 'rounded', 'hidden'];
const ALIGNS: CvTheme['header_align'][] = ['left', 'center'];
const SKILLS: CvTheme['skills'][] = ['chips', 'list'];

const FALLBACK: CvTheme = {
    id: 'coral',
    name: 'Coral column',
    layout: 'sidebar_left',
    paper: '#fff8f4',
    ink: '#3f2e2a',
    muted: '#8d6e68',
    accent: '#e07a5f',
    sidebar: '#e07a5f',
    sidebar_ink: '#fff7f3',
    heading: '#c45c40',
    type: 'sans',
    name_size: 'lg',
    photo: 'circle',
    header_align: 'left',
    skills: 'chips',
};

function themeId(value: unknown): string {
    if (value === '' || value === 'custom') {
        return '';
    }

    if (typeof value === 'string' && (/^c-[a-z0-9]{6,20}$/.test(value) || /^[a-z]{3,20}$/.test(value))) {
        return value;
    }

    return FALLBACK.id;
}

function pick<T extends string>(value: unknown, allowed: readonly T[], fallback: T): T {
    return typeof value === 'string' && (allowed as readonly string[]).includes(value) ? (value as T) : fallback;
}

function hex(value: unknown, fallback: string): string {
    return typeof value === 'string' && /^#[0-9A-Fa-f]{6}$/.test(value) ? value.toLowerCase() : fallback;
}

export function sanitizeTheme(theme: Partial<CvTheme> | null | undefined): CvTheme {
    const source = theme ?? {};

    return {
        id: themeId(source.id),
        name: typeof source.name === 'string' ? source.name.replace(/<[^>]*>/g, '').trim().slice(0, 40) || FALLBACK.name : FALLBACK.name,
        layout: pick(source.layout, LAYOUTS, FALLBACK.layout),
        paper: hex(source.paper, FALLBACK.paper),
        ink: hex(source.ink, FALLBACK.ink),
        muted: hex(source.muted, FALLBACK.muted),
        accent: hex(source.accent, FALLBACK.accent),
        sidebar: hex(source.sidebar, FALLBACK.sidebar),
        sidebar_ink: hex(source.sidebar_ink, FALLBACK.sidebar_ink),
        heading: hex(source.heading, FALLBACK.heading),
        type: pick(source.type, TYPES, FALLBACK.type),
        name_size: pick(source.name_size, NAME_SIZES, FALLBACK.name_size),
        photo: pick(source.photo, PHOTOS, FALLBACK.photo),
        header_align: pick(source.header_align, ALIGNS, FALLBACK.header_align),
        skills: pick(source.skills, SKILLS, FALLBACK.skills),
    };
}

export function cloneTheme(theme: Partial<CvTheme> | null | undefined): CvTheme {
    return { ...sanitizeTheme(theme) };
}

export function blankCustomTheme(from?: Partial<CvTheme> | null): CvTheme {
    const cloned = cloneTheme(from ?? FALLBACK);
    cloned.id = '';
    cloned.name = cloned.name.endsWith(' copy') ? cloned.name : `${cloned.name} copy`;

    return cloned;
}

export function themeVars(theme: CvTheme): Record<string, string> {
    const safe = sanitizeTheme(theme);

    return {
        '--cv-paper': safe.paper,
        '--cv-ink': safe.ink,
        '--cv-muted': safe.muted,
        '--cv-accent': safe.accent,
        '--cv-sidebar': safe.sidebar,
        '--cv-sidebar-ink': safe.sidebar_ink,
        '--cv-heading': safe.heading,
        fontFamily: safe.type === 'serif' ? "Georgia, 'Times New Roman', serif" : 'inherit',
    };
}

export function listCustomThemes(): CvTheme[] {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        const parsed = raw ? JSON.parse(raw) : [];

        if (!Array.isArray(parsed)) {
            return [];
        }

        return parsed.slice(0, MAX_SAVED).map((item) => sanitizeTheme(item)).filter((item) => item.id.startsWith('c-'));
    } catch {
        return [];
    }
}

export function upsertCustomTheme(theme: CvTheme): { theme: CvTheme; error: string } {
    const current = listCustomThemes();
    const clean = sanitizeTheme(theme);
    const id = clean.id.startsWith('c-') ? clean.id : `c-${Math.random().toString(36).slice(2, 10)}`;
    const next: CvTheme = { ...clean, id, name: clean.name.slice(0, 40) || 'My template' };
    const index = current.findIndex((item) => item.id === id);

    if (index === -1 && current.length >= MAX_SAVED) {
        return { theme: next, error: `This browser can store ${MAX_SAVED} custom templates. Delete one first.` };
    }

    if (index === -1) {
        current.push(next);
    } else {
        current[index] = next;
    }

    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(current));
    } catch {
        return { theme: next, error: 'This browser blocked saving the template.' };
    }

    return { theme: next, error: '' };
}

export function deleteCustomTheme(id: string): CvTheme[] {
    const next = listCustomThemes().filter((item) => item.id !== id);

    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
    } catch {
        return listCustomThemes();
    }

    return next;
}

export function findTheme(themes: CvTheme[], id: string): CvTheme | null {
    return themes.find((item) => item.id === id) ?? null;
}
