import React, {useEffect, useState} from 'react'
import {Head, router, usePage} from '@inertiajs/react'
import {route} from 'ziggy-js'
import {Budget, Expense, SharedData} from '@/types'
import {useTranslation} from '@/hooks/useTranslation'
import {formatDate} from '@/utils/formatDate'
import {formatCurrency} from '@/utils/formatCurrency'
import {ConfirmDeleteModal} from '@/Components/ConfirmDeleteModal'
import {ExpenseModal} from '@/Components/ExpenseModal'
import {ProgressBar} from '@/Components/ProgressBar'
import {useExpenseModalStore} from '@/store/expense-modal-store'
import {Category} from "@/types/Category"
import {getCategoryMeta} from '@/constants/category-config'

interface ShowProps {
	budget: Budget,
	categories: Category[]
}

export const Show = ({budget, categories}: ShowProps) => {
	const {auth} = usePage<SharedData>().props
	const {t, locale} = useTranslation()
	const {openModal, setBudget, setCategories} = useExpenseModalStore()

	useEffect(() => {
		setBudget(budget)
		setCategories(categories)
	}, [budget, categories, setBudget, setCategories])

	const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false)
	const [isDeleting, setIsDeleting] = useState(false)
	const [expenseToDelete, setExpenseToDelete] = useState<Expense | null>(null)
	const [isDeletingExpense, setIsDeletingExpense] = useState(false)

	const isGoal = budget.type === 'goal'
	const typeLabel = t(`type_${budget.type}`)
	const currencySymbol = budget.currency_symbol || auth?.user?.currency_symbol || '€'

	const formatCurrencyVal = (val: number | string) => formatCurrency(val, currencySymbol, locale)

	const totalSpent = (budget.expenses || []).reduce(
		(sum, exp) => sum + parseFloat(exp.amount || '0'),
		0
	)
	const budgetAmount = parseFloat(budget.amount || '0')
	const remaining = budgetAmount - totalSpent
	const percentSpent = budgetAmount > 0 ? Math.min(100, Math.max(0, (totalSpent / budgetAmount) * 100)) : 0

	const handleDeleteBudget = () => {
		setIsDeleting(true)
		router.delete(route('budgets.destroy', budget.id), {
				onFinish: () => {
					setIsDeleting(false)
					setIsDeleteModalOpen(false)
				},
				preserveScroll: true
			},
		)
	}

	const handleConfirmDeleteExpense = () => {
		if (!expenseToDelete) return
		setIsDeletingExpense(true)
		router.delete(route('expenses.destroy', expenseToDelete.id), {
			onFinish: () => {
				setIsDeletingExpense(false)
				setExpenseToDelete(null)
			},
			preserveScroll: true
		})
	}

	return (
		<>
			<Head title={`${t('budgets')}: ${budget.name}`}/>
			<div className="py-8 sm:py-12 bg-slate-50/70 min-h-[calc(100vh-80px)]">
				<div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

					{/* Navigation / Back Action Bar */}
					<div className="flex items-center justify-between">
						<a
							href={route('dashboard')}
							onClick={(e) => {
								if (e.currentTarget.getAttribute('data-clicked')) {
									e.preventDefault();
									return;
								}
								e.currentTarget.setAttribute('data-clicked', 'true');
								e.currentTarget.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none');
							}}
							className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-bold text-xs uppercase tracking-wider shadow-2xs transition-all duration-200 active:scale-95 group"
						>
							<svg
								className="w-4 h-4 text-purple-900 transition-transform group-hover:-translate-x-1"
								fill="none"
								viewBox="0 0 24 24"
								strokeWidth="2.5"
								stroke="currentColor"
							>
								<path strokeLinecap="round" strokeLinejoin="round"
								      d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
							</svg>
							<span>{t('back_to_list')}</span>
						</a>

						<span
							className={`inline-flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs font-extrabold uppercase tracking-wider ${
								isGoal
									? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20'
									: 'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-700/20'
							}`}
						>
							<span className={`w-2 h-2 rounded-full ${isGoal ? 'bg-emerald-500' : 'bg-purple-600'}`}/>
							{typeLabel}
						</span>
					</div>

					{/* Main Header Banner */}
					<div
						className="bg-white border border-purple-900/10 rounded-3xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
						<div className="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
							<div>
								<h1 className="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">
									{budget.name}
								</h1>
								<p className="text-xs sm:text-sm text-gray-500 font-medium mt-1">
									{budget.description ? budget.description : 'Maneja tu Presupuesto, añade, quita o edita tus gastos aquí.'}
								</p>
							</div>

							<div className="flex items-center gap-3">
								<a
									href={route('budgets.edit', budget.id)}
									onClick={(e) => {
										if (e.currentTarget.getAttribute('data-clicked')) {
											e.preventDefault();
											return;
										}
										e.currentTarget.setAttribute('data-clicked', 'true');
										e.currentTarget.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none');
									}}
									className="px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-slate-50 text-gray-800 font-extrabold text-xs sm:text-sm shadow-xs hover:border-gray-300 transition-all duration-200 active:scale-95 inline-flex items-center gap-2 shrink-0 cursor-pointer"
								>
									<svg
										className="w-4 h-4 text-purple-900 shrink-0"
										fill="none"
										viewBox="0 0 24 24"
										stroke="currentColor"
										strokeWidth="2.2"
									>
										<path
											strokeLinecap="round"
											strokeLinejoin="round"
											d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"
										/>
									</svg>
									<span>{t('edit')}</span>
								</a>

								<button
									type="button"
									onClick={() => openModal(null)}
									className="bg-[#1b0e35] hover:bg-[#28154e] text-white font-bold px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 cursor-pointer active:scale-95 text-xs sm:text-sm inline-flex items-center gap-2 shrink-0"
								>
									<svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
									     strokeWidth="2.5">
										<path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
									</svg>
									<span>Nuevo Gasto</span>
								</button>
							</div>
						</div>

						{/* Budget Summary Section: Circular Chart (Left) & Metrics (Right) */}
						<div className="mt-8 pt-8 border-t border-gray-100">
							<div className="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
								{/* Left Column: Circular Progress Graphic */}
								<div className="flex flex-col items-center justify-center p-4">
									<ProgressBar percentage={percentSpent} label={t('spent')}/>
								</div>

								{/* Right Column: 3 Metric Blocks (Presupuesto, Gastado, Restante) */}
								<div className="space-y-6 flex flex-col justify-center pl-0 md:pl-6">
									<div className="flex items-baseline gap-2">
										<span className="text-base sm:text-lg font-extrabold text-gray-900">
											{t('amount')}:
										</span>
										<span className="text-xl sm:text-2xl font-black text-[#ea580c]">
											{budget.formatted_amount || formatCurrencyVal(budgetAmount)}
										</span>
									</div>

									<div className="flex items-baseline gap-2">
										<span className="text-base sm:text-lg font-extrabold text-gray-900">
											{t('landing_demo_expenses')}:
										</span>
										<span className="text-xl sm:text-2xl font-black text-[#ea580c]">
											{formatCurrencyVal(totalSpent)}
										</span>
									</div>

									<div className="flex items-baseline gap-2">
										<span className="text-base sm:text-lg font-extrabold text-gray-900">
											{t('balance')}:
										</span>
										<span className="text-xl sm:text-2xl font-black text-[#ea580c]">
											{formatCurrencyVal(remaining)}
										</span>
									</div>
								</div>
							</div>
						</div>
					</div>

					{/* Expenses Section Card */}
					<div className="bg-white border border-purple-900/10 rounded-3xl p-6 sm:p-8 shadow-2xs space-y-6">
						<div className="flex items-center justify-between">
							<div>
								<h2 className="text-xl sm:text-2xl font-black text-gray-900">Gastos Registrados</h2>
								<p className="text-xs text-gray-500 mt-0.5">
									{budget.expenses?.length || 0} movimiento(s) en este presupuesto
								</p>
							</div>
						</div>

						{/* Expenses list or empty state */}
						{budget.expenses && budget.expenses.length > 0 ? (
							<div
								className="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50/40">
								{budget.expenses.map((expense) => {
									const catMeta = getCategoryMeta(expense.category)
									return (
										<div
											key={expense.id}
											className="p-4 flex items-center justify-between border-b border-slate-200 hover:bg-white transition-all duration-200 group"
										>
											<div className="flex items-center gap-3.5">
												<div
													className={`w-11 h-11 rounded-2xl ${catMeta.iconBg} flex items-center justify-center text-lg shrink-0 border shadow-2xs transition-transform group-hover:scale-105`}
												>
													{catMeta.icon}
												</div>
												<div>
													<span
														className="font-bold text-sm text-gray-900 block group-hover:text-purple-950 transition-colors">
														{expense.name}
													</span>
													<div className="flex flex-wrap items-center gap-2 mt-1">
														{expense.category && (
															<span
																className={`inline-flex items-center text-[11px] font-bold px-2.5 py-0.5 rounded-md border ${catMeta.badge}`}
															>
																{t(`category_${expense.category}`) !== `category_${expense.category}`
																	? t(`category_${expense.category}`)
																	: expense.category}
															</span>
														)}
														{expense.created_at && (
															<span className="text-[11px] font-medium text-gray-400">
																{t('added_on')}: {formatDate(expense.created_at, locale)}
															</span>
														)}
													</div>
												</div>
											</div>

											<div className="flex items-center gap-4">
											<span className="font-black text-base text-gray-900">
												{formatCurrencyVal(expense.amount)}
											</span>
												<div className="flex items-center gap-1">
													<button
														type="button"
														onClick={() => openModal(expense)}
														className="text-gray-400 hover:text-purple-900 p-1.5 rounded-lg hover:bg-purple-50 transition-colors cursor-pointer"
														title={t('edit')}
													>
														<svg className="w-4 h-4" fill="none" viewBox="0 0 24 24"
														     stroke="currentColor" strokeWidth="2">
															<path strokeLinecap="round" strokeLinejoin="round"
															      d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
														</svg>
													</button>

													<button
														type="button"
														onClick={() => setExpenseToDelete(expense)}
														className="text-gray-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition-colors cursor-pointer"
														title={t('delete')}
													>
														<svg className="w-4 h-4" fill="none" viewBox="0 0 24 24"
														     stroke="currentColor" strokeWidth="2">
															<path strokeLinecap="round" strokeLinejoin="round"
															      d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
														</svg>
													</button>
												</div>
											</div>
										</div>
									)
								})}
							</div>
						) : (
							<div
								className="py-10 text-center bg-slate-50/50 rounded-2xl border border-dashed border-gray-200">
								<p className="text-sm font-semibold text-gray-500">{t('no_expenses')}</p>
							</div>
						)}

						{/* Elegant Solid Delete Budget Button */}
						<div className="pt-6 border-t border-gray-100 flex justify-end">
							<button
								type="button"
								onClick={() => setIsDeleteModalOpen(true)}
								className="bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold px-5 py-2.5 rounded-xl border border-rose-200/80 transition-all duration-200 cursor-pointer inline-flex items-center gap-2 text-xs shadow-2xs active:scale-95"
							>
								<svg className="w-4 h-4 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24"
								     stroke="currentColor" strokeWidth="2">
									<path strokeLinecap="round" strokeLinejoin="round"
									      d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
								</svg>
								<span>{t('confirm_delete_title')}</span>
							</button>
						</div>
					</div>

					{/* Confirm Delete Budget Modal */}
					<ConfirmDeleteModal
						isOpen={isDeleteModalOpen}
						title={t('confirm_delete_budget_title', {name: budget.name})}
						message={t('confirm_delete_message')}
						confirmText={t('delete')}
						cancelText={t('cancel')}
						processingText={t('deleting')}
						isProcessing={isDeleting}
						onClose={() => setIsDeleteModalOpen(false)}
						onConfirm={handleDeleteBudget}
					/>

					{/* Confirm Delete Expense Modal */}
					<ConfirmDeleteModal
						isOpen={!!expenseToDelete}
						title={t('confirm_delete_expense_title', {name: expenseToDelete?.name || ''})}
						message={t('confirm_delete_message')}
						confirmText={t('delete')}
						cancelText={t('cancel')}
						processingText={t('deleting')}
						isProcessing={isDeletingExpense}
						onClose={() => setExpenseToDelete(null)}
						onConfirm={handleConfirmDeleteExpense}
					/>

					{/* Expense Modal connected via Zustand store */}
					<ExpenseModal/>
				</div>
			</div>
		</>
	)
}
