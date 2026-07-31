import React from 'react'
import {useTranslation} from '@/hooks/useTranslation'

interface SubscriptionDowngradeProps {
	endsAt?: string | null
}

export const SubscriptionDowngrade: React.FC<SubscriptionDowngradeProps> = ({endsAt}) => {
	const {t} = useTranslation()

	return (
		<div className="rounded-2xl bg-indigo-600 p-6 sm:p-8 text-white shadow-sm border border-indigo-500/30">
			<div className="flex items-start gap-4">
				<div className="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center shrink-0 mt-0.5">
					<svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
						      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
					</svg>
				</div>
				<div>
					<h3 className="text-xl sm:text-2xl font-extrabold text-white tracking-tight mb-2">
						{t('downgrade_yearly_title')}
					</h3>
					<p className="text-indigo-100 text-sm sm:text-base leading-relaxed">
						{t('downgrade_yearly_desc', {
							date: endsAt || '',
						})}
					</p>
				</div>
			</div>
		</div>
	)
}

