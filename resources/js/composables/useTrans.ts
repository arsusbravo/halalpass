import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface PageProps {
    locale: string;
    translations: Record<string, string>;
    [key: string]: unknown;
}

/**
 * Translation composable that reads Laravel JSON translations
 * from the shared Inertia props.
 *
 * Usage:
 *   const { t, locale } = useTrans();
 *   t('Dashboard')       → 'Dasbor' (if locale is 'id')
 *   t('Hello, :name', { name: 'Ario' }) → 'Halo, Ario'
 */
export function useTrans() {
    const page = usePage<PageProps>();

    const locale = computed<string>(() => page.props.locale || 'id');
    const translations = computed<Record<string, string>>(() => page.props.translations || {});

    /**
     * Translate a key with optional parameter replacements.
     */
    function t(key: string, replacements: Record<string, string | number> = {}): string {
        let translation: string = translations.value[key] || key;

        Object.keys(replacements).forEach((placeholder) => {
            const value = String(replacements[placeholder]);

            translation = translation
                .replace(`:${placeholder}`, value)
                .replace(`:${placeholder.toUpperCase()}`, value.toUpperCase())
                .replace(
                    `:${placeholder.charAt(0).toUpperCase() + placeholder.slice(1)}`,
                    value.charAt(0).toUpperCase() + value.slice(1)
                );
        });

        return translation;
    }

    /**
     * Format a date string based on current locale.
     * ID: dd-mm-yyyy | EN: mm-dd-yyyy
     *
     * Usage:
     *   d('2026-10-17')  → '17-10-2026' (ID) or '10-17-2026' (EN)
     *   d('2026-10-17', true)  → '17 Okt 2026' (ID) or 'Oct 17, 2026' (EN)
     */
    function d(dateStr: string | null | undefined, readable: boolean = false): string {
        if (!dateStr) return '-';

        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;

        if (readable) {
            return date.toLocaleDateString(locale.value === 'id' ? 'id-ID' : 'en-US', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
            });
        }

        const dd = String(date.getDate()).padStart(2, '0');
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const yyyy = date.getFullYear();

        return locale.value === 'id' ? `${dd}-${mm}-${yyyy}` : `${mm}-${dd}-${yyyy}`;
    }

    return { t, d, locale };
}