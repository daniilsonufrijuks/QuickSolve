import fs from 'node:fs';
import path from 'node:path';

const roots = [
    'resources/js/pages',
    'resources/js/layouts',
    'resources/js/components/marketing',
    'resources/js/components/tools',
];

function walk(dir, files = []) {
    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
        const full = path.join(dir, entry.name);

        if (entry.isDirectory()) {
            walk(full, files);
        } else if (entry.name.endsWith('.vue')) {
            files.push(full);
        }
    }

    return files;
}

function hashClass(value) {
    let hash = 0;

    for (let i = 0; i < value.length; i += 1) {
        hash = (hash * 31 + value.charCodeAt(i)) >>> 0;
    }

    return `s${hash.toString(36)}`;
}

function extract(source) {
    const templateMatch = source.match(/<template>([\s\S]*?)<\/template>/);

    if (!templateMatch) {
        return source;
    }

    let template = templateMatch[1];
    const classes = new Map();

    template = template.replace(/(?<!:)class="([^"]+)"/g, (full, value) => {
        const trimmed = value.trim().replace(/\s+/g, ' ');

        if (trimmed === '' || trimmed.includes('{{') || trimmed.includes('${')) {
            return full;
        }

        const tokens = trimmed.split(' ');
        const looksLikeUtilities = tokens.some(
            (token) =>
                token.includes('-') ||
                ['flex', 'grid', 'block', 'hidden', 'relative', 'absolute', 'fixed', 'sticky', 'truncate', 'italic', 'underline', 'contents'].includes(token),
        );

        if (!looksLikeUtilities) {
            return full;
        }

        const name = hashClass(trimmed);
        classes.set(name, trimmed);

        return `class="${name}"`;
    });

    if (classes.size === 0) {
        if (!/<style[\s>]/.test(source)) {
            return `${source.trimEnd()}\n\n<style scoped>\n</style>\n`;
        }

        return source;
    }

    const rules = [...classes.entries()]
        .map(([name, utilities]) => `.${name} {\n    @apply ${utilities};\n}`)
        .join('\n\n');

    let next = source.replace(templateMatch[1], template);

    if (/<style scoped>[\s\S]*<\/style>\s*$/.test(next)) {
        next = next.replace(/<\/style>\s*$/, `${rules}\n</style>\n`);
    } else if (/<style[\s>][\s\S]*<\/style>\s*$/.test(next)) {
        next = next.replace(/<\/style>\s*$/, `${rules}\n</style>\n`);
    } else {
        next = `${next.trimEnd()}\n\n<style scoped>\n${rules}\n</style>\n`;
    }

    return next;
}

const files = roots.flatMap((root) => walk(root));
let changed = 0;

for (const file of files) {
    const before = fs.readFileSync(file, 'utf8');
    const after = extract(before);

    if (after !== before) {
        fs.writeFileSync(file, after);
        changed += 1;
    }
}

console.log(`updated ${changed} of ${files.length} vue files`);
