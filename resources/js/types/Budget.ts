import { PageProps as InertiaPageProps } from '@inertiajs/core'

export type BudgetType = 'general' | 'goal'

export interface Budget {
	id: number
	name: string
	amount: string
	formatted_amount?: string
	type: BudgetType
	description?: string
	created_at?: string
	updated_at?: string
}

export interface SharedData extends InertiaPageProps {
	flash?: {
		status?: string
		status_type?: string
	}
	translations?: {
		messages?: Record<string, string>
	}
	locale?: string
}
