import {CategoryValue} from './Category'

export interface Expense {
	id: number
	name: string
	amount: string
	category?: CategoryValue
	budget_id: number
	created_at?: string
	updated_at?: string
}
