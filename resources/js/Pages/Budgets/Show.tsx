import React, {useState} from 'react'
import {Head, router, usePage} from '@inertiajs/react'
import {Budget, SharedData} from '@/types'
import {useTranslation} from '@/hooks/useTranslation'
import {ConfirmDeleteModal} from '@/Components/ConfirmDeleteModal'

interface ShowProps {
	budget: Budget
}

export const Show = ({budget}: ShowProps) => {
	const {flash} = usePage<SharedData>().props
	const {t} = useTranslation()
	const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false)
	const [isDeleting, setIsDeleting] = useState(false)

	const isGoal = budget.type === 'goal'
	const typeLabel = t(`type_${budget.type}`)
	const typeHelp = t(`type_help_${budget.type}`)

	const handleDelete = () => {
		setIsDeleting(true)
		router.delete(`/budgets/${budget.id}`, {
			onFinish: () => {
				setIsDeleting(false)
				setIsDeleteModalOpen(false)
			},
		})
	}

	return (
		<>
			<Head title={`${t('budgets')}: ${budget.name}`}/>
			<div className="py-10 bg-slate-50/70 min-h-[calc(100vh-80px)]">
				<div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

					{/* Status Flash Message */}
					{flash?.status && (
						<div
							className={`p-4 rounded-xl text-sm font-medium border ${
								flash.status_type === 'error'
									? 'bg-rose-50 border-rose-200 text-rose-800'
									: 'bg-emerald-50 border-emerald-200 text-emerald-800'
							}`}
						>
							{flash.status}
						</div>
					)}

					{/* Back Button */}
					<div>
						<a
							href="/dashboard"
							className="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 font-semibold text-xs uppercase tracking-wider shadow-xs transition-all duration-200 active:scale-95 group"
						>
							<svg
								className="w-4 h-4 text-purple-900 transition-transform group-hover:-translate-x-1"
								xmlns="http://www.w3.org/2000/svg"
								fill="none"
								viewBox="0 0 24 24"
								strokeWidth="2.5"
								stroke="currentColor"
							>
								<path
									strokeLinecap="round"
									strokeLinejoin="round"
									d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"
								/>
							</svg>
							<span>{t('back_to_list')}</span>
						</a>
					</div>

					{/* Detail Card */}
					<div className="bg-white border border-purple-900/10 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">

						{/* Header: Budget Name & Type Badge */}
						<div
							className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
							<div>
								<h1 className="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
									{budget.name}
								</h1>
							</div>

							<div>
								<span
									className={`inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-semibold ${
										isGoal
											? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20'
											: 'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-700/20'
									}`}
								>
									{typeLabel}
								</span>
							</div>
						</div>

						{/* Stats Grid: Amount & Type Details */}
						<div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
							{/* Amount Box */}
							<div className="bg-gray-50/50 border border-gray-100 rounded-xl p-5">
								<span
									className="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">
									{t('amount')}
								</span>
								<p className="text-3xl font-extrabold text-gray-900">
									{budget.formatted_amount || budget.amount}
								</p>
							</div>

							{/* Type Info Box */}
							<div className="bg-gray-50/50 border border-gray-100 rounded-xl p-5">
								<span
									className="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">
									{t('type')}
								</span>
								<p className="text-lg font-bold text-gray-900">{typeLabel}</p>
								<p className="text-xs text-gray-500 mt-1">{typeHelp}</p>
							</div>
						</div>

						{/* Description */}
						<div>
							<span className="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1.5">
								{t('description')}
							</span>
							<div
								className="bg-slate-50/80 rounded-xl p-4 border border-gray-100 text-gray-700 text-sm leading-relaxed">
								{budget.description ? (
									budget.description
								) : (
									<span className="text-gray-400 italic">
										{t('no_description')}
									</span>
								)}
							</div>
						</div>

						{/* Action Buttons (Edit & Delete) */}
						<div
							className="pt-6 flex flex-col sm:flex-row items-center justify-end gap-3 border-t border-gray-100">
							<a
								href={`/budgets/${budget.id}/edit`}
								className="w-full sm:w-auto bg-[#1b0e35] hover:bg-[#28154e] text-white font-semibold px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 cursor-pointer flex items-center justify-center gap-2 active:scale-95"
							>
								<svg
									className="w-4 h-4 shrink-0"
									xmlns="http://www.w3.org/2000/svg"
									fill="none"
									viewBox="0 0 24 24"
									strokeWidth="2"
									stroke="currentColor"
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
								onClick={() => setIsDeleteModalOpen(true)}
								className="w-full sm:w-auto bg-rose-50 hover:bg-rose-100 text-rose-700 font-medium px-5 py-2.5 rounded-xl border border-rose-200 transition-all duration-200 cursor-pointer flex items-center justify-center gap-2 active:scale-95"
							>
								<svg
									className="w-4 h-4 shrink-0"
									xmlns="http://www.w3.org/2000/svg"
									fill="none"
									viewBox="0 0 24 24"
									strokeWidth="2"
									stroke="currentColor"
								>
									<path
										strokeLinecap="round"
										strokeLinejoin="round"
										d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"
									/>
								</svg>
								<span>{t('delete')}</span>
							</button>
						</div>

					</div>

					{/* Confirm Delete Modal */}
					<ConfirmDeleteModal
						isOpen={isDeleteModalOpen}
						title={t('confirm_delete_budget_title', {name: budget.name})}
						message={t('confirm_delete_message')}
						confirmText={t('delete')}
						cancelText={t('cancel')}
						processingText={t('deleting')}
						isProcessing={isDeleting}
						onClose={() => setIsDeleteModalOpen(false)}
						onConfirm={handleDelete}
					/>

				</div>
			</div>
		</>
	)
}

