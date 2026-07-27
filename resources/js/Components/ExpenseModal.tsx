import React from 'react'
import {Dialog, DialogBackdrop, DialogPanel, DialogTitle} from '@headlessui/react'
import {usePage} from '@inertiajs/react'
import {SharedData} from '@/types'
import {useTranslation} from '@/hooks/useTranslation'
import {useExpenseModalStore} from '@/store/expense-modal-store'
import {ExpenseForm} from '@/Components/ExpenseForm'

export const ExpenseModal = () => {
	const {auth} = usePage<SharedData>().props
	const {t} = useTranslation()
	const {isOpen, closeModal, editingExpense, budget} = useExpenseModalStore()

	const currencySymbol = budget?.currency_symbol || auth?.user?.currency_symbol || '€'
	const formatCurrency = (val: number | string) => {
		const amountNum = typeof val === 'string' ? parseFloat(val || '0') : val
		return `${amountNum.toFixed(2)} ${currencySymbol}`
	}

	const totalSpent = (budget?.expenses || []).reduce(
		(sum, exp) => sum + parseFloat(exp.amount || '0'),
		0
	)
	const budgetAmount = parseFloat(budget?.amount || '0')
	const remaining = budgetAmount - totalSpent

	return (
		<Dialog open={isOpen} onClose={closeModal} className="relative z-50">
			<DialogBackdrop
				transition
				className="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity data-closed:opacity-0 data-enter:duration-300 data-leave:ease-in"
			/>

			<div className="fixed inset-0 z-10 w-screen overflow-y-auto">
				<div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
					<DialogPanel
						transition
						className="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all data-closed:translate-y-4 data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out sm:my-8 sm:w-full sm:max-w-2xl p-6 sm:p-8"
					>
						{/* Header */}
						<div className="flex items-center justify-between pb-4 border-b border-gray-100">
							<div>
								<DialogTitle as="h3" className="text-xl font-extrabold text-gray-900">
									{editingExpense ? t('edit_expense') : t('add_expense')}
								</DialogTitle>
								<p className="text-xs text-gray-500 mt-0.5">
									{budget?.name} ({t('amount')}: {budget?.formatted_amount || formatCurrency(budgetAmount)})
								</p>
							</div>

							<button
								type="button"
								onClick={closeModal}
								className="text-gray-400 hover:text-gray-600 rounded-lg p-1.5 transition-colors cursor-pointer"
							>
								<svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
								     strokeWidth="2">
									<path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12"/>
								</svg>
							</button>
						</div>

						{/* Spending summary bar */}
						<div
							className="my-5 p-4 rounded-xl bg-slate-50 border border-slate-100 flex flex-wrap items-center justify-between gap-3">
							<div>
								<span
									className="text-xs text-gray-500 font-medium block">{t('landing_demo_expenses')}</span>
								<span className="text-lg font-bold text-gray-900">{formatCurrency(totalSpent)}</span>
							</div>
							<div>
								<span className="text-xs text-gray-500 font-medium block">{t('balance')}</span>
								<span
									className={`text-lg font-bold ${remaining < 0 ? 'text-rose-600' : 'text-emerald-600'}`}>
									{formatCurrency(remaining)}
								</span>
							</div>
						</div>

						{/* Form to Add / Edit Expense */}
						<ExpenseForm/>
					</DialogPanel>
				</div>
			</div>
		</Dialog>
	)
}
