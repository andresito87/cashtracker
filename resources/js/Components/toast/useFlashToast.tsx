import React, {useEffect} from 'react'
import {usePage} from '@inertiajs/react'
import toast from 'react-hot-toast'
import {Toast} from './Toast'
import {SharedData} from '@/types'

const TOAST_DURATION = 4000

export const useFlashToast = () => {
	const {flash} = usePage<SharedData>().props

	useEffect(() => {
		if (!flash?.status) return

		toast.custom((t) => (
			<Toast
				message={flash.status!}
				type={flash.status_type === 'error' ? 'error' : 'success'}
				duration={TOAST_DURATION}
				visible={t.visible}
				onClose={() => toast.dismiss(t.id)}
			/>
		), {
			id: flash.status,
			duration: TOAST_DURATION,
		})
	}, [flash?.status, flash?.status_type])
}
