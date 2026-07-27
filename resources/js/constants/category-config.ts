export interface CategoryMeta {
	icon: string
	badge: string
	iconBg: string
}

export const CATEGORY_CONFIG: Record<string, CategoryMeta> = {
	food: {
		icon: '🛒',
		badge: 'bg-amber-50 text-amber-800 border-amber-200/80',
		iconBg: 'bg-amber-100/80 text-amber-900 border-amber-200/80',
	},
	transportation: {
		icon: '🚗',
		badge: 'bg-blue-50 text-blue-800 border-blue-200/80',
		iconBg: 'bg-blue-100/80 text-blue-900 border-blue-200/80',
	},
	health: {
		icon: '🩺',
		badge: 'bg-rose-50 text-rose-800 border-rose-200/80',
		iconBg: 'bg-rose-100/80 text-rose-900 border-rose-200/80',
	},
	entertainment: {
		icon: '🎬',
		badge: 'bg-purple-50 text-purple-800 border-purple-200/80',
		iconBg: 'bg-purple-100/80 text-purple-900 border-purple-200/80',
	},
	subscriptions: {
		icon: '💳',
		badge: 'bg-indigo-50 text-indigo-800 border-indigo-200/80',
		iconBg: 'bg-indigo-100/80 text-indigo-900 border-indigo-200/80',
	},
	beauty: {
		icon: '🧴',
		badge: 'bg-pink-50 text-pink-800 border-pink-200/80',
		iconBg: 'bg-pink-100/80 text-pink-900 border-pink-200/80',
	},
	clothing: {
		icon: '👕',
		badge: 'bg-emerald-50 text-emerald-800 border-emerald-200/80',
		iconBg: 'bg-emerald-100/80 text-emerald-900 border-emerald-200/80',
	},
	home: {
		icon: '🏠',
		badge: 'bg-sky-50 text-sky-800 border-sky-200/80',
		iconBg: 'bg-sky-100/80 text-sky-900 border-sky-200/80',
	},
	utilities: {
		icon: '💡',
		badge: 'bg-teal-50 text-teal-800 border-teal-200/80',
		iconBg: 'bg-teal-100/80 text-teal-900 border-teal-200/80',
	},
	education: {
		icon: '📚',
		badge: 'bg-cyan-50 text-cyan-800 border-cyan-200/80',
		iconBg: 'bg-cyan-100/80 text-cyan-900 border-cyan-200/80',
	},
	pets: {
		icon: '🐾',
		badge: 'bg-amber-100/60 text-amber-900 border-amber-300/80',
		iconBg: 'bg-amber-100 text-amber-900 border-amber-300/80',
	},
	other: {
		icon: '📦',
		badge: 'bg-slate-100 text-slate-700 border-slate-200',
		iconBg: 'bg-slate-100 text-slate-700 border-slate-200',
	},
}

export const getCategoryMeta = (category?: string): CategoryMeta => {
	const key = category?.toLowerCase() || 'other'
	return (
		CATEGORY_CONFIG[key] || {
			icon: '🏷️',
			badge: 'bg-slate-100 text-slate-700 border-slate-200',
			iconBg: 'bg-slate-100 text-slate-700 border-slate-200',
		}
	)
}
