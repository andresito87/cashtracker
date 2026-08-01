/// <reference types="vite/client" />
import React from 'react'
import {createInertiaApp} from '@inertiajs/react'
import {createRoot} from 'react-dom/client'
import {ToastContainer} from '@/Components/ToastContainer'
import {FlashToastListener} from '@/Components/FlashToastListener'

createInertiaApp({
	title: title => title ? `CashTracker - ${title}` : 'CashTracker',
	progress: {
		color: '#9333ea',
		showSpinner: false,
	},
	resolve: async name => {
		const pages = import.meta.glob('./Pages/**/*.tsx')
		const resolver = pages[`./Pages/${name}.tsx`]
		if (!resolver) throw new Error(`Page not found: ${name}`)
		const module = await resolver() as Record<string, unknown>
		const componentName = name.split('/').pop()!
		return (module.default ?? module[componentName]) as React.ComponentType
	},
	setup({el, App, props}) {
		createRoot(el).render(
			<>
				<App {...props}>
					{({Component, props, key}) => (
						<>
							<FlashToastListener/>
							<Component key={key} {...props} />
						</>
					)}
				</App>
				<ToastContainer/>
			</>
		)
	},
}).then()
