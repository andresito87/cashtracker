export interface BillingPlan {
	stripe_price_id: string
	price_minor: number
	display_price: string
	capabilities: string[]
	ai_limits?: Record<string, number> | null
	monthly_equivalent_minor?: number
	monthly_equivalent_display?: string
	monthly_total_minor?: number
	savings_minor?: number
}

export interface BillingCatalog {
	version: string
	currency: string
	plans: {
		monthly: BillingPlan
		yearly: BillingPlan
	}
}