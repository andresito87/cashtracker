import {useState} from 'react'
import {router} from '@inertiajs/react'
import {route} from 'ziggy-js'

export function useSubscriptionActions() {
	const [loadingAction, setLoadingAction] = useState<string | undefined>(undefined)

	const subscribe = (plan: 'monthly' | 'yearly') => {
		setLoadingAction(`checkout_${plan}`)
		router.post(route('subscription.checkout', {plan}), {}, {
			onFinish: () => setLoadingAction(undefined),
		})
	}

	const swapPlan = (plan: 'monthly' | 'yearly') => {
		setLoadingAction(`swap_${plan}`)
		router.post(route('subscription.swap', {plan}), {}, {
			onFinish: () => setLoadingAction(undefined),
		})
	}

	const cancelSubscription = () => {
		setLoadingAction('cancel')
		router.post(route('subscription.cancel'), {}, {
			onFinish: () => setLoadingAction(undefined),
		})
	}

	const resumeSubscription = () => {
		setLoadingAction('resume')
		router.post(route('subscription.resume'), {}, {
			onFinish: () => setLoadingAction(undefined),
		})
	}

	return {
		loadingAction,
		subscribe,
		swapPlan,
		cancelSubscription,
		resumeSubscription,
	}
}
