/**
 * Formats a numeric or string amount into localized currency format using the native Web Intl API.
 * Uses `useGrouping: 'always'` to guarantee a thousand separators on 4+ digit amounts across all locales.
 * Uses non-breaking space (\u00A0) between amount and symbol to prevent orphan currency symbols on line breaks.
 *
 * The Intl locale is resolved via `INTL_LOCALE_MAP` keyed off the application locale (`en` / `es`), so adding a
 * new language requires only extending the map — no binary branch edits here.
 *
 * In 'es' locale: 1622.00 => "1.622,00 €"
 * In 'en' locale: 1622.00 => "1,622.00 €"
 */
import {INTL_LOCALE_MAP} from '@/utils/locales'

export function formatCurrency(
	val: number | string,
	currencySymbol: string = '€',
	locale: string = 'es'
): string {
	const amountNum = typeof val === 'string' ? parseFloat(val || '0') : val
	const intlLocale = INTL_LOCALE_MAP[locale] ?? INTL_LOCALE_MAP.es ?? 'es-ES'
	const safeAmount = isNaN(amountNum) ? 0 : amountNum

	let formatted = new Intl.NumberFormat(intlLocale, {
		useGrouping: 'always',
		minimumFractionDigits: 2,
		maximumFractionDigits: 2,
	}).format(safeAmount)

	// In some JS ICU implementations of es-ES, non-breaking spaces (\u00A0 / \u202F) are used for grouping.
	// We normalize non-breaking spaces to dots in Spanish for crisp typography.
	if (locale !== 'en') {
		formatted = formatted.replace(/[\u00A0\u202F]/g, '.')
	}

	return `${formatted}\u00A0${currencySymbol}`
}
