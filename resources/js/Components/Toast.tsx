import React, {useEffect, useRef} from 'react'

export interface ToastProps {
	message: string
	type: 'success' | 'error'
	duration?: number
	visible?: boolean
	onClose?: () => void
}

export const Toast = ({message, type, duration = 4000, visible = true, onClose}: ToastProps) => {
	const progressRef = useRef<HTMLDivElement>(null)
	const isError = type === 'error'

	const accentColor = isError ? '#f43f5e' : '#10b981'
	const iconBg = isError ? '#fff1f2' : '#ecfdf5'
	const textColor = isError ? '#881337' : '#064e3b'

	useEffect(() => {
		if (!visible) return
		const el = progressRef.current
		if (!el) return

		el.style.transition = 'none'
		el.style.width = '100%'

		const raf = requestAnimationFrame(() => {
			requestAnimationFrame(() => {
				el.style.transition = `width ${duration}ms linear`
				el.style.width = '0%'
			})
		})

		return () => cancelAnimationFrame(raf)
	}, [visible, duration])

	return (
		<div
			role="alert"
			aria-live="polite"
			style={{
				opacity: visible ? 1 : 0,
				transform: visible ? 'translateX(0)' : 'translateX(12px)',
				transition: 'opacity 220ms ease, transform 220ms ease',
				fontFamily: 'inherit',
			}}
			className="relative w-90 bg-white rounded-2xl shadow-xl overflow-hidden"
		>
			<div
				className="absolute inset-y-0 left-0 w-1.25"
				style={{backgroundColor: accentColor}}
			/>

			<div className="flex items-center gap-3 pl-5 pr-4 py-3.5">
				<div
					className="shrink-0 w-8 h-8 rounded-xl flex items-center justify-center"
					style={{backgroundColor: iconBg}}
				>
					{isError ? (
						<svg className="w-4 h-4" viewBox="0 0 20 20" fill={accentColor}>
							<path fillRule="evenodd"
							      d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
							      clipRule="evenodd"/>
						</svg>
					) : (
						<svg className="w-4 h-4" viewBox="0 0 20 20" fill={accentColor}>
							<path fillRule="evenodd"
							      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
							      clipRule="evenodd"/>
						</svg>
					)}
				</div>

				<p className="flex-1 text-sm font-semibold leading-snug" style={{color: textColor}}>
					{message}
				</p>

				{onClose && (
					<button
						type="button"
						onClick={onClose}
						aria-label="Close notification"
						className="shrink-0 w-6 h-6 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors cursor-pointer"
					>
						<svg className="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
							<path
								d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
						</svg>
					</button>
				)}
			</div>

			<div className="h-0.75 w-full bg-gray-100">
				<div
					ref={progressRef}
					className="h-full"
					style={{backgroundColor: accentColor, width: '100%', opacity: 0.85}}
				/>
			</div>
		</div>
	)
}
