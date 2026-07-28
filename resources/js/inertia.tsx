/// <reference types="vite/client" />
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
	resolve: name => {
		const pages = import.meta.glob('./Pages/**/*.tsx', {eager: true})
		const module: any = pages[`./Pages/${name}.tsx`]
		const componentName = name.split('/').pop()!
		return module.default || module[componentName]
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
