import React, {useRef, useState} from 'react';
import {router} from '@inertiajs/react';
import {useTranslation} from '@/hooks/useTranslation';
import {Toast} from '@/Components/toast';
import toast from 'react-hot-toast';
import {route} from 'ziggy-js'

type UseTicketScanOptions = {
	budgetId: number;
	setMessages: (updater: (prev: any[]) => any[]) => void;
};

export const useTicketScan = ({budgetId, setMessages}: UseTicketScanOptions) => {
	const {t} = useTranslation();
	const [isUploadingTicket, setIsUploadingTicket] = useState(false);
	const fileInputRef = useRef<HTMLInputElement>(null);

	const handleUploadClick = () => {
		fileInputRef.current?.click();
	};

	const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
		const file = e.target.files?.[0];
		if (!file) return;

		const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
		if (!csrfToken) {
			console.error('CSRF token not found');
			return;
		}

		setIsUploadingTicket(true);

		setMessages((prev: any[]) => [
			...prev,
			{
				id: crypto.randomUUID(),
				role: 'user',
				content: t('ticket_scan_user_message'),
				parts: [
					{
						type: 'text',
						text: t('ticket_scan_user_full_message'),
					}
				]
			}
		]);

		const handleError = (errorMessage: string) => {
			setMessages((prev: any[]) => [
				...prev,
				{
					id: crypto.randomUUID(),
					role: 'assistant',
					content: errorMessage,
					parts: [{type: 'text', text: errorMessage}]
				}
			]);
		};

		try {
			const formData = new FormData();
			formData.append('image', file);

			const response = await fetch(route('budgets.scan-ticket', {budget: budgetId}), {
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': csrfToken,
					'Accept': 'application/json'
				},
				credentials: 'same-origin',
				body: formData
			});

			const data = await response.json().catch(() => null);

			if (!response.ok || !data?.success) {
				const errorMessage = data?.message || t('ticket_scan_error_http', {status: response.status.toString()});
				handleError(errorMessage);
				return;
			}

			toast.custom((tToast) => (
				<Toast
					message={t('expense_created')}
					type="success"
					duration={4000}
					visible={tToast.visible}
					onClose={() => toast.dismiss(tToast.id)}
				/>
			), {
				duration: 4000,
			});

			setMessages((prev: any[]) => [
				...prev,
				{
					id: crypto.randomUUID(),
					role: 'assistant',
					content: data.message,
					parts: [{type: 'text', text: data.message}]
				}
			]);

			router.reload({only: ['budget', 'expenses']});

		} catch (error) {
			console.error('Unexpected error uploading file:', error);
			handleError(t('ticket_scan_error_connection'));
		} finally {
			setIsUploadingTicket(false);
			if (fileInputRef.current) fileInputRef.current.value = '';
		}
	};

	return {
		isUploadingTicket,
		fileInputRef,
		handleUploadClick,
		handleFileChange
	};
};
