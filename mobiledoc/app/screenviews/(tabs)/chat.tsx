import React, { useEffect, useMemo, useRef, useState } from 'react';
import type { ReactNode } from 'react';
import {
  Animated,
  Pressable,
  SafeAreaView,
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import type { StyleProp, ViewStyle } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';

const T = {
  cyan500: '#06b6d4',
  cyan600: '#0891b2',
  cyan700: '#0e7490',
  slate50: '#f8fafc',
  slate100: '#f1f5f9',
  slate200: '#e2e8f0',
  slate300: '#cbd5e1',
  slate400: '#94a3b8',
  slate500: '#64748b',
  slate600: '#475569',
  slate700: '#334155',
  slate800: '#1e293b',
  slate900: '#0f172a',
  white: '#ffffff',
  red700: '#b91c1c',
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

type ConversationUser = {
  user_id: number;
  firstname?: string | null;
  middlename?: string | null;
  lastname?: string | null;
};

type ConversationItem = {
  conversation_id: number;
  user_id: number;
  messages_count?: number;
  user?: ConversationUser | null;
};

type MessageItem = {
  message_id: number;
  sender: 'user' | 'bot';
  message_text: string;
  created_at?: string;
};

type AnimatedCardProps = {
  children: ReactNode;
  delay?: number;
  style?: StyleProp<ViewStyle>;
};

function AnimatedCard({ children, delay = 0, style }: AnimatedCardProps) {
  const anim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    Animated.timing(anim, {
      toValue: 1,
      duration: 480,
      delay,
      useNativeDriver: true,
    }).start();
  }, [anim, delay]);

  return (
    <Animated.View
      style={[
        {
          opacity: anim,
          transform: [
            {
              translateY: anim.interpolate({
                inputRange: [0, 1],
                outputRange: [18, 0],
              }),
            },
          ],
        },
        style,
      ]}
    >
      {children}
    </Animated.View>
  );
}

type InfoCardProps = {
  icon: React.ComponentProps<typeof Ionicons>['name'];
  label: string;
  value: string;
  sub?: string;
  delay?: number;
};

function InfoCard({ icon, label, value, sub, delay = 0 }: InfoCardProps) {
  return (
    <AnimatedCard delay={delay} style={styles.infoCard}>
      <View style={styles.infoCardInner}>
        <View style={styles.infoCardTop}>
          <View style={styles.infoIconCircle}>
            <Ionicons name={icon} size={18} color={T.cyan700} />
          </View>
          <Text style={styles.infoLabel}>{label}</Text>
        </View>
        <View style={styles.infoCardBottom}>
          <Text style={styles.infoValue}>{value}</Text>
          <Text style={styles.infoSub}>{sub || '—'}</Text>
        </View>
      </View>
    </AnimatedCard>
  );
}

type SectionCardProps = {
  title: string;
  badge?: string;
  children: ReactNode;
  delay?: number;
  style?: StyleProp<ViewStyle>;
};

function SectionCard({ title, badge, children, delay = 0, style }: SectionCardProps) {
  return (
    <AnimatedCard delay={delay} style={[styles.sectionCard, style]}>
      <View style={styles.sectionHeader}>
        {badge ? (
          <View style={styles.sectionBadge}>
            <Text style={styles.sectionBadgeText}>{badge}</Text>
          </View>
        ) : null}
        <Text style={styles.sectionTitle}>{title}</Text>
      </View>
      <View style={styles.sectionBody}>{children}</View>
    </AnimatedCard>
  );
}

function formatConversationName(user?: ConversationUser | null, fallbackId?: number) {
  const parts = [user?.firstname, user?.middlename, user?.lastname]
    .map((part) => String(part ?? '').trim())
    .filter(Boolean);
  if (parts.length > 0) {
    return parts.join(' ');
  }
  return fallbackId ? `Patient #${fallbackId}` : 'Patient';
}

function formatTimeLabel(value?: string) {
  if (!value) return 'Just now';
  const dt = new Date(value);
  if (Number.isNaN(dt.getTime())) return 'Just now';
  return dt.toLocaleString([], {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

export default function ChatScreen() {
  const router = useRouter();
  const messageScrollRef = useRef<ScrollView | null>(null);
  const [conversations, setConversations] = useState<ConversationItem[]>([]);
  const [selectedConversationId, setSelectedConversationId] = useState<number | null>(null);
  const [messages, setMessages] = useState<MessageItem[]>([]);
  const [draft, setDraft] = useState('');
  const [loadingConversations, setLoadingConversations] = useState(false);
  const [loadingMessages, setLoadingMessages] = useState(false);
  const [sending, setSending] = useState(false);
  const [error, setError] = useState('');

  const selectedConversation = useMemo(
    () => conversations.find((item) => item.conversation_id === selectedConversationId) ?? null,
    [conversations, selectedConversationId]
  );

  async function loadConversations(selectId?: number | null) {
    const token = (globalThis as any)?.apiToken as string | undefined;
    if (!token) {
      setError('Please log in again.');
      return;
    }

    setLoadingConversations(true);
    try {
      const response = await fetch(`${API_BASE_URL}/conversations?per_page=50`, {
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        const message =
          typeof data?.message === 'string' && data.message.length > 0
            ? data.message
            : 'Unable to load conversations.';
        setError(message);
        return;
      }

      const list: ConversationItem[] = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];

      if (list.length === 0) {
        const createResponse = await fetch(`${API_BASE_URL}/conversations`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
          body: JSON.stringify({}),
        });

        const created = await createResponse.json().catch(() => ({}));
        if (createResponse.ok && created?.conversation_id) {
          const createdConversation = created as ConversationItem;
          setConversations([createdConversation]);
          setSelectedConversationId(createdConversation.conversation_id);
          setError('');
          return;
        }
      }

      setConversations(list);
      setSelectedConversationId((current) => {
        if (selectId && list.some((item) => item.conversation_id === selectId)) return selectId;
        if (current && list.some((item) => item.conversation_id === current)) return current;
        return list[0]?.conversation_id ?? null;
      });
      setError('');
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setLoadingConversations(false);
    }
  }

  async function loadMessages(conversationId: number) {
    const token = (globalThis as any)?.apiToken as string | undefined;
    if (!token) {
      setError('Please log in again.');
      return;
    }

    setLoadingMessages(true);
    try {
      const response = await fetch(`${API_BASE_URL}/conversations/${conversationId}/messages?per_page=100`, {
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        const message =
          typeof data?.message === 'string' && data.message.length > 0
            ? data.message
            : 'Unable to load messages.';
        setError(message);
        return;
      }

      const raw: MessageItem[] = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];
      setMessages([...raw].reverse());
      setError('');
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setLoadingMessages(false);
    }
  }

  async function handleSend() {
    const trimmed = draft.trim();
    if (!trimmed || !selectedConversationId || sending) return;

    const token = (globalThis as any)?.apiToken as string | undefined;
    if (!token) {
      setError('Please log in again.');
      return;
    }

    setSending(true);
    try {
      const response = await fetch(`${API_BASE_URL}/conversations/${selectedConversationId}/messages`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ message_text: trimmed }),
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        const message =
          typeof data?.message === 'string' && data.message.length > 0
            ? data.message
            : 'Unable to send message.';
        setError(message);
        return;
      }

      setDraft('');
      await loadMessages(selectedConversationId);
      await loadConversations(selectedConversationId);
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setSending(false);
    }
  }

  useEffect(() => {
    loadConversations();
  }, []);

  useEffect(() => {
    if (!selectedConversationId) {
      setMessages([]);
      return;
    }

    loadMessages(selectedConversationId);
    const intervalId = setInterval(() => {
      loadMessages(selectedConversationId);
    }, 8000);

    return () => {
      clearInterval(intervalId);
    };
  }, [selectedConversationId]);

  useEffect(() => {
    requestAnimationFrame(() => {
      messageScrollRef.current?.scrollToEnd({ animated: true });
    });
  }, [messages]);

  const selectedName = selectedConversation
    ? formatConversationName(selectedConversation.user, selectedConversation.user_id)
    : 'No conversation selected';

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" backgroundColor={T.cyan700} />
      <ScrollView style={styles.pageScroll} contentContainerStyle={styles.pageScrollContent} showsVerticalScrollIndicator={false}>
        <View style={styles.headerBackgroundFill} />
        <View style={styles.header}>
          <View style={styles.circleTopRight} />
          <View style={styles.circleBottomLeft} />
          <View style={styles.circleMidLeft} />
          <View style={styles.headerRow}>
            <View>
            
 <View style={styles.eyebrowRow}>
              <View style={[styles.eyebrowDot, { backgroundColor: 'rgba(255,255,255,0.7)' }]} />
              <Text style={[styles.eyebrowText, { color: 'rgba(255,255,255,0.8)' }]}>Patient Portal</Text>
            </View>

              <Text style={styles.headerTitle}>Messages</Text>
              <Text style={styles.headerGreeting}>Chat with the receptionist about appointments, queue updates, and clinic concerns.</Text>
            </View>
            <Pressable style={({ pressed }) => [styles.headerBtn, pressed && { opacity: 0.85 }]} onPress={() => router.replace('/screenviews/(tabs)' as any)}>
              <Text style={styles.headerBtnText}>Back</Text>
            </Pressable>
          </View>
        </View>

        <View style={styles.contentSurface}>
          {error ? <Text style={styles.inlineError}>{error}</Text> : null}

          <View style={styles.infoRow}>
            <InfoCard
              icon="chatbubble-ellipses-outline"
              label="Conversations"
              value={String(conversations.length)}
              sub={loadingConversations ? 'Loading chat threads' : 'Available chat threads'}
              delay={30}
            />
            <InfoCard
              icon="person-outline"
              label="Selected thread"
              value={selectedConversation ? selectedName : '—'}
              sub={selectedConversation ? `Conversation #${selectedConversation.conversation_id}` : 'Open or create a thread'}
              delay={60}
            />
            <InfoCard
              icon="mail-outline"
              label="Messages"
              value={String(messages.length)}
              sub={loadingMessages ? 'Syncing messages' : 'Messages in current thread'}
              delay={90}
            />
          </View>

          <SectionCard title="Conversations" badge="Inbox" delay={120}>
            {loadingConversations && conversations.length === 0 ? (
              <Text style={styles.mutedText}>Loading conversations…</Text>
            ) : conversations.length === 0 ? (
              <Text style={styles.mutedText}>No conversations available yet.</Text>
            ) : (
              conversations.map((conversation) => {
                const isActive = conversation.conversation_id === selectedConversationId;
                return (
                  <Pressable
                    key={conversation.conversation_id}
                    onPress={() => setSelectedConversationId(conversation.conversation_id)}
                    style={({ pressed }) => [
                      styles.conversationRow,
                      isActive && styles.conversationRowActive,
                      pressed && { opacity: 0.85 },
                    ]}
                  >
                    <View style={styles.rowIconWrap}>
                      <Ionicons name="person-outline" size={18} color={T.cyan700} />
                    </View>
                    <View style={styles.rowMain}>
                      <Text style={styles.rowTitle}>{formatConversationName(conversation.user, conversation.user_id)}</Text>
                      <Text style={styles.rowSubtitle}>{`Conversation #${conversation.conversation_id}`}</Text>
                    </View>
                    <View style={styles.countPill}>
                      <Text style={styles.countPillText}>{String(conversation.messages_count ?? 0)}</Text>
                    </View>
                  </Pressable>
                );
              })
            )}
          </SectionCard>

          <SectionCard title="Chat" badge="Messages" delay={160} style={{ marginBottom: 24 }}>
            {selectedConversation ? (
              <>
                <View style={styles.chatHeader}>
                  <Text style={styles.chatTitle}>{selectedName}</Text>
                  <Text style={styles.chatMeta}>{`Conversation #${selectedConversation.conversation_id}`}</Text>
                </View>

                <ScrollView ref={messageScrollRef as any} style={styles.messageScroll} contentContainerStyle={styles.messageContent}>
                  {loadingMessages && messages.length === 0 ? (
                    <Text style={styles.mutedText}>Loading messages…</Text>
                  ) : messages.length === 0 ? (
                    <Text style={styles.mutedText}>No messages yet. Start the conversation below.</Text>
                  ) : (
                    messages.map((message) => (
                      <View
                        key={message.message_id}
                        style={[
                          styles.bubbleRow,
                          message.sender === 'user' ? styles.bubbleRowUser : styles.bubbleRowBot,
                        ]}
                      >
                        <View style={[styles.bubble, message.sender === 'user' ? styles.bubbleUser : styles.bubbleBot]}>
                          <Text style={[styles.bubbleText, message.sender === 'user' ? styles.bubbleTextUser : styles.bubbleTextBot]}>
                            {message.message_text}
                          </Text>
                          <Text style={[styles.bubbleTime, message.sender === 'user' ? styles.bubbleTimeUser : styles.bubbleTimeBot]}>
                            {formatTimeLabel(message.created_at)}
                          </Text>
                        </View>
                      </View>
                    ))
                  )}
                </ScrollView>

                <View style={styles.composeRow}>
                  <TextInput
                    value={draft}
                    onChangeText={setDraft}
                    placeholder="Type your message"
                    placeholderTextColor={T.slate400}
                    multiline
                    style={styles.composeInput}
                  />
                  <Pressable
                    onPress={handleSend}
                    disabled={!draft.trim() || sending}
                    style={({ pressed }) => [
                      styles.sendBtn,
                      (!draft.trim() || sending) && { opacity: 0.6 },
                      pressed && { opacity: 0.85 },
                    ]}
                  >
                    <Ionicons name="send" size={16} color={T.white} />
                  </Pressable>
                </View>
              </>
            ) : (
              <Text style={styles.mutedText}>Select a conversation to view and send messages.</Text>
            )}
          </SectionCard>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: T.cyan700,
  },
  headerBackgroundFill: {
    backgroundColor: T.cyan700,
    position: 'absolute',
    top: -1000,
    left: 0,
    right: 0,
    height: 1000,
  },
  header: {
    backgroundColor: T.cyan700,
    paddingHorizontal: 20,
    paddingTop: 10,
    paddingBottom: 20,
    position: 'relative',
    overflow: 'hidden',
  },
  circleTopRight: {
    position: 'absolute',
    top: -80,
    right: -80,
    width: 280,
    height: 280,
    borderRadius: 140,
    backgroundColor: 'rgba(255,255,255,0.08)',
  },
  circleBottomLeft: {
    position: 'absolute',
    bottom: -80,
    left: -60,
    width: 190,
    height: 190,
    borderRadius: 95,
    backgroundColor: 'rgba(255,255,255,0.07)',
  },
  circleMidLeft: {
    position: 'absolute',
    top: 30,
    left: -90,
    width: 180,
    height: 180,
    borderRadius: 90,
    backgroundColor: 'rgba(255,255,255,0.05)',
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    position: 'relative',
    zIndex: 1,
    gap: 12,
  },
  headerEyebrow: {
    fontSize: 9,
    fontWeight: '700',
    letterSpacing: 1.2,
    color: 'rgba(255,255,255,0.65)',
    marginBottom: 2,
  },
   eyebrowRow: { flexDirection: 'row', alignItems: 'center', gap: 5, marginBottom: 4 },
  eyebrowDot: { width: 6, height: 6, borderRadius: 3, backgroundColor: T.cyan500 },
  eyebrowText: { fontSize: 9, fontWeight: '700', letterSpacing: 0.9, textTransform: 'uppercase', color: T.cyan600 },
  headerTitle: {
    fontSize: 35,
    fontWeight: '800',
    fontFamily: 'serif',
    color: T.white,
    letterSpacing: 0.2,
    lineHeight: 34,
  },
  headerGreeting: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.75)',
    marginTop: 2,
    fontWeight: '400',
    maxWidth: 255,
  },
  headerBtn: {
    marginTop: 4,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: 'rgba(255,255,255,0.15)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.25)',
  },
  headerBtnText: {
    fontSize: 12,
    fontWeight: '700',
    color: T.white,
  },
  pageScroll: {
    flex: 1,
    backgroundColor: 'rgba(255,255,255,0.07)',
  },
  pageScrollContent: {
    flexGrow: 1,
  },
  contentSurface: {
    flex: 1,
    backgroundColor: T.slate100,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    paddingTop: 20,
    paddingHorizontal: 14,
    paddingBottom: 84,
  },
  inlineError: {
    fontSize: 12,
    color: T.red700,
    marginBottom: 10,
  },
  infoRow: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 18,
    alignItems: 'stretch',
  },
  infoCard: {
    flex: 1,
  },
  infoCardInner: {
    flex: 1,
    backgroundColor: T.white,
    borderRadius: 16,
    padding: 12,
    borderWidth: 1,
    borderColor: T.slate200,
    shadowColor: T.slate900,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 2,
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    minHeight: 142,
  },
  infoCardTop: {
    width: '100%',
  },
  infoCardBottom: {
    width: '100%',
  },
  infoIconCircle: {
    width: 36,
    height: 36,
    borderRadius: 10,
    backgroundColor: 'rgba(6,182,212,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 8,
  },
  infoLabel: {
    fontSize: 9,
    fontWeight: '600',
    color: T.slate400,
    letterSpacing: 0.2,
    marginBottom: 4,
    lineHeight: 12,
  },
  infoValue: {
    fontSize: 13,
    fontWeight: '800',
    color: T.cyan700,
    lineHeight: 15,
    marginBottom: 2,
  },
  infoSub: {
    fontSize: 9,
    color: T.slate500,
    lineHeight: 13,
  },
  sectionCard: {
    backgroundColor: T.white,
    borderRadius: 18,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: T.slate200,
    shadowColor: T.slate900,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
    overflow: 'hidden',
  },
  sectionHeader: {
    paddingHorizontal: 16,
    paddingTop: 14,
    paddingBottom: 10,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    borderBottomWidth: 1,
    borderBottomColor: T.slate100,
  },
  sectionBadge: {
    backgroundColor: 'rgba(6,182,212,0.1)',
    borderRadius: 6,
    paddingHorizontal: 8,
    paddingVertical: 2,
  },
  sectionBadgeText: {
    fontSize: 9,
    fontWeight: '800',
    color: T.cyan700,
    letterSpacing: 0.5,
    textTransform: 'uppercase',
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: T.slate800,
  },
  sectionBody: {
    paddingBottom: 4,
  },
  conversationRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: T.slate100,
  },
  conversationRowActive: {
    backgroundColor: T.slate50,
  },
  rowIconWrap: {
    width: 34,
    height: 34,
    borderRadius: 10,
    backgroundColor: T.slate100,
    alignItems: 'center',
    justifyContent: 'center',
    flexShrink: 0,
  },
  rowMain: {
    flex: 1,
  },
  rowTitle: {
    fontSize: 12,
    fontWeight: '600',
    color: T.slate800,
    marginBottom: 2,
  },
  rowSubtitle: {
    fontSize: 10,
    color: T.slate500,
    lineHeight: 14,
  },
  countPill: {
    minWidth: 28,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 999,
    backgroundColor: 'rgba(6,182,212,0.10)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  countPillText: {
    fontSize: 10,
    fontWeight: '700',
    color: T.cyan700,
  },
  chatHeader: {
    paddingHorizontal: 16,
    paddingTop: 14,
    paddingBottom: 10,
    borderBottomWidth: 1,
    borderBottomColor: T.slate100,
  },
  chatTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: T.slate800,
  },
  chatMeta: {
    marginTop: 2,
    fontSize: 10,
    color: T.slate500,
  },
  messageScroll: {
    maxHeight: 420,
    minHeight: 280,
    backgroundColor: T.white,
  },
  messageContent: {
    padding: 14,
    gap: 10,
  },
  bubbleRow: {
    flexDirection: 'row',
  },
  bubbleRowBot: {
    justifyContent: 'flex-start',
  },
  bubbleRowUser: {
    justifyContent: 'flex-end',
  },
  bubble: {
    maxWidth: '86%',
    borderRadius: 16,
    borderWidth: 1,
    paddingHorizontal: 12,
    paddingVertical: 10,
  },
  bubbleBot: {
    backgroundColor: T.slate50,
    borderColor: T.slate200,
  },
  bubbleUser: {
    backgroundColor: T.cyan700,
    borderColor: T.cyan700,
  },
  bubbleText: {
    fontSize: 13,
    lineHeight: 18,
  },
  bubbleTextBot: {
    color: T.slate900,
  },
  bubbleTextUser: {
    color: T.white,
  },
  bubbleTime: {
    marginTop: 6,
    fontSize: 10,
  },
  bubbleTimeBot: {
    color: T.slate400,
  },
  bubbleTimeUser: {
    color: 'rgba(255,255,255,0.75)',
  },
  composeRow: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    gap: 10,
    paddingHorizontal: 14,
    paddingTop: 12,
    paddingBottom: 14,
    borderTopWidth: 1,
    borderTopColor: T.slate100,
    backgroundColor: T.white,
  },
  composeInput: {
    flex: 1,
    minHeight: 44,
    maxHeight: 120,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: T.slate200,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 13,
    color: T.slate900,
    backgroundColor: T.white,
  },
  sendBtn: {
    width: 44,
    height: 44,
    borderRadius: 12,
    backgroundColor: T.cyan700,
    alignItems: 'center',
    justifyContent: 'center',
  },
  mutedText: {
    paddingHorizontal: 16,
    paddingVertical: 16,
    fontSize: 12,
    color: T.slate500,
  },
});
