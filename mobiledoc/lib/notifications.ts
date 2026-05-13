const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

export type PatientNotification = {
  id: string;
  type: string;
  title: string;
  body: string;
  isRead: boolean;
  createdAt: string | null;
};

function titleFromType(type: string): string {
  const normalized = type.trim().toLowerCase();
  if (normalized === 'appointment') return 'Appointment Update';
  if (normalized === 'payment') return 'Payment Update';
  return 'System Update';
}

export function parseNotificationMessage(message: unknown, type: unknown) {
  const safeMessage = typeof message === 'string' ? message.trim() : '';
  const safeType = typeof type === 'string' ? type : 'system';
  const bracketMatch = safeMessage.match(/^\[([^\]]+)\]\s*(.*)$/s);

  if (bracketMatch) {
    return {
      title: bracketMatch[1].trim(),
      body: bracketMatch[2].trim() || titleFromType(safeType),
    };
  }

  return {
    title: titleFromType(safeType),
    body: safeMessage || 'No additional details available.',
  };
}

export function mapPatientNotification(raw: any): PatientNotification {
  const type = typeof raw?.type === 'string' ? raw.type : 'system';
  const parsed = parseNotificationMessage(raw?.message, type);

  return {
    id: String(raw?.notification_id ?? raw?.id ?? ''),
    type,
    title: parsed.title,
    body: parsed.body,
    isRead: Boolean(raw?.is_read),
    createdAt: typeof raw?.created_at === 'string' ? raw.created_at : null,
  };
}

export function formatNotificationTimestamp(value: string | null): string {
  if (!value) return 'Recently';

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return 'Recently';

  return date.toLocaleString([], {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
}

export async function fetchPatientNotifications(token: string, perPage = 10): Promise<PatientNotification[]> {
  const response = await fetch(`${API_BASE_URL}/notifications?per_page=${perPage}`, {
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${token}`,
    },
  });

  const data = await response.json().catch(() => ({}));
  if (!response.ok) {
    const message = typeof data?.message === 'string' && data.message.trim().length > 0
      ? data.message
      : 'Unable to load notifications.';
    throw new Error(message);
  }

  const records = Array.isArray(data?.data) ? data.data : [];
  return records.map(mapPatientNotification).filter((item: PatientNotification) => item.id);
}

export async function markPatientNotificationsAsRead(token: string, notificationIds: string[]): Promise<void> {
  const uniqueIds = Array.from(new Set(notificationIds.filter((id) => typeof id === 'string' && id.trim().length > 0)));
  if (!uniqueIds.length) return;

  await Promise.all(
    uniqueIds.map(async (notificationId) => {
      const response = await fetch(`${API_BASE_URL}/notifications/${encodeURIComponent(notificationId)}`, {
        method: 'PATCH',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ is_read: true }),
      });

      if (!response.ok) {
        const data = await response.json().catch(() => ({}));
        const message = typeof data?.message === 'string' && data.message.trim().length > 0
          ? data.message
          : 'Unable to update notifications.';
        throw new Error(message);
      }
    }),
  );
}
