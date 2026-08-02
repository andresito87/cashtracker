import React from 'react'
import {Link} from '@inertiajs/react'
import {route} from 'ziggy-js'
import {useTranslation} from '@/hooks/useTranslation'

interface SettingsHeaderProps {
	title: string
	subtitle: string
	activeTab: 'profile' | 'password'
}

export const SettingsHeader = ({title, subtitle, activeTab}: SettingsHeaderProps) => {
	const {t} = useTranslation()

	return (
		<div className="mb-8">
			{/* Back Link */}
			<div className="mb-4">
				<Link
					href={route('dashboard')}
					className="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-purple-900/80 hover:text-purple-900 transition-colors group"
				>
					<svg
						className="w-3.5 h-3.5 transition-transform group-hover:-translate-x-0.5"
						fill="none"
						stroke="currentColor"
						viewBox="0 0 24 24"
						strokeWidth="2.5"
					>
						<path strokeLinecap="round" strokeLinejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
					</svg>
					<span>{t('back_to_list')}</span>
				</Link>
			</div>

			{/* Title & Subtitle */}
			<div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
				<div>
					<h1 className="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
						{title}
					</h1>
					<p className="text-gray-500 text-base mt-1">
						{subtitle}
					</p>
				</div>
			</div>

			{/* Tabs Navigation */}
			<div className="mt-6 border-b border-gray-200">
				<nav className="-mb-px flex gap-6" aria-label="Tabs">
					<Link
						href={route('settings.profile')}
						prefetch
						className={`inline-flex items-center gap-2 py-3 px-1 border-b-2 text-sm font-bold transition-all duration-150 ${
							activeTab === 'profile'
								? 'border-purple-900 text-purple-900'
								: 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
						}`}
					>
						<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2">
							<path strokeLinecap="round" strokeLinejoin="round"
							      d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
						</svg>
						<span>{t('profile_tab')}</span>
					</Link>

					<Link
						href={route('settings.password')}
						prefetch
						className={`inline-flex items-center gap-2 py-3 px-1 border-b-2 text-sm font-bold transition-all duration-150 ${
							activeTab === 'password'
								? 'border-purple-900 text-purple-900'
								: 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
						}`}
					>
						<svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2">
							<path strokeLinecap="round" strokeLinejoin="round"
							      d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
						</svg>
						<span>{t('password_tab')}</span>
					</Link>
				</nav>
			</div>
		</div>
	)
}
