import { Show } from '@/Pages/Budgets/Show'

/**
 * Inertia Page Registry
 * Statically maps Inertia page routes to their corresponding React components.
 * Enables strict type checking for page props and ensures IDEs acknowledge page entrypoints.
 */
export interface InertiaPageRegistry {
	'Budgets/Show': typeof Show
}
