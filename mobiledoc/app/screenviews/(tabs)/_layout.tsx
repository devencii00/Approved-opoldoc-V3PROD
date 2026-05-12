import React, { useEffect, useMemo, useRef, useState } from 'react';
import {
  Modal, Pressable, ScrollView, StyleSheet, Text, TextInput, View,
} from 'react-native';
 import { Redirect, Tabs, usePathname } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
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

const USE_GLOBAL_CHATBOT_OVERLAY = true;

// ─── Reusable tab icon with active pill indicator ─────────────────────────────
type TabIconProps = {
  name: keyof typeof Ionicons.glyphMap;
  outlineName: keyof typeof Ionicons.glyphMap;
  label: string;
  focused: boolean;
  color: string;
};

function TabIcon({ name, outlineName, label, focused, color }: TabIconProps) {
  return (
    <View style={tabStyles.wrap}>
      {focused && <View style={[tabStyles.indicator, { backgroundColor: color }]} />}
      <Ionicons name={focused ? name : outlineName} size={22} color={color} />
      <Text style={[tabStyles.label, { color }]}>{label}</Text>
    </View>
  );
}

const tabStyles = StyleSheet.create({
  wrap: {
    alignItems: 'center',
    justifyContent: 'center',
    gap: 3,
    paddingTop: 10,
    position: 'relative',
    minWidth: 64,
  },

  indicator: {
    position: 'absolute',
    top: 0,
    width: 28,
    height: 3,
    borderBottomLeftRadius: 3,
    borderBottomRightRadius: 3,
  },

  label: {
    fontSize: 10,
    fontWeight: '600',
    letterSpacing: 0.2,
  },
});

export default function TabsLayout() {
  const user = (globalThis as any)?.currentUser as any | undefined;
  const isOnboarding = Boolean((globalThis as any)?.currentUser?.is_first_login);
  const hasPendingVerification = Boolean((globalThis as any)?.currentUser?.has_pending_verification);
  const pathname = usePathname();
  const [chatOpen, setChatOpen] = useState(false);
  const [chatLoading, setChatLoading] = useState(false);
  const [chatError, setChatError] = useState('');
  const [greeting, setGreeting] = useState('How can I help you today?');
  const [options, setOptions] = useState<ChatbotOption[]>([]);
  const [currentParentId, setCurrentParentId] = useState<number | null>(null);
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [freeText, setFreeText] = useState('');
  const scrollRef = useRef<ScrollView | null>(null);

  const currentOptions = useMemo(
    () => getChildChatbotOptions(options, currentParentId),
    [options, currentParentId]
  );

  function resetChat(nextGreeting?: string) {
    const greet = typeof nextGreeting === 'string' && nextGreeting.trim() ? nextGreeting.trim() : greeting;
    setMessages([{ id: `bot-greet-${Date.now()}`, from: 'bot', text: greet }]);
    setCurrentParentId(null);
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
    setMessages((prev) => [
      ...prev,
      { id: `user-${option.id}-${Date.now()}`, from: 'user' as const, text: optionText || 'Selected option' },
      ...(responseText ? [{ id: `bot-r-${option.id}-${Date.now()}`, from: 'bot' as const, text: responseText }] : []),
    ]);

    const hasChildren = options.some((item) => Number(item.parent_id ?? 0) === Number(option.id));
    setCurrentParentId(hasChildren ? Number(option.id) : null);
  }

  function sendFreeText() {
    const trimmed = freeText.trim();
    if (!trimmed) return;
    setFreeText('');
    setMessages((prev) => [
      ...prev,
      { id: `user-free-${Date.now()}`, from: 'user', text: trimmed },
      { id: `bot-free-${Date.now()}`, from: 'bot', text: 'Please select one of the suggested options so I can respond accurately.' },
    ]);
  }

  useEffect(() => {
    if (!chatOpen) return;
    ensureChatLoaded();
  }, [chatOpen]);

  useEffect(() => {
    if (!chatOpen) return;
    requestAnimationFrame(() => scrollRef.current?.scrollToEnd({ animated: true }));
  }, [messages, chatOpen]);

  if (user?.must_change_credentials) {
    return <Redirect href="/screenviews/aut-landing/first-login" />;
  }

  if (isOnboarding) {
    const onboardingAllowedPaths = new Set([
      '/screenviews/medical-bg',
      '/screenviews/verify',
    ]);

    if (hasPendingVerification) {
      return <Redirect href="/screenviews/aut-landing/pending-approval" />;
    }

    if (!onboardingAllowedPaths.has(pathname)) {
      return <Redirect href="/screenviews/aut-landing/fillup-info" />;
    }
  }

  return (
    <View style={styles.root}>
      <Tabs
        screenOptions={{
          headerShown: false,
          tabBarActiveTintColor: '#0891b2',
          tabBarInactiveTintColor: '#94a3b8',
          tabBarShowLabel: false, 
          tabBarStyle: isOnboarding
            ? { display: 'none' }
            : {
                backgroundColor: '#ffffff',
                borderTopWidth: 0,
                height: 64,
              },
          tabBarItemStyle: {
            height: 64,
            paddingVertical: 0,
            overflow: 'visible',
          },
        }}
      >
        {/* ── Dependents ── */}

           <Tabs.Screen
          name="index"
          options={{
            tabBarIcon: ({ color, focused }) => (
              <TabIcon name="home" outlineName="home-outline" label="Home" focused={focused} color={color} />
            ),
          }}
        />
        
        <Tabs.Screen
          name="dependents"
          options={{
            tabBarIcon: ({ color, focused }) => (
              <TabIcon
                name="people"
                outlineName="people-outline"
                label="Dependents"
                focused={focused}
                color={color}
              />
            ),
          }}
        />

     

        {/* ── Profile ── */}
        <Tabs.Screen
          name="profile"
          options={{
            tabBarIcon: ({ color, focused }) => (
              <TabIcon
                name="person"
                outlineName="person-outline"
                label="Profile"
                focused={focused}
                color={color}
              />
            ),
          }}
        />

        {/* Hidden screens */}
        <Tabs.Screen name="appointments" options={{ href: null, tabBarStyle: { display: 'none' } }} />
        <Tabs.Screen name="notifications" options={{ href: null, tabBarStyle: { display: 'none' } }} />
        <Tabs.Screen name="queue"        options={{ href: null, tabBarStyle: { display: 'none' } }} />
        <Tabs.Screen name="visits"       options={{ href: null , tabBarStyle: { display: 'none' } }} />
        <Tabs.Screen name="prescriptions" options={{ href: null , tabBarStyle: { display: 'none' } }} />
        <Tabs.Screen name="settings"     options={{ href: null, tabBarStyle: { display: 'none' } }} />
        <Tabs.Screen name="medical-bg"   options={{ href: null , tabBarStyle: { display: 'none' } }} />
         <Tabs.Screen name="records"   options={{ href: null , tabBarStyle: { display: 'none' } }} />
          <Tabs.Screen name="booking"   options={{ href: null , tabBarStyle: { display: 'none' } }} />
           <Tabs.Screen name="chat"   options={{ href: null, tabBarStyle: { display: 'none' } }} />
           <Tabs.Screen name="verify"   options={{ href: null, tabBarStyle: { display: 'none' } }} />
      </Tabs>

      {!USE_GLOBAL_CHATBOT_OVERLAY ? (
        <>
          <Pressable
            onPress={() => setChatOpen(true)}
            style={({ pressed }) => [styles.fab, pressed && { opacity: 0.85 }]}
          >
            <Ionicons name="chatbubbles-outline" size={22} color="#ffffff" />
          </Pressable>

          <Modal visible={chatOpen} transparent animationType="fade" onRequestClose={() => setChatOpen(false)}>
            <Pressable style={styles.modalBackdrop} onPress={() => setChatOpen(false)}>
              <View />
            </Pressable>
            <View style={styles.sheet}>
              <View style={styles.sheetHeader}>
                <View style={styles.sheetTitleRow}>
                  <Ionicons name="sparkles-outline" size={18} color="#0e7490" />
                  <Text style={styles.sheetTitle}>Clinic Assistant</Text>
                </View>
                <View style={styles.sheetHeaderActions}>
                  <Pressable onPress={() => resetChat()} style={({ pressed }) => [styles.headerBtn, pressed && { opacity: 0.75 }]}>
                    <Text style={styles.headerBtnText}>Restart</Text>
                  </Pressable>
                  <Pressable onPress={() => setChatOpen(false)} style={({ pressed }) => [styles.headerBtn, pressed && { opacity: 0.75 }]}>
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
                    {chatError ? <Text style={styles.errorText}>{chatError}</Text> : null}
                  </ScrollView>

                  <View style={styles.optionsWrap}>
                    {currentOptions.length > 0 ? (
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
                    <View style={styles.freeTextRow}>
                      <TextInput
                        value={freeText}
                        onChangeText={setFreeText}
                        placeholder="Type a question (optional)"
                        placeholderTextColor="#94a3b8"
                        style={styles.freeTextInput}
                      />
                      <Pressable onPress={sendFreeText} style={({ pressed }) => [styles.sendBtn, pressed && { opacity: 0.85 }]}>
                        <Ionicons name="send" size={16} color="#ffffff" />
                      </Pressable>
                    </View>
                  </View>
                </>
              )}
            </View>
          </Modal>
        </>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  root: { flex: 1 },


  // ── Chatbot FAB + Modal ───────────────────────────────────────────────────
  fab: {
    position: 'absolute',
    right: 18,
    bottom: 88,
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
  bubble: { maxWidth: '86%', borderRadius: 14, paddingHorizontal: 12, paddingVertical: 10, borderWidth: 1 },
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
