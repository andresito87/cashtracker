import React from 'react'
import {usePage} from '@inertiajs/react'
import {useTranslation} from '@/hooks/useTranslation'
import {useSubscriptionActions} from '@/hooks/useSubscriptionActions'
import {formatDate} from '@/utils/formatDate'
import {SharedData, Subscription} from '@/types'

interface PricingTableProps {
	subscription?: Subscription | null
}

export const PricingTable = ({subscription}: PricingTableProps) => {
	const {auth, catalog} = usePage<SharedData>().props
	const {t, locale} = useTranslation()
	const {
		loadingAction,
		subscribe,
		swapPlan,
		cancelSubscription,
		resumeSubscription,
	} = useSubscriptionActions()

	const isSubscribed = auth?.user?.subscribed ?? false
	const currentPlan = auth?.user?.plan

	const rawEndsAt = subscription?.ends_at || auth?.user?.ends_at
	const formattedDate = rawEndsAt ? formatDate(rawEndsAt, locale) : ''

	const monthlyPrice = catalog?.plans?.monthly?.display_price ?? ''
	const yearlyPrice = catalog?.plans?.yearly?.display_price ?? ''
	const monthlyEquivalent = catalog?.plans?.yearly?.monthly_equivalent_display ?? ''

	return (
		<div className="py-4">
			{/* Header Section */}
			<div className="text-center max-w-3xl mx-auto mb-10">
                <span
					className="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 border border-orange-500/20 mb-4 uppercase tracking-wider">
                    CashTracker PRO
                </span>
				<h1 className="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight mb-4">
					{t('pro_plans_title')}
				</h1>
				<p className="text-base sm:text-lg text-gray-500 leading-relaxed">
					{t('pro_plans_subtitle')}
				</p>
			</div>

			{/* Current Active Subscription Info Banner if applicable */}
			{isSubscribed && (
				<div
					className="max-w-4xl mx-auto mb-8 bg-purple-900/5 border border-purple-900/15 rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
					<div>
						<div className="flex items-center gap-2">
							<span className="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"/>
							<h3 className="text-lg font-bold text-purple-900">
								{subscription?.on_grace_period
									? t('subscription_canceled')
									: t('subscription_active')}
							</h3>
						</div>
						<p className="text-sm text-gray-600 mt-1">
							{formattedDate
								? `${subscription?.on_grace_period ? t('ends_on', {date: formattedDate}) : t('renews_on', {date: formattedDate})}`
								: `Plan ${currentPlan === 'yearly' ? t('plan_yearly') : t('plan_monthly')}`}
						</p>
					</div>

					<div className="flex flex-wrap items-center gap-3">
						{subscription?.on_grace_period ? (
							<button
								onClick={resumeSubscription}
								disabled={loadingAction !== null}
								className="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md shadow-orange-500/20 transition-all cursor-pointer disabled:opacity-50"
							>
								{loadingAction === 'resume' ? t('loading') : t('resume_subscription')}
							</button>
						) : (
							<button
								onClick={cancelSubscription}
								disabled={loadingAction !== null}
								className="px-4 py-2 rounded-xl bg-gray-100 hover:bg-red-50 text-gray-700 hover:text-red-600 font-semibold text-sm transition-all border border-gray-200 cursor-pointer disabled:opacity-50"
							>
								{loadingAction === 'cancel' ? t('loading') : t('cancel_subscription')}
							</button>
						)}
					</div>
				</div>
			)}

			{/* Plans Grid */}
			<div className="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
				{/* Monthly Plan */}
				<div
					className={`rounded-2xl bg-white p-6 sm:p-8 flex flex-col justify-between transition-all duration-200 ${
						currentPlan === 'monthly'
							? 'border-2 border-purple-900 shadow-md ring-4 ring-purple-900/10'
							: 'border border-purple-900/15 shadow-sm hover:shadow-md'
					}`}>
					<div>
						<div className="flex justify-between items-center mb-4">
							<h2 className="text-2xl font-bold text-purple-900">{t('plan_monthly')}</h2>
							{currentPlan === 'monthly' && (
								<span
									className="bg-purple-100 text-purple-900 text-xs font-bold px-3 py-1 rounded-full uppercase">
                                    {t('status_active')}
                                </span>
							)}
						</div>

						<div className="flex items-baseline gap-1 my-4">
							<span className="text-5xl font-extrabold text-gray-900 tracking-tight">{monthlyPrice}</span>
							<span className="text-gray-500 font-semibold">{t('per_month')}</span>
						</div>

						<ul className="mt-6 space-y-3.5 text-sm sm:text-base text-gray-600">
							<li className="flex items-center gap-3">
								<svg className="w-5 h-5 text-orange-500 shrink-0" fill="none" stroke="currentColor"
								     viewBox="0 0 24 24">
									<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5"
									      d="M5 13l4 4L19 7"/>
								</svg>
								<span>{t('feature_unlimited_ai')}</span>
							</li>
							<li className="flex items-center gap-3">
								<svg className="w-5 h-5 text-orange-500 shrink-0" fill="none" stroke="currentColor"
								     viewBox="0 0 24 24">
									<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5"
									      d="M5 13l4 4L19 7"/>
								</svg>
								<span>{t('feature_ticket_scanning')}</span>
							</li>
							<li className="flex items-center gap-3">
								<svg className="w-5 h-5 text-orange-500 shrink-0" fill="none" stroke="currentColor"
								     viewBox="0 0 24 24">
									<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5"
									      d="M5 13l4 4L19 7"/>
								</svg>
								<span>{t('feature_cancel_anytime')}</span>
							</li>
						</ul>
					</div>

					<div className="mt-8 pt-4 border-t border-gray-100">
						{isSubscribed ? (
							currentPlan === 'monthly' ? (
								<button
									disabled
									className="w-full py-3.5 px-6 rounded-xl bg-gray-100 text-gray-400 font-bold text-center cursor-not-allowed"
								>
									{t('current_plan')}
								</button>
							) : (
								<button
									onClick={() => swapPlan('monthly')}
									disabled={loadingAction !== null}
									className="w-full py-3.5 px-6 rounded-xl bg-purple-900 hover:bg-purple-800 text-white font-bold text-center shadow-md transition-all cursor-pointer disabled:opacity-50"
								>
									{loadingAction === 'swap_monthly' ? t('loading') : t('change_plan')}
								</button>
							)
						) : (
							<button
								onClick={() => subscribe('monthly')}
								disabled={loadingAction !== undefined}
								className="w-full py-3.5 px-6 rounded-xl bg-orange-500 hover:bg-orange-600 active:scale-95 text-white font-bold text-center shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer border-0 outline-none disabled:opacity-50"
							>
								{loadingAction === 'checkout_monthly' ? t('loading') : t('get_pro_monthly')}
							</button>
						)}
					</div>
				</div>

				{/* Yearly Plan (Featured) */}
				<div
					className={`rounded-2xl bg-white p-6 sm:p-8 flex flex-col justify-between relative transition-all duration-200 ${
						currentPlan === 'yearly'
							? 'border-2 border-purple-900 shadow-md ring-4 ring-purple-900/10'
							: 'border-2 border-orange-500 shadow-md hover:shadow-lg'
					}`}>
					{/* Badge */}
					<span
						className="absolute -top-3.5 right-6 bg-orange-500 text-white text-xs font-extrabold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                        {t('free_months_badge', {count: 4})}
                    </span>

					<div>
						<div className="flex justify-between items-center mb-4">
							<h2 className="text-2xl font-bold text-purple-900">{t('plan_yearly')}</h2>
							{currentPlan === 'yearly' && (
								<span
									className="bg-purple-100 text-purple-900 text-xs font-bold px-3 py-1 rounded-full uppercase">
                                    {t('status_active')}
                                </span>
							)}
						</div>

						<div className="my-4">
							<div className="flex items-baseline gap-1">
								<span
									className="text-5xl font-extrabold text-gray-900 tracking-tight">{yearlyPrice}</span>
								<span className="text-gray-500 font-semibold">{t('per_year')}</span>
							</div>
							<p className="text-xs sm:text-sm font-semibold text-orange-600 mt-1">
								{t('equivalent_per_month', {amount: monthlyEquivalent})}
							</p>
						</div>

						<ul className="mt-6 space-y-3.5 text-sm sm:text-base text-gray-600">
							<li className="flex items-center gap-3">
								<svg className="w-5 h-5 text-orange-500 shrink-0" fill="none" stroke="currentColor"
								     viewBox="0 0 24 24">
									<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5"
									      d="M5 13l4 4L19 7"/>
								</svg>
								<span className="font-semibold text-gray-800">{t('feature_all_monthly')}</span>
							</li>
							<li className="flex items-center gap-3">
								<svg className="w-5 h-5 text-orange-500 shrink-0" fill="none" stroke="currentColor"
								     viewBox="0 0 24 24">
									<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5"
									      d="M5 13l4 4L19 7"/>
								</svg>
								<span>{t('free_months_badge', {count: 4})}</span>
							</li>
							<li className="flex items-center gap-3">
								<svg className="w-5 h-5 text-orange-500 shrink-0" fill="none" stroke="currentColor"
								     viewBox="0 0 24 24">
									<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5"
									      d="M5 13l4 4L19 7"/>
								</svg>
								<span>{t('feature_priority_support')}</span>
							</li>
						</ul>
					</div>

					<div className="mt-8 pt-4 border-t border-gray-100">
						{isSubscribed ? (
							currentPlan === 'yearly' ? (
								<button
									disabled
									className="w-full py-3.5 px-6 rounded-xl bg-gray-100 text-gray-400 font-bold text-center cursor-not-allowed"
								>
									{t('current_plan')}
								</button>
							) : (
								<button
									onClick={() => swapPlan('yearly')}
									disabled={loadingAction !== undefined}
									className="w-full py-3.5 px-6 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-center shadow-md transition-all cursor-pointer disabled:opacity-50"
								>
									{loadingAction === 'swap_yearly' ? t('loading') : t('change_plan')}
								</button>
							)
						) : (
							<button
								onClick={() => subscribe('yearly')}
								disabled={loadingAction !== undefined}
								className="w-full py-3.5 px-6 rounded-xl bg-orange-500 hover:bg-orange-600 active:scale-95 text-white font-bold text-center shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer border-0 outline-none disabled:opacity-50"
							>
								{loadingAction === 'checkout_yearly' ? t('loading') : t('get_pro_yearly')}
							</button>
						)}
					</div>
				</div>
			</div>
		</div>
	)
}

