import React from 'react';
import {useTranslation} from '@/hooks/useTranslation';

type ChatMessageItemProps = {
	message: {
		id: string;
		role: string;
		content?: string;
		parts?: Array<{ type: string; text?: string }>;
	};
};

export const ChatMessageItem = ({message}: ChatMessageItemProps) => {
	const {t} = useTranslation();
	const isUser = message.role === 'user';
	const textParts = (message.parts || []).filter((part) => part.type === 'text');
	const rawContent = message.content;
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
						<strong
							key={chunkIdx}
							className={isUser ? 'font-bold text-white' : 'font-bold text-gray-900'}
						>
							{chunk.slice(2, -2)}
						</strong>
					);
				}
				return chunk;
			});

			return (
				<React.Fragment key={lineIdx}>
					{formattedLine.map((item, itemIdx) => (
						<div key={itemIdx}>{item}</div>
					))}
					{lineIdx < lines.length - 1 && <br/>}
				</React.Fragment>
			);
		});
	};

	const fullText = textParts.length > 0
		? textParts.map((p) => p.text || '').join('\n')
		: rawContent || '';

	const renderedContent = renderFormattedText(fullText);

	if (!renderedContent) return null;

	return (
		<div className={`flex gap-3 ${isUser ? 'justify-end' : 'justify-start'}`}>
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
					{renderedContent.map((item, itemIdx) => (
						<div key={itemIdx}>{item}</div>
					))}
				</div>
			</div>
			{isUser && (
				<div
					className="w-8 h-8 rounded-xl bg-purple-100 text-purple-900 flex items-center justify-center shrink-0 text-xs font-bold shadow-xs border border-purple-200">
					{t('agent_user_avatar')}
				</div>
			)}
		</div>
	);
};
