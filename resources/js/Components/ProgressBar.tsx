import React, {useEffect, useState} from 'react'
import {buildStyles, CircularProgressbarWithChildren} from 'react-circular-progressbar'
import 'react-circular-progressbar/dist/styles.css'

export interface ProgressBarProps {
	percentage: number
	label?: string
}

export const ProgressBar = ({percentage, label = 'Gastado'}: ProgressBarProps) => {
	const [progress, setProgress] = useState(0)

	useEffect(() => {
		const timer = setTimeout(() => {
			setProgress(percentage)
		}, 200)

		return () => clearTimeout(timer)
	}, [percentage])

	const clampedPercentage = Math.min(Math.max(Math.round(progress), 0), 100)
	const isOverBudget = progress > 100

	const pathColor = isOverBudget ? '#e11d48' : '#1b0e35'
	const textColor = isOverBudget ? '#e11d48' : '#1b0e35'
	const isThreeDigits = clampedPercentage >= 100

	return (
		<div className="w-48 h-48 sm:w-56 sm:h-56 mx-auto flex items-center justify-center p-2">
			<CircularProgressbarWithChildren
				value={clampedPercentage}
				styles={buildStyles({
					pathColor: pathColor,
					trailColor: '#f1f5f9',
					strokeLinecap: 'round',
					pathTransitionDuration: 1.0,
				})}
			>
				<div className="flex flex-col items-center justify-center text-center px-4 py-2">
					<span
						className={`font-black tracking-tight leading-none ${
							isThreeDigits ? 'text-xl sm:text-2xl' : 'text-2xl sm:text-3xl'
						}`}
						style={{color: textColor}}
					>
						{clampedPercentage}%
					</span>
					<span className="text-xs font-extrabold text-gray-500 uppercase tracking-wider mt-1 sm:mt-1.5">
						{label}
					</span>
				</div>
			</CircularProgressbarWithChildren>
		</div>
	)
}
