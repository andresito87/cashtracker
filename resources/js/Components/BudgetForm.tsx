import React, {useEffect} from 'react'
import {useForm} from '@inertiajs/react'
import {route} from 'ziggy-js'
import {useTranslation} from '@/hooks/useTranslation'
import {useBudgetModalStore} from '@/store/budget-modal-store'
import {InputError} from '@/Components/InputError'

export interface BudgetFormProps {
	onSuccess?: () => void
	onCancel?: () => void
}

export const BudgetForm = ({onSuccess, onCancel}: BudgetFormProps) => {
	const {t} = useTranslation()
	const {budget, closeModal} = useBudgetModalStore()

	const {data, setData, put, processing, errors, reset, clearErrors} = useForm({
		name: '',
		amount: '',
		type: 'general',
		description: '',
	})

	useEffect(() => {
		if (budget) {
			setData({
				name: budget.name,
				amount: budget.amount,
				type: budget.type,
				description: budget.description || '',
			})
			clearErrors()
		}
	}, [budget, setData, clearErrors])

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
		if (!budget?.id) return

		put(route('budgets.update', budget.id), {
			onSuccess: () => {
				reset()
				if (onSuccess) {
					onSuccess()
				} else {
					closeModal()
				}
			},
			preserveScroll: true
		})
	}

	return (
		<form noValidate onSubmit={handleSubmit} className="space-y-5">
			{/* Name */}
			<div>
				<label className="block text-sm font-semibold text-gray-800 mb-1.5">
					{t('name')} <span className="text-red-500">*</span>
				</label>
				<input
					type="text"
					value={data.name}
					onChange={(e) => setData('name', e.target.value)}
					placeholder={t('budget_name_placeholder')}
					className={`w-full px-4 py-2.5 rounded-xl border text-gray-900 bg-gray-50/40 focus:outline-none focus:ring-2 focus:ring-purple-900/20 transition-all ${
						errors.name ? 'border-rose-500 bg-rose-50/30' : 'border-gray-200'
					}`}
				/>
				<InputError message={errors.name} className="mt-1.5"/>
			</div>

			{/* Amount & Type Grid */}
			<div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
				{/* Amount */}
				<div>
					<label className="block text-sm font-semibold text-gray-800 mb-1.5">
						{t('amount')} <span className="text-red-500">*</span>
					</label>
					<div
						className={`flex rounded-xl border bg-gray-50/40 focus-within:ring-2 focus-within:ring-purple-900/20 transition-all overflow-hidden ${
							errors.amount ? 'border-rose-500' : 'border-gray-200'
						}`}>
						<input
							type="text"
							value={data.amount}
							onChange={(e) => setData('amount', e.target.value)}
							placeholder={t('budget_amount_placeholder')}
							className="w-full px-4 py-2.5 bg-transparent border-0 text-gray-900 focus:outline-none focus:ring-0"
						/>
						<span
							className="bg-gray-100/90 text-gray-800 text-sm font-bold border-l border-gray-200 px-4 py-2.5 flex items-center shrink-0">
							{budget?.currency_symbol || '€'}
						</span>
					</div>
					<InputError message={errors.amount} className="mt-1.5"/>
				</div>

				{/* Type */}
				<div>
					<label className="block text-sm font-semibold text-gray-800 mb-1.5">
						{t('type')} <span className="text-red-500">*</span>
					</label>
					<select
						value={data.type}
						onChange={(e) => setData('type', e.target.value as 'general' | 'goal')}
						className={`w-full px-4 py-2.5 rounded-xl border text-gray-900 bg-gray-50/40 focus:outline-none focus:ring-2 focus:ring-purple-900/20 transition-all cursor-pointer ${
							errors.type ? 'border-rose-500' : 'border-gray-200'
						}`}
					>
						<option value="general">{t('type_general')}</option>
						<option value="goal">{t('type_goal')}</option>
					</select>
					<InputError message={errors.type} className="mt-1.5"/>
				</div>
			</div>

			{/* Description */}
			<div>
				<label className="block text-sm font-semibold text-gray-800 mb-1.5">
					{t('description')}
				</label>
				<textarea
					value={data.description}
					onChange={(e) => setData('description', e.target.value)}
					rows={3}
					placeholder={t('budget_description_placeholder')}
					className={`w-full px-4 py-2.5 rounded-xl border text-gray-900 bg-gray-50/40 focus:outline-none focus:ring-2 focus:ring-purple-900/20 transition-all resize-y ${
						errors.description ? 'border-rose-500' : 'border-gray-200'
					}`}
				/>
				<InputError message={errors.description} className="mt-1.5"/>
			</div>

			{/* Action Buttons */}
			<div className="pt-4 flex flex-col sm:flex-row items-center justify-end gap-3 border-t border-gray-100">
				<button
					type="submit"
					disabled={processing}
					className="w-full sm:w-auto bg-[#1b0e35] hover:bg-[#28154e] text-white font-semibold px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 cursor-pointer flex items-center justify-center gap-2 active:scale-95 disabled:opacity-50"
				>
					{processing ? (
						<>
							<svg className="animate-spin w-4 h-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
								<circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
								        strokeWidth="4"/>
								<path className="opacity-75" fill="currentColor"
								      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
							</svg>
							<span>{t('updating_budget')}</span>
						</>
					) : (
						<>
							<svg className="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" strokeWidth="2.5"
							     stroke="currentColor">
								<path strokeLinecap="round" strokeLinejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
							</svg>
							<span>{t('update_budget')}</span>
						</>
					)}
				</button>

				<button
					type="button"
					onClick={handleCancel}
					disabled={processing}
					className="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-100 font-medium transition-all duration-200 text-center active:scale-95 disabled:opacity-50 cursor-pointer"
				>
					{t('cancel')}
				</button>
			</div>
		</form>
	)
}
