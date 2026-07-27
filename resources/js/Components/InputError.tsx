import React, {HTMLAttributes} from 'react'

export interface InputErrorProps extends HTMLAttributes<HTMLParagraphElement> {
	message?: string
	className?: string
}

export const InputError = ({message, className = '', ...props}: InputErrorProps) => {
	if (!message) {
		return null
	}

	return (
		<p
			{...props}
			className={`text-xs text-rose-600 mt-1 font-medium ${className}`}
		>
			{message}
		</p>
	)
}
