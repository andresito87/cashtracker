import {create} from 'zustand'
import {devtools} from 'zustand/middleware'
import {Budget} from '@/types'

interface BudgetModalState {
	isOpen: boolean
	budget: Budget | null
	openModal: (budget: Budget) => void
	closeModal: () => void
}

export const useBudgetModalStore = create<BudgetModalState>()(
	devtools(
		(set) => ({
			isOpen: false,
			budget: null,
			openModal: (budget) => set({isOpen: true, budget}, false, 'openModal'),
			closeModal: () => set({isOpen: false, budget: null}, false, 'closeModal'),
		}),
		{name: 'BudgetModalStore'}
	)
)
