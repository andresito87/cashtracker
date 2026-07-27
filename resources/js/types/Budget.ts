export type BudgetType = 'general' | 'goal'

export interface Expense {
	id: number
	name: string
	amount: string
	category?: string
	budget_id: number
	created_at?: string
	updated_at?: string
}

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
