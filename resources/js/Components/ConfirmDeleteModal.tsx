import React, {useRef} from 'react'
import {Dialog, DialogBackdrop, DialogPanel, DialogTitle} from '@headlessui/react'

interface ConfirmDeleteModalProps {
	isOpen: boolean
	title: string
	message: string
	confirmText?: string
	cancelText?: string
	processingText?: string
	isProcessing?: boolean
	onClose: () => void
	onConfirm: () => void
}

export const ConfirmDeleteModal = ({
									   isOpen,
									   title,
									   message,
									   confirmText = 'Eliminar',
									   cancelText = 'Cancelar',
									   processingText = 'Eliminando...',
									   isProcessing = false,
									   onClose,
									   onConfirm,
								   }: ConfirmDeleteModalProps) => {
	const cachedTitleRef = useRef(title)
	const cachedMessageRef = useRef(message)

	if (isOpen) {
		if (title) cachedTitleRef.current = title
		if (message) cachedMessageRef.current = message
	}

	const displayTitle = isOpen ? title : cachedTitleRef.current
	const displayMessage = isOpen ? message : cachedMessageRef.current

	const handleClose = () => {
		if (!isProcessing) {
			onClose()
		}
	}

	return (
		<Dialog open={isOpen} onClose={handleClose} className="relative z-50">
			<DialogBackdrop
				transition
				className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300 ease-out data-closed:opacity-0"
			/>

			<div className="fixed inset-0 z-10 w-screen overflow-y-auto">
				<div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
					<DialogPanel
						transition
						className="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all duration-300 ease-out data-closed:translate-y-4 data-closed:opacity-0 data-closed:scale-95 sm:my-8 sm:w-full sm:max-w-md p-6 sm:p-8 space-y-6"
					>
						<div className="flex items-start gap-4">
							<div className="p-3 bg-rose-100 text-rose-600 rounded-full shrink-0">
								<svg
									className="w-6 h-6"
									fill="none"
									viewBox="0 0 24 24"
									strokeWidth="2"
									stroke="currentColor"
								>
									<path
										strokeLinecap="round"
										strokeLinejoin="round"
										d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
									/>
								</svg>
							</div>
							<div>
								<DialogTitle as="h3" className="text-lg font-bold text-gray-900">
									{displayTitle}
								</DialogTitle>
								<p className="mt-2 text-sm text-gray-600 leading-relaxed">
									{displayMessage}
								</p>
							</div>
						</div>

						<div className="flex flex-row items-center justify-end gap-3 pt-4 border-t border-gray-100">
							<button
								type="button"
								onClick={onConfirm}
								disabled={isProcessing}
								className="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-sm shadow-md transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-75 cursor-pointer active:scale-95"
							>
								{isProcessing ? (
									<>
										<svg
											className="animate-spin h-4 w-4 text-white shrink-0"
											xmlns="http://www.w3.org/2000/svg"
											fill="none"
											viewBox="0 0 24 24"
										>
											<circle
												className="opacity-25"
												cx="12"
												cy="12"
												r="10"
												stroke="currentColor"
												strokeWidth="4"
											></circle>
											<path
												className="opacity-75"
												fill="currentColor"
												d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
											></path>
										</svg>
										<span>{processingText}</span>
									</>
								) : (
									<span>{confirmText}</span>
								)}
							</button>
							<button
								type="button"
								onClick={handleClose}
								disabled={isProcessing}
								className="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold text-sm transition-all duration-200 disabled:opacity-50 cursor-pointer active:scale-95"
							>
								{cancelText}
							</button>
						</div>
					</DialogPanel>
				</div>
			</div>
		</Dialog>
	)
}
