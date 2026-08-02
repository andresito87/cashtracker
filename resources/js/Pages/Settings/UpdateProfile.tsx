import React from 'react'
import {Head, useForm, usePage} from '@inertiajs/react'
import {route} from 'ziggy-js'
import {SharedData} from '@/types'
import {useTranslation} from '@/hooks/useTranslation'
import {InputError} from '@/Components/InputError'
import {SettingsHeader} from '@/Components/settings/SettingsHeader'

export const UpdateProfile = () => {
	const {auth} = usePage<SharedData>().props
	const {t} = useTranslation()

	const {data, setData, put, errors, processing} = useForm({
		name: auth?.user?.name ?? '',
		email: auth?.user?.email ?? '',
	})

	const submit = (e: React.SubmitEvent<HTMLFormElement>) => {
		e.preventDefault()
		put(route('settings.profile.update'))
	}

	return (
		<>
			<Head title={t('profile_settings_title')}/>

			<div className="min-h-[calc(100vh-64px)] sm:min-h-[calc(100vh-80px)] bg-slate-50/70 p-6 sm:p-10">
				<div className="max-w-2xl mx-auto">
					<SettingsHeader
						title={t('profile_settings_title')}
						subtitle={t('profile_settings_subtitle')}
						activeTab="profile"
					/>

					{/* Profile Form Card */}
					<div className="bg-white rounded-2xl p-6 sm:p-8 border border-purple-900/10 shadow-sm">
						<form onSubmit={submit} className="space-y-6">
							{/* Name Input */}
							<div>
								<label className="block text-sm font-bold text-gray-700 mb-2" htmlFor="name">
									{t('name')}
								</label>
								<input
									id="name"
									type="text"
									placeholder={t('name_placeholder')}
									className="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-900 text-sm focus:ring-1 focus:ring-purple-900 focus:outline-none transition-colors shadow-xs"
									value={data.name}
									onChange={(e) => setData('name', e.target.value)}
								/>
								{errors.name && <InputError message={errors.name} className="mt-1.5"/>}
							</div>

							{/* Email Input */}
							<div>
								<label className="block text-sm font-bold text-gray-700 mb-2" htmlFor="email">
									{t('email')}
								</label>
								<input
									id="email"
									type="email"
									placeholder={t('email_placeholder')}
									className="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-900 text-sm focus:ring-1 focus:ring-purple-900 focus:outline-none transition-colors shadow-xs"
									value={data.email}
									onChange={(e) => setData('email', e.target.value)}
								/>
								{errors.email && <InputError message={errors.email} className="mt-1.5"/>}
								<p className="mt-2 text-xs text-gray-500">
									{t('email_verification_notice')}
								</p>
							</div>

							{/* Submit Button */}
							<div className="pt-2">
								<button
									type="submit"
									disabled={processing}
									className="w-full bg-purple-950 hover:bg-purple-900 text-white font-bold py-3.5 px-6 rounded-xl shadow-md shadow-purple-950/20 hover:shadow-purple-950/35 transition-all duration-200 active:scale-95 text-base flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
								>
									{processing ? (
										<>
											<svg className="animate-spin w-5 h-5 text-white" fill="none"
											     viewBox="0 0 24 24">
												<circle className="opacity-25" cx="12" cy="12" r="10"
												        stroke="currentColor" strokeWidth="4"/>
												<path className="opacity-75" fill="currentColor"
												      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
											</svg>
											<span>{t('saving_changes')}</span>
										</>
									) : (
										<span>{t('save_changes')}</span>
									)}
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</>
	)
}

