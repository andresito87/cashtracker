export type CategoryValue =
	| 'food'
	| 'transportation'
	| 'health'
	| 'entertainment'
	| 'subscriptions'
	| 'beauty'
	| 'clothing'
	| 'home'
	| 'utilities'
	| 'education'
	| 'pets'
	| 'other'

export interface Category {
	value: CategoryValue
	label?: string
}
