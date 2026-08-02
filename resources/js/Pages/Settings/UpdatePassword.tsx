import React from 'react'
import {Head, useForm} from '@inertiajs/react'
import {route} from 'ziggy-js'
import {useTranslation} from '@/hooks/useTranslation'
import {InputError} from '@/Components/InputError'
import {SettingsHeader} from '@/Components/settings/SettingsHeader'

export const UpdatePassword = () => {
	const {t} = useTranslation()

	const {data, setData, put, errors, processing, reset} = useForm({
		current_password: '',
		password: '',
		password_confirmation: '',
	})

	const submit = (e: React.SubmitEvent<HTMLFormElement>) => {
		e.preventDefault()
		put(route('settings.password.update'), {
			onSuccess: () => reset(),
			onError: () => reset('password', 'password_confirmation'),
		})
	}

	return (
		<>
			<Head title={t('password_settings_title')}/>

			<div className="min-h-[calc(100vh-64px)] sm:min-h-[calc(100vh-80px)] bg-slate-50/70 p-6 sm:p-10">
				<div className="max-w-2xl mx-auto">
					<SettingsHeader
						title={t('password_settings_title')}
						subtitle={t('password_settings_subtitle')}
						activeTab="password"
					/>

					{/* Password Form Card */}
					<div className="bg-white rounded-2xl p-6 sm:p-8 border border-purple-900/10 shadow-sm">
						<form onSubmit={submit} className="space-y-6">
							{/* Current Password Input */}
							<div>
								<label className="block text-sm font-bold text-gray-700 mb-2"
								       htmlFor="current_password">
									{t('current_password_label')}
								</label>
								<input
									id="current_password"
									type="password"
									placeholder={t('current_password_placeholder')}
									className="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-900 text-sm focus:ring-1 focus:ring-purple-900 focus:outline-none transition-colors shadow-xs"
									value={data.current_password}
									onChange={(e) => setData('current_password', e.target.value)}
								/>
								{errors.current_password &&
									<InputError message={errors.current_password} className="mt-1.5"/>}
							</div>

							{/* New Password Input */}
							<div>
								<label className="block text-sm font-bold text-gray-700 mb-2" htmlFor="password">
									{t('new_password_label')}
								</label>
								<input
									id="password"
									type="password"
									placeholder={t('new_password_placeholder')}
									className="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-900 text-sm focus:ring-1 focus:ring-purple-900 focus:outline-none transition-colors shadow-xs"
									value={data.password}
									onChange={(e) => setData('password', e.target.value)}
								/>
								{errors.password && <InputError message={errors.password} className="mt-1.5"/>}
							</div>

							{/* Password Confirmation Input */}
							<div>
								<label className="block text-sm font-bold text-gray-700 mb-2"
								       htmlFor="password_confirmation">
									{t('confirm_password_label')}
								</label>
								<input
									id="password_confirmation"
									type="password"
									placeholder={t('confirm_password_placeholder')}
									className="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-900 text-sm focus:ring-1 focus:ring-purple-900 focus:outline-none transition-colors shadow-xs"
									value={data.password_confirmation}
									onChange={(e) => setData('password_confirmation', e.target.value)}
								/>
								{errors.password_confirmation &&
									<InputError message={errors.password_confirmation} className="mt-1.5"/>}
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
											<span>{t('updating_password')}</span>
										</>
									) : (
										<span>{t('update_password')}</span>
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

