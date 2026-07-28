import React from 'react'
import {Toaster} from 'react-hot-toast'

export const ToastContainer = () => (
	<Toaster
		position="top-right"
		containerStyle={{
			top: 96,
			right: 16,
		}}
		toastOptions={{
			duration: 4000,
			style: {
				background: 'transparent',
				boxShadow: 'none',
				padding: 0,
				maxWidth: '420px',
			},
		}}
	/>
)

