import React from 'react'
import {Head, Link, usePage} from '@inertiajs/react'
import {route} from 'ziggy-js'
import {useTranslation} from '@/hooks/useTranslation'
import {useSubscriptionActions} from '@/hooks/useSubscriptionActions'
import {SharedData, Subscription} from '@/types'
import {SubscriptionStatus} from '@/Components/subscriptions/SubscriptionStatus'
import {PricingTable} from '@/Components/PricingTable'

interface ManageProps {
	subscription?: Subscription | null
}

export const Manage: React.FC<ManageProps> = ({subscription}) => {
	const {auth} = usePage<SharedData>().props
	const {t} = useTranslation()
	const {
		loadingAction,
		swapPlan,
		cancelSubscription,
		resumeSubscription,
	} = useSubscriptionActions()

	const isSubscribed = auth?.user?.subscribed ?? false

	return (
		<>
			<Head title={`CashTracker - ${t('manage_subscription_title')}`}/>

			<div className="min-h-[calc(100vh-64px)] sm:min-h-[calc(100vh-80px)] bg-slate-50/70 p-6 sm:p-10">
				<div className="max-w-4xl mx-auto">
					{/* Header */}
					<div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
						<div>
							<div className="mb-2">
								<Link
									href={route('dashboard')}
									className="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-purple-900/80 hover:text-purple-900 transition-colors group"
								>
									<svg className="w-3.5 h-3.5 transition-transform group-hover:-translate-x-0.5"
									     fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2.5">
										<path strokeLinecap="round" strokeLinejoin="round"
										      d="M15.75 19.5L8.25 12l7.5-7.5"/>
									</svg>
									<span>{t('back_to_list')}</span>
								</Link>
							</div>
							<h1 className="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
								{t('manage_subscription_title')}
							</h1>
							<p className="text-gray-500 text-base mt-1">
								{t('manage_subscription_subtitle')}
							</p>
						</div>

						{isSubscribed && (
							<div className="shrink-0">
                                <span
									className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-500/10 border border-orange-500/20 text-orange-600 font-extrabold text-sm uppercase tracking-wider">
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5"
                                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <span>CashTracker PRO</span>
                                </span>
							</div>
						)}
					</div>

					{/* Subscription Content */}
					{isSubscribed ? (
						<SubscriptionStatus
							subscription={subscription}
							loadingAction={loadingAction}
							onCancel={cancelSubscription}
							onResume={resumeSubscription}
							onSwap={swapPlan}
						/>
					) : (
						<PricingTable subscription={subscription}/>
					)}
				</div>
			</div>
		</>
	)
}
