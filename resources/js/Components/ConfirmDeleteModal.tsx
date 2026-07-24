import React, {useEffect} from 'react'

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
	useEffect(() => {
		const handleKeyDown = (e: KeyboardEvent) => {
			if (e.key === 'Escape' && isOpen && !isProcessing) {
				onClose()
			}
		}

		if (isOpen) {
			document.body.style.overflow = 'hidden'
			window.addEventListener('keydown', handleKeyDown)
		}

		return () => {
			document.body.style.overflow = ''
			window.removeEventListener('keydown', handleKeyDown)
		}
	}, [isOpen, isProcessing, onClose])

	if (!isOpen) return null

	return (
		<div
			className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200"
			onClick={onClose}
			role="dialog"
			aria-modal="true"
			aria-labelledby="modal-title"
		>
			<div
				className="bg-white border border-purple-900/10 rounded-2xl max-w-md w-full p-6 sm:p-8 shadow-xl space-y-6 transform transition-all duration-200 scale-100"
				onClick={(e) => e.stopPropagation()}
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
						<h3 id="modal-title" className="text-lg font-bold text-gray-900">
							{title}
						</h3>
						<p className="mt-2 text-sm text-gray-600 leading-relaxed">
							{message}
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
						onClick={onClose}
						disabled={isProcessing}
						className="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold text-sm transition-all duration-200 disabled:opacity-50 cursor-pointer active:scale-95"
					>
						{cancelText}
					</button>
				</div>
			</div>
		</div>
	)
}
