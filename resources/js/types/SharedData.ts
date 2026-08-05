import {PageProps as InertiaPageProps} from '@inertiajs/core'
import {BillingCatalog} from './BillingCatalog'

export interface SharedData extends InertiaPageProps {
	auth?: {
		user?: {
			id: number
			name: string
			email: string
			currency?: string
			currency_symbol?: string
			subscribed?: boolean
			on_grace_period?: boolean
			ends_at?: string | null
			plan?: 'yearly' | 'monthly' | null
		}
	}
	catalog?: BillingCatalog | null
	flash?: {
		status?: string
		status_type?: string
	}
	translations?: {
		messages?: Record<string, string>
	}
	locale?: string
	available_locales?: string[]
	default_locale?: string
}
