import React, {useEffect, useRef, useState} from 'react';
import {useTranslation} from '@/hooks/useTranslation';
import {useChat} from '@ai-sdk/react';
import {DefaultChatTransport} from "ai";
import {Toast} from "@/Components/Toast";
import toast from 'react-hot-toast';
import {router} from "@inertiajs/react";

type Props = {
	budgetId: number
}

export default function CashTrackerAgent({budgetId}: Props) {
	const {t} = useTranslation();
	const [input, setInput] = useState('');
	const fileInputRef = useRef<HTMLInputElement>(null);
	const messagesEndRef = useRef<HTMLDivElement>(null);

	const {sendMessage, messages, status} = useChat({
		transport: new DefaultChatTransport({
			api: `/budgets/${budgetId}/chat`,
		}),
		onFinish: ({message}) => {
			const expenseCreated = message.parts.find((part) => {
				// Check based on output and its content, if available
				// if (!part.output) return null;
				// return part.output.startWith('[EXPENSE_CREATED]');

				const isAddExpenseTool = part.type === 'tool-AddExpense';
				const finished = 'state' in part && part.state === 'output-available';

				return isAddExpenseTool && finished;
			});

			const hasTag = (message as any).content?.includes('[EXPENSE_CREATED]') ||
				message.parts?.some((p: any) => p.text?.includes('[EXPENSE_CREATED]'));

			if (expenseCreated || hasTag) {
				toast.custom((tToast) => (
					<Toast
						message={t('expense_created') !== 'expense_created' ? t('expense_created') : 'Gasto añadido con éxito.'}
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

	const isLoading = status === 'submitted' || status === 'streaming';

	useEffect(() => {
		messagesEndRef.current?.scrollIntoView({behavior: 'smooth'});
	}, [messages, status]);

	const handleUploadClick = () => {
		fileInputRef.current?.click();
	};

	const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
		const file = e.target.files?.[0];
		if (file) {
			// Handle file selection logic when implemented
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
						{t('agent_title') !== 'agent_title' ? t('agent_title') : 'Asistente CashTracker'}
					</h2>
					<p className="text-xs sm:text-sm text-gray-500 font-medium mt-1">
						{t('agent_subtitle') !== 'agent_subtitle' ? t('agent_subtitle') : 'Consulta tus gastos, haz preguntas sobre tu presupuesto o sube un ticket.'}
					</p>
				</div>
			</div>

			{/* Messages Container */}
			{messages.length > 0 && (
				<div
					className="min-h-88 max-h-128 sm:max-h-152 overflow-y-auto space-y-4 pr-2 scrollbar-thin scrollbar-thumb-gray-200">
					{messages.map((message) => {
						const isUser = message.role === 'user';
						const textParts = (message.parts || []).filter((part) => part.type === 'text');
						const rawContent = (message as any).content;
						const hasText = textParts.length > 0 || (typeof rawContent === 'string' && rawContent.trim() !== '');

						if (!hasText) return null;

						const cleanRawMessageText = (text: string): string => {
							if (!text) return '';
							let cleaned = text;

							// Remove <think>...</think> blocks
							cleaned = cleaned.replace(/<think>[\s\S]*?<\/think>/gi, '');

							// Remove [EXPENSE_CREATED] tags
							cleaned = cleaned.replace(/\[EXPENSE_CREATED]/g, '');

							// If internal CoT end tags exist, extract the text after the last end tag
							if (cleaned.includes('<|end|>')) {
								const parts = cleaned.split('<|end|>');
								cleaned = parts[parts.length - 1];
							}
							if (cleaned.includes('<|im_end|>')) {
								const parts = cleaned.split('<|im_end|>');
								cleaned = parts[parts.length - 1];
							}

							// Remove leftover special tags
							cleaned = cleaned.replace(/<\|[a-z_0-9|-]+\|>/gi, '');

							return cleaned.trim();
						};

						const renderFormattedText = (rawText: string) => {
							const text = cleanRawMessageText(rawText);
							if (!text) return null;

							const lines = text.split('\n');
							return lines.map((line, lineIdx) => {
								const parts = line.split(/(`[^`]+`|\*\*.*?\*\*)/g);
								const formattedLine = parts.map((chunk, chunkIdx) => {
									if (chunk.startsWith('`') && chunk.endsWith('`') && chunk.length > 2) {
										return (
											<code
												key={chunkIdx}
												className={
													isUser
														? 'bg-purple-950 text-purple-200 px-1.5 py-0.5 rounded font-mono text-xs'
														: 'bg-slate-200 text-purple-950 px-1.5 py-0.5 rounded font-mono text-xs font-semibold'
												}
											>
												{chunk.slice(1, -1)}
											</code>
										);
									}
									if (chunk.startsWith('**') && chunk.endsWith('**') && chunk.length > 4) {
										return (
											<strong key={chunkIdx}
											        className={isUser ? 'font-bold text-white' : 'font-bold text-gray-900'}>
												{chunk.slice(2, -2)}
											</strong>
										);
									}
									return chunk;
								});

								return (
									<React.Fragment key={lineIdx}>
										{formattedLine.map((item) => (
											<div>
												{item}
											</div>
										))}
										{lineIdx < lines.length - 1 && <br/>}
									</React.Fragment>
								);
							});
						};

						const fullText = textParts.length > 0
							? textParts.map((p: any) => p.text || '').join('\n')
							: rawContent || '';

						const renderedContent = renderFormattedText(fullText);

						// If the message has no clean text (e.g. while thinking or executing tools), don't show empty bubble
						if (!renderedContent) return null;

						return (
							<div
								key={message.id}
								className={`flex gap-3 ${isUser ? 'justify-end' : 'justify-start'}`}
							>
								{!isUser && (
									<div
										className="w-8 h-8 rounded-xl bg-[#1b0e35] text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-xs">
										AI
									</div>
								)}
								<div
									className={`max-w-[85%] rounded-2xl p-4 text-sm shadow-2xs ${
										isUser
											? 'bg-[#1b0e35] text-white rounded-br-none'
											: 'bg-slate-100/80 text-gray-800 border border-slate-200/60 rounded-bl-none'
									}`}
								>
									<div className="leading-relaxed">
										{renderedContent.map((item) => (
											<div>
												{item}
											</div>
										))}
									</div>
								</div>
								{isUser && (
									<div
										className="w-8 h-8 rounded-xl bg-purple-100 text-purple-900 flex items-center justify-center shrink-0 text-xs font-bold shadow-xs border border-purple-200">
										TÚ
									</div>
								)}
							</div>
						);
					})}
					{isLoading && (
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
			<form
				onSubmit={(e) => {
					e.preventDefault();
					if (input.trim()) {
						sendMessage({text: input.trim()}).then();
						setInput('');
					}
				}}
				className="space-y-4">
				<textarea
					value={input}
					onChange={(e) => setInput(e.target.value)}
					placeholder={t('agent_placeholder') !== 'agent_placeholder' ? t('agent_placeholder') : 'Consulta dudas sobre tu presupuesto o añade gastos...'}
					rows={3}
					className="w-full rounded-2xl border border-gray-200 bg-slate-50/60 p-4 text-sm sm:text-base placeholder:text-gray-400 focus:ring-2 focus:ring-purple-900/20 focus:outline-none transition-all duration-200 resize-none shadow-2xs"
				/>

				<div className="flex flex-col sm:flex-row items-center justify-between gap-3">
					<button
						type="button"
						onClick={handleUploadClick}
						className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4.5 py-2.5 rounded-xl bg-white hover:bg-purple-50/70 text-purple-950 border border-purple-200/90 font-bold text-xs sm:text-sm shadow-2xs transition-all duration-200 active:scale-95 cursor-pointer"
					>
						<svg className="w-4 h-4 text-purple-900 shrink-0" fill="none" viewBox="0 0 24 24"
						     stroke="currentColor" strokeWidth="2">
							<path strokeLinecap="round" strokeLinejoin="round"
							      d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316A2.192 2.192 0 0014.49 4.25H9.51c-.69 0-1.325.356-1.69.948l-.993 1.623z"/>
							<path strokeLinecap="round" strokeLinejoin="round" d="M15 13.5a3 3 0 11-6 0 3 3 0 016 0z"/>
						</svg>
						<span>{t('agent_upload_ticket') !== 'agent_upload_ticket' ? t('agent_upload_ticket') : 'Subir Ticket'}</span>
					</button>

					<button
						type="submit"
						disabled={!input.trim() || isLoading}
						className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-[#1b0e35] hover:bg-[#28154e] text-white font-bold text-xs sm:text-sm shadow-md hover:shadow-lg transition-all duration-200 active:scale-95 cursor-pointer disabled:opacity-40 disabled:pointer-events-none"
					>
						<svg className="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
						     strokeWidth="2.5">
							<path strokeLinecap="round" strokeLinejoin="round" d="M6 12L3 21l19-9L3 3l3 9zm0 0h7"/>
						</svg>
						<span>{t('agent_consult') !== 'agent_consult' ? t('agent_consult') : 'Consultar'}</span>
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
}

