import React, {useEffect, useState} from 'react'
import {Head, router, usePage} from '@inertiajs/react'
import {route} from 'ziggy-js'
import {Budget, Expense, SharedData} from '@/types'
import {useTranslation} from '@/hooks/useTranslation'
import {ConfirmDeleteModal} from '@/Components/ConfirmDeleteModal'
import {ExpenseModal} from '@/Components/ExpenseModal'
import {useExpenseModalStore} from '@/store/expense-modal-store'
import {Category} from "@/types/Category"
import {getCategoryMeta} from '@/constants/category-config'

interface ShowProps {
	budget: Budget,
	categories: Category[]
}

export const Show = ({budget, categories}: ShowProps) => {
	const {flash, auth} = usePage<SharedData>().props
	const {t} = useTranslation()
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

	const formatCurrency = (val: number | string) => {
		const amountNum = typeof val === 'string' ? parseFloat(val || '0') : val
		return `${amountNum.toFixed(2)} ${currencySymbol}`
	}

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
		})
	}

	const handleConfirmDeleteExpense = () => {
		if (!expenseToDelete) return
		setIsDeletingExpense(true)
		router.delete(route('expenses.destroy', expenseToDelete.id), {
			onFinish: () => {
				setIsDeletingExpense(false)
				setExpenseToDelete(null)
			},
		})
	}

	return (
		<>
			<Head title={`${t('budgets')}: ${budget.name}`}/>
			<div className="py-8 sm:py-12 bg-slate-50/70 min-h-[calc(100vh-80px)]">
				<div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

					{/* Status Flash Message */}
					{flash?.status && (
						<div
							className={`p-4 rounded-2xl text-sm font-medium border shadow-xs transition-all ${
								flash.status_type === 'error'
									? 'bg-rose-50 border-rose-200 text-rose-800'
									: 'bg-emerald-50 border-emerald-200 text-emerald-800'
							}`}
						>
							{flash.status}
						</div>
					)}

					{/* Navigation / Back Action Bar */}
					<div className="flex items-center justify-between">
						<a
							href={route('dashboard')}
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

						{/* Budget Usage Progress Bar */}
						<div className="mt-6 pt-6 border-t border-gray-100">
							<div className="flex items-center justify-between text-xs font-bold mb-2">
								<span className="text-gray-500 uppercase tracking-wider">Uso del presupuesto</span>
								<span className={percentSpent > 90 ? 'text-rose-600' : 'text-purple-900'}>
									{percentSpent.toFixed(0)}% consumido
								</span>
							</div>
							<div
								className="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden p-0.5 border border-slate-200/60">
								<div
									className={`h-full rounded-full transition-all duration-500 ${
										percentSpent > 100
											? 'bg-rose-500'
											: percentSpent > 80
												? 'bg-amber-500'
												: 'bg-linear-to-r from-purple-900 to-indigo-600'
									}`}
									style={{width: `${Math.min(100, percentSpent)}%`}}
								/>
							</div>
						</div>
					</div>

					{/* 3 Metric Cards Grid */}
					<div className="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
						{/* Card 1: Presupuesto Total */}
						<div
							className="bg-white border border-purple-900/10 rounded-2xl p-5 shadow-2xs relative overflow-hidden group hover:border-purple-900/25 transition-all">
							<span className="text-xs font-extrabold uppercase tracking-wider text-gray-400 block mb-1">
								{t('amount')}
							</span>
							<p className="text-2xl sm:text-3xl font-black text-gray-900">
								{budget.formatted_amount || formatCurrency(budgetAmount)}
							</p>
						</div>

						{/* Card 2: Gastado */}
						<div
							className="bg-white border border-purple-900/10 rounded-2xl p-5 shadow-2xs relative overflow-hidden group hover:border-purple-900/25 transition-all">
							<span className="text-xs font-extrabold uppercase tracking-wider text-gray-400 block mb-1">
								{t('landing_demo_expenses')}
							</span>
							<p className="text-2xl sm:text-3xl font-black text-rose-600">
								{formatCurrency(totalSpent)}
							</p>
						</div>

						{/* Card 3: Restante */}
						<div
							className="bg-white border border-purple-900/10 rounded-2xl p-5 shadow-2xs relative overflow-hidden group hover:border-purple-900/25 transition-all">
							<span className="text-xs font-extrabold uppercase tracking-wider text-gray-400 block mb-1">
								{t('balance')}
							</span>
							<p className={`text-2xl sm:text-3xl font-black ${remaining < 0 ? 'text-rose-600' : 'text-emerald-600'}`}>
								{formatCurrency(remaining)}
							</p>
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
							<div className="divide-y divide-gray-100 border rounded-2xl overflow-hidden bg-slate-50/40">
								{budget.expenses.map((expense) => {
									const catMeta = getCategoryMeta(expense.category)
									return (
										<div
											key={expense.id}
											className="p-4 flex items-center justify-between hover:bg-white transition-all duration-200 group"
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
													{expense.category && (
														<span
															className={`inline-flex items-center text-[11px] font-bold px-2.5 py-0.5 rounded-md border mt-1 ${catMeta.badge}`}
														>
															{t(`category_${expense.category}`) !== `category_${expense.category}`
																? t(`category_${expense.category}`)
																: expense.category}
														</span>
													)}
												</div>
											</div>

											<div className="flex items-center gap-4">
											<span className="font-black text-base text-gray-900">
												{formatCurrency(expense.amount)}
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
