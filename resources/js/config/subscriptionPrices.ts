export const PLAN_PRICES = {
	EUR: {
		monthly: 39,
		yearly: 299,
		symbol: '€',
	},
	USD: {
		monthly: 39,
		yearly: 299,
		symbol: '$',
	},
} as const

export interface FormattedPlanPrices {
	monthlyPrice: string
	yearlyPrice: string
	monthlyEquivalent: string
	monthlyPriceTotal: string
	savings: string
}

export const getPlanPrices = (currency?: string, symbolOverride?: string): FormattedPlanPrices => {
	const isUSD = currency === 'USD'
	const symbol = symbolOverride || (isUSD ? '$' : '€')

	const config = isUSD ? PLAN_PRICES.USD : PLAN_PRICES.EUR
	const monthly = config.monthly
	const yearly = config.yearly
	const monthlyTotal = monthly * 12
	const savings = monthlyTotal - yearly
	const monthlyEquivalent = yearly / 12

	const formatAmount = (num: number) => {
		if (isUSD) {
			const formattedNum = num % 1 === 0 ? num.toString() : num.toFixed(2)
			return `$${formattedNum}`
		}
		const formattedNum = num % 1 === 0 ? num.toString() : num.toFixed(2).replace('.', ',')
		return `${formattedNum}${symbol}`
	}

	return {
		monthlyPrice: formatAmount(monthly),
		yearlyPrice: formatAmount(yearly),
		monthlyEquivalent: formatAmount(monthlyEquivalent),
		monthlyPriceTotal: formatAmount(monthlyTotal),
		savings: formatAmount(savings),
	}
}
