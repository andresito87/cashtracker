import {Show} from '@/Pages/Budgets/Show'
import {Dashboard} from '@/Pages/Dashboard'
import {Manage} from '@/Pages/Subscriptions/Manage'
import {UpdateProfile} from '@/Pages/Settings/UpdateProfile'
import {UpdatePassword} from '@/Pages/Settings/UpdatePassword'

/**
 * Inertia Page Registry
 * Statically maps Inertia page routes to their corresponding React components.
 * Enables strict type checking for page props and ensures IDEs acknowledge page entrypoints.
 */
export interface InertiaPageRegistry {
	'Budgets/Show': typeof Show
	'Dashboard': typeof Dashboard
	'Subscriptions/Manage': typeof Manage
	'Settings/UpdateProfile': typeof UpdateProfile
	'Settings/UpdatePassword': typeof UpdatePassword
}
