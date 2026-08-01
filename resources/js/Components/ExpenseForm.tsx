import React, {useEffect} from 'react'
import {useForm} from '@inertiajs/react'
import {route} from 'ziggy-js'
import {useTranslation} from '@/hooks/useTranslation'
import {useExpenseModalStore} from '@/store/expense-modal-store'
import {InputError} from '@/Components/InputError'
import {formatDate} from '@/utils/formatDate'

export interface ExpenseFormProps {
	onSuccess?: () => void
	onCancel?: () => void
}

export const ExpenseForm = ({onSuccess, onCancel}: ExpenseFormProps) => {
	const {t, locale} = useTranslation()
	const {budget, categories, closeModal, editingExpense} = useExpenseModalStore()

	const {data, setData, post, put, processing, errors, reset, clearErrors} = useForm({
		name: '',
		amount: '',
		category: 'other',
	})

	useEffect(() => {
		if (editingExpense) {
			setData({
				name: editingExpense.name,
				amount: editingExpense.amount,
				category: editingExpense.category || 'other',
			})
			clearErrors()
		} else {
			reset()
			clearErrors()
		}
		// setData, clearErrors, reset are stable refs from useForm — safe to include
	}, [editingExpense, setData, clearErrors, reset])

	const handleCancel = () => {
		reset()
		if (onCancel) {
			onCancel()
		} else {
			closeModal()
		}
	}

	const handleSubmit = (e: React.SubmitEvent<HTMLFormElement>) => {
		e.preventDefault()
		if (!editingExpense && !budget?.id) {
			return
		}

		const handleDone = () => {
			reset()
			if (onSuccess) {
				onSuccess()
			} else {
				closeModal()
			}
		}

		if (editingExpense) {
			put(route('expenses.update', editingExpense.id), {
				onSuccess: handleDone,
				preserveScroll: true
			})
		} else {
			post(route('budgets.expenses.store', budget!.id), {
				onSuccess: handleDone,
				preserveScroll: true
			})
		}
	}

	return (
		<form
			noValidate
			onSubmit={handleSubmit}
			className="p-4 rounded-xl border border-purple-900/10 bg-purple-50/30 space-y-4"
		>
			<div className="flex items-center justify-between">
				<h4 className="text-sm font-bold text-purple-950">
					{editingExpense ? t('edit_expense') : t('add_expense')}
				</h4>
			</div>

			<div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
				<div className="sm:col-span-1">
					<label className="block text-xs font-semibold text-gray-700 mb-1">{t('name')}</label>
					<input
						type="text"
						value={data.name}
						onChange={(e) => setData('name', e.target.value)}
						placeholder="ej. Compras"
						className={`w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-purple-900 focus:outline-none ${
							errors.name ? 'border-rose-500 bg-rose-50/30' : 'border-gray-300'
						}`}
					/>
					{editingExpense?.created_at && (
						<span className="text-[11px] font-medium text-gray-400 block mt-1">
							{t('added_on')}: {formatDate(editingExpense.created_at, locale)}
						</span>
					)}
					<InputError message={errors.name}/>
				</div>

				<div>
					<label className="block text-xs font-semibold text-gray-700 mb-1">{t('amount')}</label>
					<input
						type="text"
						value={data.amount}
						onChange={(e) => setData('amount', e.target.value)}
						placeholder="0.00"
						className={`w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-purple-900 focus:outline-none ${
							errors.amount ? 'border-rose-500 bg-rose-50/30' : 'border-gray-300'
						}`}
					/>
					<InputError message={errors.amount}/>
				</div>

				<div>
					<label className="block text-xs font-semibold text-gray-700 mb-1">{t('category')}</label>
					<select
						value={data.category}
						onChange={(e) => setData('category', e.target.value)}
						className={`w-full px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-purple-900 focus:outline-none bg-white cursor-pointer ${
							errors.category ? 'border-rose-500 bg-rose-50/30' : 'border-gray-300'
						}`}
					>
						{categories.map((cat) => {
							const key = `category_${cat.value}`
							const translated = t(key)
							const labelText = translated !== key ? translated : cat.label || cat.value
							return (
								<option key={cat.value} value={cat.value}>
									{labelText}
								</option>
							)
						})}
					</select>
					<InputError message={errors.category}/>
				</div>
			</div>

			<div className="flex justify-end gap-2 pt-2">
				<button
					type="submit"
					disabled={processing}
					className="bg-[#1b0e35] hover:bg-[#28154e] text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors cursor-pointer disabled:opacity-50"
				>
					{processing
						? t('saving_budget')
						: editingExpense
							? t('update_budget')
							: t('add_expense')}
				</button>
				<button
					type="button"
					onClick={handleCancel}
					className="border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold px-4 py-2 rounded-lg transition-colors cursor-pointer shadow-2xs"
				>
					{t('cancel')}
				</button>
			</div>
		</form>
	)
}
