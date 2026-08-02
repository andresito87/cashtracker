import React from 'react'
import {Dialog, DialogBackdrop, DialogPanel, DialogTitle} from '@headlessui/react'
import {useTranslation} from '@/hooks/useTranslation'
import {useBudgetModalStore} from '@/store/budget-modal-store'
import {BudgetForm} from '@/Components/BudgetForm'

export const BudgetModal = () => {
	const {t} = useTranslation()
	const {isOpen, closeModal, budget} = useBudgetModalStore()

	return (
		<Dialog open={isOpen} onClose={closeModal} className="relative z-50">
			<DialogBackdrop
				transition
				className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300 ease-out data-closed:opacity-0"
			/>

			<div className="fixed inset-0 z-10 w-screen overflow-y-auto">
				<div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
					<DialogPanel
						transition
						className="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all duration-300 ease-out data-closed:translate-y-4 data-closed:opacity-0 data-closed:scale-95 sm:my-8 sm:w-full sm:max-w-2xl p-6 sm:p-8"
					>
						{/* Header */}
						<div className="flex items-center justify-between pb-4 border-b border-gray-100">
							<div>
								<DialogTitle as="h3" className="text-xl font-extrabold text-gray-900">
									{t('edit_budget')}
								</DialogTitle>
								<p className="text-xs text-gray-500 mt-0.5">
									{budget?.name}
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

						{/* Form */}
						<div className="mt-6">
							<BudgetForm/>
						</div>
					</DialogPanel>
				</div>
			</div>
		</Dialog>
	)
}
