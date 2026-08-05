import React from 'react'
import {usePage} from '@inertiajs/react'
import {useTranslation} from '@/hooks/useTranslation'
import {SharedData} from '@/types'

interface SubscriptionUpgradeProps {
	onSwap?: (plan: 'monthly' | 'yearly') => void
	loadingAction?: string
}

export const SubscriptionUpgrade = ({
										onSwap,
										loadingAction,
									}: SubscriptionUpgradeProps) => {
	const {auth, catalog} = usePage<SharedData>().props
	const {t} = useTranslation()

	const monthlyMinor = catalog?.plans?.monthly?.price_minor ?? 0
	const yearlyMinor = catalog?.plans?.yearly?.price_minor ?? 0
	const yearlyPrice = catalog?.plans?.yearly?.display_price ?? ''
	const monthlyPriceTotal = catalog?.plans?.monthly?.display_price ?? ''
	// Savings computed from integer cents only — no float arithmetic in React.
	const savingsMinor = monthlyMinor * 12 - yearlyMinor
	const symbol = auth?.user?.currency_symbol ?? '€'
	const isUSD = (auth?.user?.currency === 'USD')

	// Format a cents value client-side from the catalog using integer division.
	const formatMinor = (minor: number) => {
		const whole = Math.floor(minor / 100)
		const frac = minor % 100
		return isUSD
			? `$${whole.toLocaleString()}.${String(frac).padStart(2, '0')}`
			: `${whole.toLocaleString('es')},${String(frac).padStart(2, '0')}${symbol}`
	}
	const savings = formatMinor(savingsMinor)

	return (
		<div className="rounded-2xl bg-[#1b0e35] p-6 sm:p-8 text-white shadow-sm border border-purple-900/30">
			<div className="flex flex-col sm:flex-row items-start justify-between gap-6">
				<div className="flex-1">
					<div className="flex items-center gap-2 mb-2">
                        <span
							className="bg-orange-500/20 text-orange-400 text-xs font-extrabold px-3 py-1 rounded-full uppercase tracking-wider border border-orange-500/30">
                            PRO Upgrade
                        </span>
					</div>

					<h3 className="text-2xl font-extrabold text-white tracking-tight mb-2">
						{t('upgrade_yearly_title')}
					</h3>

					<p className="text-purple-200/90 text-sm sm:text-base leading-relaxed mb-6">
						{t('upgrade_yearly_desc', {
							yearlyPrice,
							monthlyPriceTotal,
							savings,
						})}
					</p>

					{onSwap && (
						<button
							onClick={() => onSwap('yearly')}
							disabled={loadingAction !== undefined}
							className="bg-orange-500 hover:bg-orange-600 active:scale-95 text-white font-bold py-3.5 px-7 rounded-xl shadow-md shadow-orange-500/25 hover:shadow-orange-500/40 transition-all duration-200 cursor-pointer text-sm inline-flex items-center gap-2 disabled:opacity-50"
						>
                            <span>
                                {loadingAction === 'swap_yearly'
									? t('loading')
									: t('upgrade_yearly_btn')}
                            </span>
							<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5"
								      d="M13 7l5 5m0 0l-5 5m5-5H6"/>
							</svg>
						</button>
					)}

					<p className="text-xs text-purple-300/70 mt-4">
						{t('upgrade_yearly_note')}
					</p>
				</div>
			</div>
		</div>
	)
}

