import React from 'react'
import {Head, Link} from '@inertiajs/react'
import {route} from 'ziggy-js'
import {useTranslation} from '@/hooks/useTranslation'
import {Budget} from '@/types'

interface DashboardProps {
	budgets: Budget[]
}

export const Dashboard = ({budgets}: DashboardProps) => {
	const {t} = useTranslation()

	return (
		<>
			<Head title={t('dashboard')}/>
			<div className="min-h-[calc(100vh-64px)] sm:min-h-[calc(100vh-80px)] bg-slate-50/70 p-6 sm:p-10">
				<div className="max-w-7xl mx-auto">
					{/* Section Header: Manage Budgets + Create Budget CTA */}
					<div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
						<div>
							<h1 className="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
								{t('manage_budgets_title')}
							</h1>
							<p className="text-gray-500 text-base mt-1">
								{t('manage_budgets_subtitle')}
							</p>
						</div>

						<div>
							<a
								href={route('budgets.create')}
								className="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-3 rounded-xl shadow-md shadow-orange-500/20 hover:shadow-orange-500/35 transition-all duration-200 active:scale-95 text-base"
							>
								<svg className="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
								     viewBox="0 0 24 24"
								     strokeWidth="2.5" stroke="currentColor">
									<path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
								</svg>
								<span>{t('create_budget')}</span>
							</a>
						</div>
					</div>

					{/* Budget List Grid */}
					<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
						{budgets.length > 0 ? (
							budgets.map((budget) => (
								<div
									key={budget.id}
									className="group bg-white rounded-2xl p-6 border border-purple-900/10 hover:border-purple-900/25 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col justify-between"
								>
									<div>
										{/* Header: Name & Type Badge */}
										<div className="flex items-start justify-between gap-3 mb-3">
											<h2 className="text-xl font-bold text-gray-900 line-clamp-1 group-hover:text-purple-900 transition-colors">
												<Link href={route('budgets.show', budget.id)}
												      className="hover:underline">
													{budget.name}
												</Link>
											</h2>
											<span
												className={`inline-flex items-center shrink-0 rounded-lg px-2.5 py-1 text-xs font-semibold ${
													budget.type === 'goal'
														? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20'
														: 'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-700/20'
												}`}
											>
                                                {t(`type_${budget.type}`)}
                                            </span>
										</div>

										{/* Amount */}
										<div className="mb-4">
                                            <span
												className="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-0.5">
                                                {t('amount')}
                                            </span>
											<p className="text-3xl font-extrabold text-gray-900">{budget.formatted_amount}</p>
										</div>

										{/* Description (if present) */}
										{budget.description && (
											<div
												className="mb-4 bg-slate-50/80 rounded-xl px-3.5 py-3 border border-gray-100 min-h-19 flex items-center">
												<p className="text-xs sm:text-sm text-gray-600 line-clamp-3 leading-relaxed font-medium">
													{budget.description}
												</p>
											</div>
										)}
									</div>

									{/* Card Footer Actions */}
									<div
										className="pt-4 border-t border-gray-100 flex items-center justify-between mt-4">
										<Link
											href={route('budgets.show', budget.id)}
											className="inline-flex items-center gap-1.5 text-sm font-bold text-purple-900 hover:text-purple-700 transition-colors group/link"
										>
											<span>{t('show')}</span>
											<svg className="w-4 h-4 transition-transform group-hover/link:translate-x-1"
											     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
											     strokeWidth="2"
											     stroke="currentColor">
												<path strokeLinecap="round" strokeLinejoin="round"
												      d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
											</svg>
										</Link>
									</div>
								</div>
							))
						) : (
							<div
								className="col-span-full bg-white rounded-2xl p-8 sm:p-10 text-center border border-purple-900/10 shadow-sm">
								<h3 className="text-lg font-bold text-gray-900 mb-1">{t('no_budgets')}</h3>
								<p className="text-gray-500 text-sm max-w-md mx-auto">{t('no_budgets_subtitle')}</p>
							</div>
						)}
					</div>
				</div>
			</div>
		</>
	)
}
