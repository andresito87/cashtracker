import {Expense} from './Expense'

export type BudgetType = 'general' | 'goal'

export interface Budget {
	id: number
	name: string
	amount: string
	formatted_amount?: string
	currency_symbol?: string
	type: BudgetType
	description?: string
	expenses?: Expense[]
	created_at?: string
	updated_at?: string
}
