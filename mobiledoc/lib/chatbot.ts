const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

export type ChatbotOption = {
  id: number;
  parent_id: number | null;
  button_text: string;
  response_text: string | null;
  is_starting_option: boolean;
  sort_order: number;
};

export type ChatbotConfig = {
  greeting: string;
  options: ChatbotOption[];
};

export async function fetchChatbotConfig(): Promise<ChatbotConfig> {
  const res = await fetch(`${API_BASE_URL}/chatbot/config`, {
    headers: { Accept: 'application/json' },
  });

  const data = await res.json().catch(() => null);
  if (!res.ok) {
    const msg = typeof (data as any)?.message === 'string'
      ? (data as any).message
      : 'Unable to load chatbot.';
    throw new Error(msg);
  }

  const greeting = typeof (data as any)?.greeting === 'string'
    ? String((data as any).greeting)
    : 'How can I help you today?';
  const options = Array.isArray((data as any)?.options)
    ? ((data as any).options as ChatbotOption[])
    : [];

  return { greeting, options };
}

export function sortChatbotOptions(options: ChatbotOption[]): ChatbotOption[] {
  return [...options].sort((a, b) => {
    const aOrder = Number(a.sort_order) || 0;
    const bOrder = Number(b.sort_order) || 0;
    if (aOrder !== bOrder) return aOrder - bOrder;
    return Number(a.id) - Number(b.id);
  });
}

export function getStartingChatbotOptions(options: ChatbotOption[]): ChatbotOption[] {
  return sortChatbotOptions(
    options.filter((option) => option.parent_id == null && Boolean(option.is_starting_option))
  );
}

export function getChildChatbotOptions(options: ChatbotOption[], parentId: number | null): ChatbotOption[] {
  if (parentId == null) {
    return getStartingChatbotOptions(options);
  }

  const children = sortChatbotOptions(
    options.filter((option) => Number(option.parent_id ?? 0) === Number(parentId))
  );

  return children.length > 0 ? children : getStartingChatbotOptions(options);
}
