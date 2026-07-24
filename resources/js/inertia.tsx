/// <reference types="vite/client" />
import {createInertiaApp} from '@inertiajs/react'
import {createRoot} from 'react-dom/client'

createInertiaApp({
	title: title => title ? `CashTracker - ${title}` : 'CashTracker',
	resolve: name => {
		const pages = import.meta.glob('./Pages/**/*.tsx', {eager: true})
		const module: any = pages[`./Pages/${name}.tsx`]
		const componentName = name.split('/').pop()!
		return module.default || module[componentName]
	},
	setup({el, App, props}) {
		createRoot(el).render(<App {...props} />)
	},
}).then()
