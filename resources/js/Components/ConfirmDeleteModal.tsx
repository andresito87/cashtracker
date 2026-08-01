import React from 'react'
import {ConfirmModal, ConfirmModalProps} from './ConfirmModal'

export const ConfirmDeleteModal = ({
									   confirmText = 'Eliminar',
									   processingText = 'Eliminando...',
									   ...props
								   }: ConfirmModalProps) => {
	return (
		<ConfirmModal
			confirmText={confirmText}
			processingText={processingText}
			variant="danger"
			{...props}
		/>
	)
}

