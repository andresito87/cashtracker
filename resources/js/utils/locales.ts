/**
 * Maps application locales (keys accepted via `?lang=`) to ICU / `Intl`
 * identifiers used by `formatCurrency` and `formatDate`. Keep in sync with
 * `config('app.available_locales')`.
 */
export const INTL_LOCALE_MAP: Record<string, string> = {
	en: 'en-US',
	es: 'es-ES',
}

