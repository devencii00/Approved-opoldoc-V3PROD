import React, { useEffect, useMemo, useRef, useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  Dimensions,
  Animated,
  StatusBar,
  Image,
  Platform,
  Modal,
  Pressable,
  ScrollView,
  TextInput,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';

const { width, height } = Dimensions.get('window');
const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

type ChatbotOption = {
  id: number;
  parent_id: number | null;
  button_text: string;
  response_text: string;
  is_starting_option: boolean;
  sort_order: number;
};

type ChatMessage = {
  id: string;
  from: 'bot' | 'user';
  text: string;
};

const USE_GLOBAL_CHATBOT_OVERLAY = true;

export default function HomeLanding() {
  const insets = useSafeAreaInsets();
  const router = useRouter();

  // Animations
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const slideAnim = useRef(new Animated.Value(40)).current;
  const logoScaleAnim = useRef(new Animated.Value(0.8)).current;
  const pulseAnim = useRef(new Animated.Value(1)).current;
  const btnSlideAnim = useRef(new Animated.Value(30)).current;
  const btnFadeAnim = useRef(new Animated.Value(0)).current;

  const [chatOpen, setChatOpen] = useState(false);
  const [chatLoading, setChatLoading] = useState(false);
  const [chatError, setChatError] = useState('');
  const [greeting, setGreeting] = useState('How can I help you today?');
  const [options, setOptions] = useState<ChatbotOption[]>([]);
  const [currentParentId, setCurrentParentId] = useState<number | null>(null);
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [freeText, setFreeText] = useState('');
  const scrollRef = useRef<ScrollView | null>(null);

  const startingOptions = useMemo(() => {
    return [...options]
      .filter((o) => o.parent_id == null && !!o.is_starting_option)
      .sort((a, b) => (Number(a.sort_order) || 0) - (Number(b.sort_order) || 0));
  }, [options]);

  const currentOptions = useMemo(() => {
    if (currentParentId == null) return startingOptions;
    const children = options
      .filter((o) => Number(o.parent_id ?? 0) === Number(currentParentId))
      .sort((a, b) => (Number(a.sort_order) || 0) - (Number(b.sort_order) || 0));
    return children.length > 0 ? children : startingOptions;
  }, [currentParentId, options, startingOptions]);

  function resetChat(nextGreeting?: string) {
    const greet = typeof nextGreeting === 'string' && nextGreeting.trim() ? nextGreeting.trim() : greeting;
    setMessages([{ id: `bot-greet-${Date.now()}`, from: 'bot', text: greet }]);
    setCurrentParentId(null);
  }

  async function ensureChatLoaded() {
    if (options.length > 0) return;
    const token = (globalThis as any)?.apiToken as string | undefined;
    if (!token) {
      setChatError('Please log in again.');
      setOptions([]);
      setMessages([{ id: 'bot-auth', from: 'bot', text: 'Please log in to use the chatbot.' }]);
      setCurrentParentId(null);
      return;
    }

    setChatLoading(true);
    setChatError('');
    try {
      const res = await fetch(`${API_BASE_URL}/chatbot/config`, {
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      });
      const data = await res.json().catch(() => (null));
      if (!res.ok) {
        const msg = typeof (data as any)?.message === 'string' ? (data as any).message : 'Unable to load chatbot.';
        setChatError(msg);
        setMessages([{ id: 'bot-load-fail', from: 'bot', text: msg }]);
        setCurrentParentId(null);
        return;
      }
      const greet = typeof (data as any)?.greeting === 'string' ? String((data as any).greeting) : 'How can I help you today?';
      const list: ChatbotOption[] = Array.isArray((data as any)?.options) ? ((data as any).options as ChatbotOption[]) : [];
      setGreeting(greet);
      setOptions(list);
      resetChat(greet);
    } catch {
      setChatError('Network error. Please try again.');
      setMessages([{ id: 'bot-net', from: 'bot', text: 'Network error. Please try again.' }]);
      setCurrentParentId(null);
    } finally {
      setChatLoading(false);
    }
  }

  function pickOption(option: ChatbotOption) {
    const optionText = String(option.button_text ?? '').trim();
    const responseText = String(option.response_text ?? '').trim();
    const ts = Date.now();
    setMessages((prev) => {
      const next: ChatMessage[] = [
        ...prev,
        { id: `user-${option.id}-${ts}`, from: 'user', text: optionText || 'Selected option' },
      ];
      if (responseText) {
        next.push({ id: `bot-r-${option.id}-${ts}`, from: 'bot', text: responseText });
      }
      return next;
    });

    const hasChildren = options.some((o) => Number(o.parent_id ?? 0) === Number(option.id));
    setCurrentParentId(hasChildren ? Number(option.id) : null);
  }

  function sendFreeText() {
    const trimmed = freeText.trim();
    if (!trimmed) return;
    setFreeText('');
    setMessages((prev) => [
      ...prev,
      { id: `user-free-${Date.now()}`, from: 'user', text: trimmed },
      {
        id: `bot-free-${Date.now()}`,
        from: 'bot',
        text: 'Please select one of the suggested options so I can respond accurately.',
      },
    ]);
  }

  useEffect(() => {
    // Entrance sequence
    Animated.sequence([
      Animated.parallel([
        Animated.timing(fadeAnim, {
          toValue: 1,
          duration: 700,
          useNativeDriver: true,
        }),
        Animated.spring(logoScaleAnim, {
          toValue: 1,
          friction: 6,
          tension: 80,
          useNativeDriver: true,
        }),
        Animated.timing(slideAnim, {
          toValue: 0,
          duration: 600,
          useNativeDriver: true,
        }),
      ]),
      Animated.parallel([
        Animated.timing(btnFadeAnim, {
          toValue: 1,
          duration: 500,
          useNativeDriver: true,
        }),
        Animated.timing(btnSlideAnim, {
          toValue: 0,
          duration: 500,
          useNativeDriver: true,
        }),
      ]),
    ]).start();

    // Pulse loop for logo ring
    Animated.loop(
      Animated.sequence([
        Animated.timing(pulseAnim, {
          toValue: 1.08,
          duration: 1500,
          useNativeDriver: true,
        }),
        Animated.timing(pulseAnim, {
          toValue: 1,
          duration: 1500,
          useNativeDriver: true,
        }),
      ])
    ).start();
  }, []);

  useEffect(() => {
    if (!chatOpen) return;
    ensureChatLoaded();
  }, [chatOpen]);

  useEffect(() => {
    if (!chatOpen) return;
    requestAnimationFrame(() => {
      scrollRef.current?.scrollToEnd({ animated: true });
    });
  }, [messages, chatOpen]);

  return (
    <View style={styles.root}>
      <StatusBar barStyle="light-content" translucent backgroundColor="transparent" />

      <LinearGradient
        colors={['#0891b2', '#0e7490', '#155e75']}
        start={{ x: 0.1, y: 0 }}
        end={{ x: 0.9, y: 1 }}
        style={StyleSheet.absoluteFill}
      />

      {/* Decorative circles */}
      <View style={styles.circleTopRight} />
      <View style={styles.circleBottomLeft} />
      <View style={styles.circleMidLeft} />

      {/* Content */}
      <View style={[styles.container, { paddingTop: insets.top + 32, paddingBottom: insets.bottom + 32 }]}>

        {/* Top badge */}
        <Animated.View style={[styles.badgeRow, { opacity: fadeAnim, transform: [{ translateY: slideAnim }] }]}>
          <View style={styles.badge}>
            <Text style={styles.badgeText}>General Medicine</Text>
          </View>
          <View style={styles.badge}>
            <Text style={styles.badgeText}>Patient Care</Text>
          </View>
        </Animated.View>

        {/* Logo section */}
        <Animated.View
          style={[
            styles.logoWrapper,
            {
              opacity: fadeAnim,
              transform: [{ scale: logoScaleAnim }],
            },
          ]}
        >
          <Animated.View style={[styles.logoPulseRing, { transform: [{ scale: pulseAnim }] }]}>
            <View style={styles.logoRing}>
              {/* Replace with actual <Image> when asset is available */}
              <View style={styles.logoPlaceholder}>
              
              </View>
              {
              <Image
               source={require('../../../assets/images/docfiles/opoldoc.png')}
                style={styles.logoImage}
                resizeMode="contain"
              /> }
            </View>
          </Animated.View>
        </Animated.View>

        {/* Clinic name */}
        <Animated.View style={[styles.nameBlock, { opacity: fadeAnim, transform: [{ translateY: slideAnim }] }]}>
          <Text style={styles.tagline}>TRUSTED HEALTHCARE SINCE</Text>
          <Text style={styles.clinicName}>Opol Doctors{'\n'}Medical Clinic</Text>
          <View style={styles.dividerLine} />
          <Text style={styles.clinicSubtitle}>Your health, our commitment</Text>
        </Animated.View>

        {/* Spacer */}
        <View style={{ flex: 1 }} />

        {/* Buttons */}
        <Animated.View style={[styles.buttonsBlock, { opacity: btnFadeAnim, transform: [{ translateY: btnSlideAnim }] }]}>

          <TouchableOpacity
            style={styles.loginBtn}
            activeOpacity={0.85}
            onPress={() => router.push('/screenviews/aut-landing/login-screen')}
          >
            <LinearGradient
              colors={['rgba(255,255,255,0.22)', 'rgba(255,255,255,0.10)']}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 1 }}
              style={styles.loginBtnGradient}
            >
              <Text style={styles.loginBtnText}>Log In</Text>
            </LinearGradient>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.createBtn}
            activeOpacity={0.85}
            onPress={() => router.push('/screenviews/aut-landing/create-account')}
          >
            <Text style={styles.createBtnText}>Create Account</Text>
          </TouchableOpacity>

          <Text style={styles.footerNote}>
            By continuing, you agree to our{' '}
            <Text style={styles.footerLink}>Terms</Text>
            {' '}and{' '}
            <Text style={styles.footerLink}>Privacy Policy</Text>
          </Text>
        </Animated.View>

      </View>

      {!USE_GLOBAL_CHATBOT_OVERLAY ? (
        <>
          <Pressable onPress={() => setChatOpen(true)} style={({ pressed }) => [styles.fab, pressed && { opacity: 0.85 }]}>
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
                        placeholderTextColor="rgba(148,163,184,0.9)"
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
  root: {
    flex: 1,
    backgroundColor: '#0891b2',
  },

  // Decorative circles
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
    bottom: -60,
    left: -60,
    width: 200,
    height: 200,
    borderRadius: 100,
    backgroundColor: 'rgba(255,255,255,0.07)',
  },
  circleMidLeft: {
    position: 'absolute',
    top: height * 0.38,
    left: -100,
    width: 220,
    height: 220,
    borderRadius: 110,
    backgroundColor: 'rgba(255,255,255,0.05)',
  },

  container: {
    flex: 1,
    alignItems: 'center',
    paddingHorizontal: 28,
  },

  // Badge row
  badgeRow: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 32,
  },
  badge: {
    backgroundColor: 'rgba(255,255,255,0.15)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.25)',
    borderRadius: 20,
    paddingHorizontal: 14,
    paddingVertical: 5,
  },
  badgeText: {
    color: 'rgba(255,255,255,0.85)',
    fontSize: 10,
    fontWeight: '500',
    letterSpacing: 0.5,
  },

  // Logo
  logoWrapper: {
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 28,
  },
  logoPulseRing: {
    width: 168,
    height: 168,
    borderRadius: 84,
    backgroundColor: 'rgba(255,255,255,0.08)',
    alignItems: 'center',
    justifyContent: 'center',
    ...Platform.select({
      ios: {
        shadowColor: '#fff',
        shadowOffset: { width: 0, height: 0 },
        shadowOpacity: 0.2,
        shadowRadius: 20,
      },
      android: {
        elevation: 8,
      },
    }),
  },
  logoRing: {
    width: 140,
    height: 140,
    borderRadius: 70,
    backgroundColor: 'rgba(255,255,255,0.12)',
    borderWidth: 1.5,
    borderColor: 'rgba(255,255,255,0.25)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  logoPlaceholder: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  logoPlaceholderText: {
    fontSize: 56,
  },
  logoImage: {
    width: 100,
    height: 100,
  },

  // Clinic name block
  nameBlock: {
    alignItems: 'center',
  },
  tagline: {
    color: 'rgba(255,255,255,0.65)',
    fontSize: 9,
    fontWeight: '600',
    letterSpacing: 2.5,
    textTransform: 'uppercase',
    marginBottom: 8,
  },
  clinicName: {
    color: '#ffffff',
    fontSize: 30,
    fontWeight: '700',
    textAlign: 'center',
    lineHeight: 36,
    letterSpacing: 0.3,
    fontFamily: Platform.OS === 'ios' ? 'Georgia' : 'serif',
  },
  dividerLine: {
    width: 48,
    height: 2,
    backgroundColor: 'rgba(255,255,255,0.35)',
    borderRadius: 1,
    marginVertical: 14,
  },
  clinicSubtitle: {
    color: 'rgba(255,255,255,0.60)',
    fontSize: 13,
    fontWeight: '400',
    letterSpacing: 0.4,
    fontStyle: 'italic',
  },

  // Buttons
  buttonsBlock: {
    width: '100%',
    alignItems: 'center',
    gap: 12,
  },
  loginBtn: {
    width: '100%',
    borderRadius: 16,
    overflow: 'hidden',
    borderWidth: 1.5,
    borderColor: 'rgba(255,255,255,0.4)',
    ...Platform.select({
      ios: {
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.15,
        shadowRadius: 10,
      },
      android: {
        elevation: 4,
      },
    }),
  },
  loginBtnGradient: {
    paddingVertical: 16,
    alignItems: 'center',
    justifyContent: 'center',
  },
  loginBtnText: {
    color: '#ffffff',
    fontSize: 15,
    fontWeight: '600',
    letterSpacing: 0.5,
    fontFamily: Platform.OS === 'ios' ? 'Georgia' : 'serif',
  },
  createBtn: {
    width: '100%',
    backgroundColor: '#ffffff',
    borderRadius: 16,
    paddingVertical: 16,
    alignItems: 'center',
    justifyContent: 'center',
    ...Platform.select({
      ios: {
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 6 },
        shadowOpacity: 0.18,
        shadowRadius: 12,
      },
      android: {
        elevation: 6,
      },
    }),
  },
  createBtnText: {
    color: '#0e7490',
    fontSize: 15,
    fontWeight: '700',
    letterSpacing: 0.5,
    fontFamily: Platform.OS === 'ios' ? 'Georgia' : 'serif',
  },
  footerNote: {
    color: 'rgba(255,255,255,0.45)',
    fontSize: 11,
    textAlign: 'center',
    marginTop: 4,
    lineHeight: 16,
  },
  footerLink: {
    color: 'rgba(255,255,255,0.75)',
    fontWeight: '600',
    textDecorationLine: 'underline',
  },

  fab: {
    position: 'absolute',
    right: 18,
    bottom: 22,
    width: 54,
    height: 54,
    borderRadius: 18,
    backgroundColor: '#0e7490',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.18)',
    ...Platform.select({
      ios: {
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 10 },
        shadowOpacity: 0.22,
        shadowRadius: 12,
      },
      android: { elevation: 8 },
    }),
  },
  modalBackdrop: {
    position: 'absolute',
    top: 0,
    right: 0,
    bottom: 0,
    left: 0,
    backgroundColor: 'rgba(15,23,42,0.45)',
  },
  sheet: {
    position: 'absolute',
    left: 14,
    right: 14,
    bottom: 14,
    top: height * 0.18,
    backgroundColor: '#ffffff',
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#e2e8f0',
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
    gap: 10,
  },
  sheetTitleRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  sheetTitle: { fontSize: 14, fontWeight: '700', color: '#0f172a' },
  sheetHeaderActions: { flexDirection: 'row', alignItems: 'center', gap: 10 },
  headerBtn: {
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 999,
    backgroundColor: '#f8fafc',
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
