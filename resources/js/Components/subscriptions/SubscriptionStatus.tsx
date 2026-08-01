import React, {useEffect, useState} from 'react'
import {usePage} from '@inertiajs/react'
import {useTranslation} from '@/hooks/useTranslation'
import {formatDate} from '@/utils/formatDate'
import {SharedData, Subscription} from '@/types'
import {SubscriptionUpgrade} from './SubscriptionUpgrade'
import {SubscriptionDowngrade} from './SubscriptionDowngrade'
import {ConfirmModal} from '@/Components/ConfirmModal'

export interface SubscriptionStatusProps {
	subscription?: Subscription | null
	loadingAction?: string
	onCancel?: () => void
	onResume?: () => void
	onSwap?: (plan: 'monthly' | 'yearly') => void
}

export const SubscriptionStatus: React.FC<SubscriptionStatusProps> = ({
																		  subscription,
																		  loadingAction,
																		  onCancel,
																		  onResume,
																		  onSwap,
																	  }) => {
	const {auth} = usePage<SharedData>().props
	const {t, locale} = useTranslation()
	const [isCancelModalOpen, setIsCancelModalOpen] = useState(false)
	const [isSwapModalOpen, setIsSwapModalOpen] = useState(false)

	const currentPlan = auth?.user?.plan || subscription?.plan
	const symbol = auth?.user?.currency_symbol || '€'
	const isUSD = auth?.user?.currency === 'USD'

	const monthlyPrice = isUSD ? '$39' : `39${symbol}`
	const yearlyPrice = isUSD ? '$299' : `299${symbol}`

	const isOnGracePeriod = subscription?.on_grace_period ?? auth?.user?.on_grace_period ?? false
	const statusLabel = subscription?.status_label
	const rawDate = statusLabel?.date || subscription?.next_billing_date || subscription?.ends_at || auth?.user?.ends_at
	const formattedDate = rawDate ? formatDate(rawDate, locale) : ''

	useEffect(() => {
		if (isOnGracePeriod) {
			setIsCancelModalOpen(false)
		}
	}, [isOnGracePeriod])

	useEffect(() => {
		if (currentPlan === 'yearly') {
			setIsSwapModalOpen(false)
		}
	}, [currentPlan])

	const handleRequestSwap = () => {
		if (onSwap && loadingAction === undefined) {
			setIsSwapModalOpen(true)
		}
	}

	const handleConfirmSwap = () => {
		setIsSwapModalOpen(false)
		if (onSwap) {
			onSwap('yearly')
		}
	}

	const getDotColorClass = () => {
		switch (statusLabel?.color) {
			case 'orange':
				return 'bg-amber-500 animate-ping'
			case 'red':
				return 'bg-rose-500 animate-pulse'
			case 'gray':
				return 'bg-gray-400'
			case 'green':
			default:
				return isOnGracePeriod ? 'bg-amber-500 animate-ping' : 'bg-emerald-500 animate-pulse'
		}
	}

	const statusTitle = statusLabel?.text || (isOnGracePeriod ? t('subscription_canceled') : t('subscription_active'))
	const statusDescription = statusLabel?.description
		? `${statusLabel.description} ${formattedDate}`.trim()
		: formattedDate
			? (isOnGracePeriod ? t('ends_on', {date: formattedDate}) : t('renews_on', {date: formattedDate}))
			: `Plan ${currentPlan === 'yearly' ? t('plan_yearly') : t('plan_monthly')}`

	return (
		<div className="space-y-6">
			{/* Main Subscription Info Card */}
			<div className="bg-white rounded-2xl p-6 sm:p-8 border border-purple-900/15 shadow-sm">
				<div
					className="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-6 border-b border-gray-100">
					<div>
						<div className="flex items-center gap-3 mb-2">
							<span className={`w-3 h-3 rounded-full ${getDotColorClass()}`}/>
							<h2 className="text-2xl font-extrabold text-gray-900">
								{statusTitle}
							</h2>
						</div>

						<p className="text-sm text-gray-600 font-medium">
							{statusDescription}
						</p>
					</div>

					<div className="flex items-center gap-3 shrink-0">
						{isOnGracePeriod ? (
							onResume && (
								<button
									onClick={onResume}
									disabled={loadingAction !== undefined}
									className="px-6 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm shadow-md shadow-orange-500/20 active:scale-95 transition-all cursor-pointer disabled:opacity-50"
								>
									{loadingAction === 'resume' ? t('loading') : t('resume_subscription')}
								</button>
							)
						) : (
							onCancel && (
								<button
									onClick={() => setIsCancelModalOpen(true)}
									disabled={loadingAction !== undefined}
									className="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-red-50 text-gray-700 hover:text-red-600 font-semibold text-sm transition-all border border-gray-200 cursor-pointer disabled:opacity-50"
								>
									{loadingAction === 'cancel' ? t('loading') : t('cancel_subscription')}
								</button>
							)
						)}
					</div>
				</div>

				{/* Plan Details Grid */}
				<div className="pt-6">
					<h3 className="text-lg font-bold text-purple-900 mb-4">{t('current_plan')}</h3>

					<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
						{/* Monthly Option */}
						<div
							className={`rounded-xl p-5 border transition-all ${
								currentPlan === 'monthly'
									? 'border-2 border-purple-900 bg-purple-900/5'
									: 'border-gray-200 bg-slate-50/50'
							}`}
						>
							<div className="flex justify-between items-center mb-2">
								<h4 className="font-bold text-gray-900">{t('plan_monthly')}</h4>
								{currentPlan === 'monthly' && (
									<span
										className="bg-purple-900 text-white text-xs font-extrabold px-2.5 py-0.5 rounded-full uppercase">
                                        {t('status_active')}
                                    </span>
								)}
							</div>
							<p className="text-2xl font-extrabold text-gray-900">
								{monthlyPrice}
								<span className="text-xs text-gray-500 font-normal ml-1">
                                    {t('per_month')}
                                </span>
							</p>
						</div>

						{/* Yearly Option */}
						<div
							onClick={() => {
								if (currentPlan === 'monthly') {
									handleRequestSwap()
								}
							}}
							className={`rounded-xl p-5 border transition-all ${
								currentPlan === 'yearly'
									? 'border-2 border-purple-900 bg-purple-900/5'
									: currentPlan === 'monthly'
										? 'border-gray-200 bg-slate-50/50 hover:border-orange-500 hover:bg-orange-50/30 cursor-pointer shadow-2xs hover:shadow-md'
										: 'border-gray-200 bg-slate-50/50'
							}`}
						>
							<div className="flex justify-between items-center mb-2">
								<h4 className="font-bold text-gray-900">{t('plan_yearly')}</h4>
								{currentPlan === 'yearly' ? (
									<span
										className="bg-purple-900 text-white text-xs font-extrabold px-2.5 py-0.5 rounded-full uppercase">
                                        {t('status_active')}
                                    </span>
								) : (
									currentPlan === 'monthly' && (
										<span
											className="bg-orange-500/10 text-orange-600 border border-orange-500/20 text-xs font-extrabold px-2.5 py-0.5 rounded-full uppercase">
											{loadingAction === 'swap_yearly' ? t('loading') : t('upgrade_yearly_btn')}
										</span>
									)
								)}
							</div>
							<p className="text-2xl font-extrabold text-gray-900">
								{yearlyPrice}
								<span className="text-xs text-gray-500 font-normal ml-1">
                                    {t('per_year')}
                                </span>
							</p>
						</div>
					</div>
				</div>
			</div>

			{/* Upgrade or Downgrade Information Section */}
			{currentPlan === 'monthly' && (
				<SubscriptionUpgrade onSwap={handleRequestSwap} loadingAction={loadingAction}/>
			)}

			{currentPlan === 'yearly' && (
				<SubscriptionDowngrade endsAt={formattedDate}/>
			)}

			{/* Security & Stripe Info Box */}
			<div className="bg-white rounded-2xl p-6 border border-gray-200/80 shadow-xs flex items-center gap-4">
				<div
					className="w-10 h-10 rounded-full bg-purple-100 text-purple-900 flex items-center justify-center shrink-0">
					<svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path
							strokeLinecap="round"
							strokeLinejoin="round"
							strokeWidth="2"
							d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
						/>
					</svg>
				</div>
				<div>
					<h4 className="text-sm font-bold text-gray-900">{t('billing_info')}</h4>
					<p className="text-xs text-gray-500 mt-0.5">{t('secure_stripe_notice')}</p>
				</div>
			</div>

			{/* Confirmation Modal for Subscription Cancellation */}
			<ConfirmModal
				isOpen={isCancelModalOpen}
				title={t('confirm_cancel_subscription_title')}
				message={t('confirm_cancel_subscription_message')}
				confirmText={t('confirm_cancel_subscription_btn')}
				cancelText={t('cancel')}
				processingText={t('canceling_subscription')}
				isProcessing={loadingAction === 'cancel'}
				variant="danger"
				onClose={() => setIsCancelModalOpen(false)}
				onConfirm={() => {
					setIsCancelModalOpen(false)
					if (onCancel) {
						onCancel()
					}
				}}
			/>

			{/* Confirmation Modal for Subscription Swap to Yearly */}
			<ConfirmModal
				isOpen={isSwapModalOpen}
				title={t('confirm_swap_yearly_title')}
				message={t('confirm_swap_yearly_message', {price: yearlyPrice})}
				confirmText={t('confirm_swap_yearly_btn')}
				cancelText={t('cancel')}
				processingText={t('swapping_plan')}
				isProcessing={loadingAction === 'swap_yearly'}
				variant="warning"
				onClose={() => setIsSwapModalOpen(false)}
				onConfirm={handleConfirmSwap}
			/>
		</div>
	)
}


