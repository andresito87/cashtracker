import React, {useEffect, useRef, useState} from 'react';
import {useTranslation} from '@/hooks/useTranslation';
import {useChat} from '@ai-sdk/react';
import {DefaultChatTransport} from 'ai';
import {Toast} from '@/Components/toast';
import toast from 'react-hot-toast';
import {router} from '@inertiajs/react';
import {useTicketScan} from '@/hooks/useTicketScan';
import {ChatMessageItem} from '@/Components/ChatMessageItem';

type Props = {
	budgetId: number;
};

export const CashTrackerAgent = ({budgetId}: Props) => {
	const {t} = useTranslation();
	const [input, setInput] = useState('');
	const messagesEndRef = useRef<HTMLDivElement>(null);

	const {sendMessage, messages, status, setMessages} = useChat({
		transport: new DefaultChatTransport({
			api: `/budgets/${budgetId}/chat`,
		}),
		onFinish: ({message}) => {
			const expenseCreated = message.parts.find((part) => {
				const isAddExpenseTool = part.type === 'tool-AddExpense';
				const finished = 'state' in part && part.state === 'output-available';

				return isAddExpenseTool && finished;
			});

			const hasTag = message.parts?.some(
				(p) => 'text' in p && typeof p.text === 'string' && p.text.includes('[EXPENSE_CREATED]')
			);

			if (expenseCreated || hasTag) {
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

				router.reload({only: ['budget', 'expenses']});
			}
		}
	});

	const {isUploadingTicket, fileInputRef, handleUploadClick, handleFileChange} = useTicketScan({
		budgetId,
		setMessages,
	});

	const isLoading = status === 'submitted' || status === 'streaming';
	const isProcessing = isLoading || isUploadingTicket;

	useEffect(() => {
		messagesEndRef.current?.scrollIntoView({behavior: 'smooth'});
	}, [messages, status, isUploadingTicket]);

	const handleSubmit = (e: React.SubmitEvent<HTMLFormElement>) => {
		e.preventDefault();
		if (input.trim() && !isProcessing) {
			sendMessage({text: input.trim()}).catch((error: unknown) => {
				console.error('[CashTrackerAgent] Failed to send message:', error)
			});
			setInput('');
		}
	};

	return (
		<section className="bg-white border border-purple-900/10 rounded-3xl p-6 sm:p-8 shadow-2xs space-y-6">

			{/* Chat Section Header */}
			<div className="flex items-start gap-3.5">
				<div
					className="w-12 h-12 rounded-2xl bg-[#1b0e35] text-white flex items-center justify-center shadow-md shrink-0">
					<svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
						<path strokeLinecap="round" strokeLinejoin="round"
						      d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>
					</svg>
				</div>
				<div>
					<h2 className="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
						{t('agent_title')}
					</h2>
					<p className="text-xs sm:text-sm text-gray-500 font-medium mt-1">
						{t('agent_subtitle')}
					</p>
				</div>
			</div>

			{/* Messages Container */}
			{messages.length > 0 && (
				<div
					className="min-h-88 max-h-128 sm:max-h-152 overflow-y-auto space-y-4 pr-2 scrollbar-thin scrollbar-thumb-gray-200">
					{messages.map((message) => (
						<ChatMessageItem key={message.id} message={message as any}/>
					))}

					{isProcessing && (
						<div className="flex gap-3 justify-start items-center text-xs text-gray-400">
							<div
								className="w-8 h-8 rounded-xl bg-[#1b0e35] text-white flex items-center justify-center shrink-0 text-xs font-bold opacity-60">
								AI
							</div>
							<div
								className="flex items-center gap-1.5 bg-slate-100/60 border border-slate-200/50 px-3.5 py-2.5 rounded-2xl rounded-bl-none">
								<span className="w-2 h-2 rounded-full bg-purple-600 animate-pulse"></span>
								<span className="w-2 h-2 rounded-full bg-purple-600 animate-pulse delay-150"></span>
								<span className="w-2 h-2 rounded-full bg-purple-600 animate-pulse delay-300"></span>
							</div>
						</div>
					)}
					<div ref={messagesEndRef}/>
				</div>
			)}

			{/* Chat Input & Form Area */}
			<form onSubmit={handleSubmit} className="space-y-4">
				<textarea
					value={input}
					onChange={(e) => setInput(e.target.value)}
					placeholder={t('agent_placeholder')}
					rows={3}
					className="w-full rounded-2xl border border-gray-200 bg-slate-50/60 p-4 text-sm sm:text-base placeholder:text-gray-400 focus:ring-2 focus:ring-purple-900/20 focus:outline-none transition-all duration-200 resize-none shadow-2xs"
				/>

				<div className="flex flex-col sm:flex-row items-center justify-between gap-3">
					<button
						type="button"
						onClick={handleUploadClick}
						disabled={isProcessing}
						className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4.5 py-2.5 rounded-xl bg-white hover:bg-purple-50/70 text-purple-950 border border-purple-200/90 font-bold text-xs sm:text-sm shadow-2xs transition-all duration-200 active:scale-95 cursor-pointer disabled:opacity-40 disabled:pointer-events-none"
					>
						<svg className="w-4 h-4 text-purple-900 shrink-0" fill="none" viewBox="0 0 24 24"
						     stroke="currentColor" strokeWidth="2">
							<path strokeLinecap="round" strokeLinejoin="round"
							      d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316A2.192 2.192 0 0014.49 4.25H9.51c-.69 0-1.325.356-1.69.948l-.993 1.623z"/>
							<path strokeLinecap="round" strokeLinejoin="round" d="M15 13.5a3 3 0 11-6 0 3 3 0 016 0z"/>
						</svg>
						<span>{t('agent_upload_ticket')}</span>
					</button>

					<button
						type="submit"
						disabled={!input.trim() || isProcessing}
						className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-[#1b0e35] hover:bg-[#28154e] text-white font-bold text-xs sm:text-sm shadow-md hover:shadow-lg transition-all duration-200 active:scale-95 cursor-pointer disabled:opacity-40 disabled:pointer-events-none"
					>
						<svg className="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
						     strokeWidth="2.5">
							<path strokeLinecap="round" strokeLinejoin="round" d="M6 12L3 21l19-9L3 3l3 9zm0 0h7"/>
						</svg>
						<span>
							{isProcessing
								? t('agent_thinking')
								: t('agent_consult')}
						</span>
					</button>
				</div>
				<input
					ref={fileInputRef}
					type="file"
					accept="image/*"
					onChange={handleFileChange}
					className="hidden"
				/>
			</form>
		</section>
	);
};
