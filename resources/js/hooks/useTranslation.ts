import {usePage} from '@inertiajs/react'
import {SharedData} from '@/types'

export const useTranslation = () => {
	const {translations, locale, default_locale} = usePage<SharedData>().props
	const messages = translations?.messages || {}

	const t = (key: string, replacements: Record<string, string | number> = {}): string => {
		let text = messages[key] || key
		Object.entries(replacements).forEach(([k, v]) => {
			text = text.replace(`:${k}`, String(v))
		})
		return text
	}

	return {t, locale: locale || default_locale || 'en'}
}
