/**
 * Formats a numeric or string amount into localized currency format using the native Web Intl API.
 * Uses `useGrouping: 'always'` to guarantee a thousand separators on 4+ digit amounts across all locales.
 *
 * In 'es' locale: 1622.00 => "1.622,00 €"
 * In 'en' locale: 1622.00 => "1,622.00 €"
 */
export function formatCurrency(
	val: number | string,
	currencySymbol: string = '€',
	locale: string = 'es'
): string {
	const amountNum = typeof val === 'string' ? parseFloat(val || '0') : val
	if (isNaN(amountNum)) return `0${locale === 'en' ? '.00' : ',00'} ${currencySymbol}`

	const targetLocale = locale === 'en' ? 'en-US' : 'es-ES'

	let formatted = new Intl.NumberFormat(targetLocale, {
		useGrouping: 'always',
		minimumFractionDigits: 2,
		maximumFractionDigits: 2,
	}).format(amountNum)

	// In some JS ICU implementations of es-ES, non-breaking spaces (\u00A0 / \u202F) are used for grouping.
	// We normalize non-breaking spaces to dots in Spanish for crisp typography.
	if (locale !== 'en') {
		formatted = formatted.replace(/[\u00A0\u202F]/g, '.')
	}

	return `${formatted} ${currencySymbol}`
}
