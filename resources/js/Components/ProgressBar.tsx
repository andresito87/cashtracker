import React, {useEffect, useState} from 'react'
import {buildStyles, CircularProgressbar} from 'react-circular-progressbar'
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

	return (
		<div className="w-52 h-52 sm:w-64 sm:h-64 mx-auto flex items-center justify-center p-2">
			<CircularProgressbar
				value={clampedPercentage}
				text={`${clampedPercentage}% ${label}`}
				styles={buildStyles({
					pathColor: pathColor,
					textColor: textColor,
					trailColor: '#f1f5f9',
					strokeLinecap: 'round',
					textSize: '12px',
					pathTransitionDuration: 1.0,
				})}
			/>
		</div>
	)
}
