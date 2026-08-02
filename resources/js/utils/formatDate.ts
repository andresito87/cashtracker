/**
 * Formats a date string or Date object according to the given locale and format options.
 *
 * The Intl locale is resolved via `INTL_LOCALE_MAP` keyed off the application locale (`en` / `es`), so adding a
 * new language requires only extending the map — no binary branch edits here.
 */
import {INTL_LOCALE_MAP} from '@/utils/locales'

export function formatDate(
	dateInput?: string | Date,
	locale: string = 'es',
	options: Intl.DateTimeFormatOptions = {
		day: 'numeric',
		month: 'long',
		year: 'numeric',
	}
): string {
	if (!dateInput) return ''

	const date = typeof dateInput === 'string' ? new Date(dateInput) : dateInput
	if (isNaN(date.getTime())) return ''

	const intlLocale = INTL_LOCALE_MAP[locale] ?? INTL_LOCALE_MAP.es ?? 'es-ES'

	return new Intl.DateTimeFormat(intlLocale, options).format(date)
}
