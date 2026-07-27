/**
 * Formats a date string or Date object according to the given locale and format options.
 */
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

	const targetLocale = locale === 'en' ? 'en-US' : 'es-ES'

	return new Intl.DateTimeFormat(targetLocale, options).format(date)
}
