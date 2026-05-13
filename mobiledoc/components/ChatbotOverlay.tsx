import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Ionicons } from '@expo/vector-icons';
import { useSegments } from 'expo-router';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Modal, Pressable, ScrollView, StyleSheet, Text, TextInput, View } from 'react-native';
import {
  fetchChatbotConfig,
  getChildChatbotOptions,
  type ChatbotOption,
} from '@/lib/chatbot';

type ChatMessage = {
  id: string;
  from: 'bot' | 'user';
  text: string;
};

export default function ChatbotOverlay() {
  const BOT_RESPONSE_DELAY_MS = 1000;
  const OPTIONS_REVEAL_DELAY_MS = 300;
  const insets = useSafeAreaInsets();
  const segments = useSegments();
  const isTabsRoute = (segments as string[]).includes('(tabs)');
  const isFirstLoginRoute = (segments as string[]).includes('aut-landing') && (segments as string[]).includes('first-login');



  const [chatOpen, setChatOpen] = useState(false);
  const [chatLoading, setChatLoading] = useState(false);
  const [chatError, setChatError] = useState('');
  const [greeting, setGreeting] = useState('How can I help you today?');
  const [options, setOptions] = useState<ChatbotOption[]>([]);
  const [currentParentId, setCurrentParentId] = useState<number | null>(null);
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [freeText, setFreeText] = useState('');
  const [showCurrentOptions, setShowCurrentOptions] = useState(true);
  const [botIsThinking, setBotIsThinking] = useState(false);
  const scrollRef = useRef<ScrollView | null>(null);
  const responseTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const optionsTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  const currentOptions = useMemo(
    () => getChildChatbotOptions(options, currentParentId),
    [options, currentParentId]
  );

  function clearPendingTimers() {
    if (responseTimerRef.current) {
      clearTimeout(responseTimerRef.current);
      responseTimerRef.current = null;
    }
    if (optionsTimerRef.current) {
      clearTimeout(optionsTimerRef.current);
      optionsTimerRef.current = null;
    }
  }

  function revealOptionsWithDelay() {
    if (optionsTimerRef.current) {
      clearTimeout(optionsTimerRef.current);
    }
    optionsTimerRef.current = setTimeout(() => {
      setShowCurrentOptions(true);
      optionsTimerRef.current = null;
    }, OPTIONS_REVEAL_DELAY_MS);
  }

  function resetChat(nextGreeting?: string) {
    clearPendingTimers();
    const greet = typeof nextGreeting === 'string' && nextGreeting.trim() ? nextGreeting.trim() : greeting;
    setMessages([{ id: `bot-greet-${Date.now()}`, from: 'bot', text: greet }]);
    setCurrentParentId(null);
    setBotIsThinking(false);
    setShowCurrentOptions(true);
  }

  async function ensureChatLoaded() {
    if (options.length > 0) return;

    setChatLoading(true);
    setChatError('');
    try {
      const config = await fetchChatbotConfig();
      setGreeting(config.greeting);
      setOptions(config.options);
      resetChat(config.greeting);
    } catch (err) {
      const msg = err instanceof Error && err.message ? err.message : 'Network error. Please try again.';
      setChatError(msg);
      setMessages([{ id: 'bot-load-fail', from: 'bot', text: msg }]);
      setCurrentParentId(null);
    } finally {
      setChatLoading(false);
    }
  }

  function pickOption(option: ChatbotOption) {
    const optionText = String(option.button_text ?? '').trim();
    const responseText = String(option.response_text ?? '').trim();
    const ts = Date.now();
    const hasChildren = options.some((item) => Number(item.parent_id ?? 0) === Number(option.id));
    const nextParentId = hasChildren ? Number(option.id) : null;

    clearPendingTimers();
    setMessages((prev) => [
      ...prev,
      { id: `user-${option.id}-${ts}`, from: 'user' as const, text: optionText || 'Selected option' },
    ]);
    setShowCurrentOptions(false);
    setBotIsThinking(true);
    setCurrentParentId(null);

    responseTimerRef.current = setTimeout(() => {
      if (responseText) {
        setMessages((prev) => [
          ...prev,
          { id: `bot-r-${option.id}-${ts}`, from: 'bot' as const, text: responseText },
        ]);
      }
      setBotIsThinking(false);
      setCurrentParentId(nextParentId);
      revealOptionsWithDelay();
      responseTimerRef.current = null;
    }, BOT_RESPONSE_DELAY_MS);
  }

  function sendFreeText() {
    const trimmed = freeText.trim();
    if (!trimmed) return;
    setFreeText('');
    clearPendingTimers();
    setMessages((prev) => [
      ...prev,
      { id: `user-free-${Date.now()}`, from: 'user', text: trimmed },
    ]);
    setShowCurrentOptions(false);
    setBotIsThinking(true);
    responseTimerRef.current = setTimeout(() => {
      setMessages((prev) => [
        ...prev,
        {
          id: `bot-free-${Date.now()}`,
          from: 'bot',
          text: 'Please select one of the suggested options so I can respond accurately.',
        },
      ]);
      setBotIsThinking(false);
      revealOptionsWithDelay();
      responseTimerRef.current = null;
    }, BOT_RESPONSE_DELAY_MS);
  }

  useEffect(() => {
    if (!chatOpen) return;
    ensureChatLoaded();
  }, [chatOpen]);

  useEffect(() => {
    if (!chatOpen) return;
    requestAnimationFrame(() => {
      scrollRef.current?.scrollToEnd({ animated: true });
    });
  }, [messages, chatOpen, botIsThinking]);

  useEffect(() => {
    return () => {
      clearPendingTimers();
    };
  }, []);

  const fabBottom = insets.bottom + (isTabsRoute ? 92 : 24);
  const hideOverlay = isFirstLoginRoute;

  return (
    <>
      {hideOverlay ? null : (
      <>
      <Pressable
        onPress={() => setChatOpen(true)}
        style={({ pressed, hovered }) => [
          styles.fab,
          { bottom: fabBottom },
          hovered && styles.fabHovered,
          pressed && styles.fabPressed,
        ]}
      >
        <Ionicons name="chatbubbles-outline" size={22} color="#ffffff" />
      </Pressable>

      <Modal visible={chatOpen} transparent animationType="fade" onRequestClose={() => setChatOpen(false)}>
        <Pressable style={styles.modalBackdrop} onPress={() => setChatOpen(false)}>
          <View />
        </Pressable>
        <View style={[styles.sheet, { paddingBottom: Math.max(insets.bottom, 10) }]}>
          <View style={styles.sheetHeader}>
            <View style={styles.sheetTitleRow}>
              <Ionicons name="sparkles-outline" size={18} color="#0e7490" />
              <Text style={styles.sheetTitle}>Clinic Assistant</Text>
            </View>
            <View style={styles.sheetHeaderActions}>
              <Pressable
                onPress={() => resetChat()}
                style={({ pressed }) => [styles.headerBtn, pressed && { opacity: 0.75 }]}
              >
                <Text style={styles.headerBtnText}>Restart</Text>
              </Pressable>
              <Pressable
                onPress={() => setChatOpen(false)}
                style={({ pressed }) => [styles.headerBtn, pressed && { opacity: 0.75 }]}
              >
                <Text style={styles.headerBtnText}>Close</Text>
              </Pressable>
            </View>
          </View>

          {chatLoading ? (
            <View style={styles.center}>
              <Text style={styles.mutedText}>Loading…</Text>
            </View>
          ) : (
            <>
              <ScrollView ref={scrollRef as any} style={styles.chatScroll} contentContainerStyle={styles.chatContent}>
                {messages.map((m) => (
                  <View key={m.id} style={[styles.bubbleRow, m.from === 'user' ? styles.bubbleRowUser : styles.bubbleRowBot]}>
                    <View style={[styles.bubble, m.from === 'user' ? styles.bubbleUser : styles.bubbleBot]}>
                      <Text style={[styles.bubbleText, m.from === 'user' ? styles.bubbleTextUser : styles.bubbleTextBot]}>
                        {m.text}
                      </Text>
                    </View>
                  </View>
                ))}
                {botIsThinking ? (
                  <View style={[styles.bubbleRow, styles.bubbleRowBot]}>
                    <View style={[styles.bubble, styles.bubbleBot]}>
                      <Text style={[styles.bubbleText, styles.bubbleTextBot]}>Thinking....</Text>
                    </View>
                  </View>
                ) : null}
                {chatError ? <Text style={styles.errorText}>{chatError}</Text> : null}
              </ScrollView>

              <View style={styles.optionsWrap}>
                {!showCurrentOptions ? (
                  <View style={styles.optionRow}>
                    <Text style={styles.mutedText}>Preparing options...</Text>
                  </View>
                ) : currentOptions.length > 0 ? (
                  <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.optionRow}>
                    {currentOptions.map((o) => (
                      <Pressable
                        key={o.id}
                        onPress={() => pickOption(o)}
                        style={({ pressed }) => [styles.optionChip, pressed && { opacity: 0.85 }]}
                      >
                        <Text style={styles.optionChipText}>{String(o.button_text ?? '')}</Text>
                      </Pressable>
                    ))}
                  </ScrollView>
                ) : (
                  <View style={styles.optionRow}>
                    <Text style={styles.mutedText}>No more options.</Text>
                  </View>
                )}

              
              </View>
            </>
          )}
        </View>
      </Modal>
      </>
      )}
    </>
  );
}

const styles = StyleSheet.create({
  fab: {
    position: 'absolute',
    right: 18,
    width: 54,
    height: 54,
    borderRadius: 27,
    backgroundColor: '#0e7490',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.35)',
    shadowColor: '#0f172a',
    shadowOpacity: 0.15,
    shadowOffset: { width: 0, height: 6 },
    shadowRadius: 10,
    elevation: 6,
    zIndex: 60,
    opacity: 0.45,
  },
  fabHovered: {
    opacity: 0.82,
    backgroundColor: 'rgba(14,116,144,0.96)',
    transform: [{ scale: 1.04 }],
  },
  fabPressed: {
    opacity: 0.92,
  },
  modalBackdrop: { ...StyleSheet.absoluteFillObject, backgroundColor: 'rgba(15,23,42,0.45)' },
  sheet: {
    position: 'absolute',
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: '#ffffff',
    borderTopLeftRadius: 18,
    borderTopRightRadius: 18,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    maxHeight: '80%',
    overflow: 'hidden',
  },
  sheetHeader: {
    paddingHorizontal: 14,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#e2e8f0',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
  },
  sheetTitleRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  sheetTitle: { fontSize: 14, fontWeight: '700', color: '#0f172a' },
  sheetHeaderActions: { flexDirection: 'row', alignItems: 'center', gap: 10 },
  headerBtn: {
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 999,
    backgroundColor: '#f1f5f9',
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  headerBtnText: { fontSize: 12, fontWeight: '600', color: '#334155' },
  chatScroll: { flex: 1 },
  chatContent: { padding: 14, gap: 10 },
  bubbleRow: { flexDirection: 'row' },
  bubbleRowBot: { justifyContent: 'flex-start' },
  bubbleRowUser: { justifyContent: 'flex-end' },
  bubble: {
    maxWidth: '86%',
    borderRadius: 14,
    paddingHorizontal: 12,
    paddingVertical: 10,
    borderWidth: 1,
  },
  bubbleBot: { backgroundColor: '#f8fafc', borderColor: '#e2e8f0' },
  bubbleUser: { backgroundColor: '#0e7490', borderColor: '#0e7490' },
  bubbleText: { fontSize: 13, lineHeight: 18 },
  bubbleTextBot: { color: '#0f172a' },
  bubbleTextUser: { color: '#ffffff' },
  optionsWrap: { borderTopWidth: 1, borderTopColor: '#e2e8f0', padding: 12, gap: 10 },
  optionRow: { gap: 8 },
  optionChip: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: '#ecfeff',
    borderWidth: 1,
    borderColor: 'rgba(8,145,178,0.25)',
  },
  optionChipText: { fontSize: 12, fontWeight: '600', color: '#0e7490' },
  freeTextRow: { flexDirection: 'row', alignItems: 'center', gap: 10 },
  freeTextInput: {
    flex: 1,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 13,
    color: '#0f172a',
    backgroundColor: '#ffffff',
  },
  sendBtn: {
    width: 40,
    height: 40,
    borderRadius: 12,
    backgroundColor: '#0e7490',
    alignItems: 'center',
    justifyContent: 'center',
  },
  mutedText: { fontSize: 12, color: '#64748b' },
  errorText: { marginTop: 8, fontSize: 12, color: '#b91c1c' },
  center: { padding: 16, alignItems: 'center', justifyContent: 'center' },
});
