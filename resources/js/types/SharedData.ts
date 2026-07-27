import { PageProps as InertiaPageProps } from '@inertiajs/core'

export interface SharedData extends InertiaPageProps {
	auth?: {
		user?: {
			id: number
			name: string
			email: string
			currency?: string
			currency_symbol?: string
		}
	}
	flash?: {
		status?: string
		status_type?: string
	}
	translations?: {
		messages?: Record<string, string>
	}
	locale?: string
}
