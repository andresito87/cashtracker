import {create} from 'zustand'
import {devtools} from 'zustand/middleware'
import {Budget, Expense} from '@/types'
import {Category} from '@/types/Category'

interface ExpenseModalState {
	isOpen: boolean
	budget: Budget | null
	categories: Category[]
	editingExpense: Expense | null
	setBudget: (budget: Budget | null) => void
	openModal: (expense?: Expense | null) => void
	closeModal: () => void
	setIsOpen: (isOpen: boolean) => void
	setCategories: (categories: Category[]) => void
}

export const useExpenseModalStore = create<ExpenseModalState>()(
	devtools(
		(set) => ({
			isOpen: false,
			editingExpense: null,
			budget: null,
			categories: [],
			setBudget: (budget) => set({budget}, false, 'setBudget'),
			openModal: (expense = null) => set({isOpen: true, editingExpense: expense}, false, 'openModal'),
			closeModal: () => set({isOpen: false, editingExpense: null}, false, 'closeModal'),
			setIsOpen: (isOpen) =>
				set((state) => ({isOpen, editingExpense: isOpen ? state.editingExpense : null}), false, 'setIsOpen'),
			setCategories: (categories) => set({categories}, false, 'setCategories'),
		}),
		{name: 'ExpenseModalStore'}
	)
)
