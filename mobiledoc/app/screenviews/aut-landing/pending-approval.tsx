import React, { useEffect, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  Pressable,
  SafeAreaView,
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { clearPersistedAuthSession, persistCurrentUser } from '@/lib/auth-storage';

const T = {
  cyan600: '#0891b2',
  cyan700: '#0e7490',
  slate50: '#f8fafc',
  slate100: '#f1f5f9',
  slate200: '#e2e8f0',
  slate400: '#94a3b8',
  slate500: '#64748b',
  slate700: '#334155',
  slate800: '#1e293b',
  slate900: '#0f172a',
  white: '#ffffff',
  red100: 'rgba(239,68,68,0.12)',
  red700: '#b91c1c',
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

export default function PendingApprovalScreen() {
  const router = useRouter();
  const [checking, setChecking] = useState(false);
  const [error, setError] = useState('');

  const token = (globalThis as any)?.apiToken as string | undefined;

  const canCheck = useMemo(() => Boolean(token) && !checking, [token, checking]);

  async function handleLogout() {
    try {
      const currentToken = (globalThis as any)?.apiToken as string | undefined;
      if (currentToken) {
        await fetch(`${API_BASE_URL}/logout`, {
          method: 'POST',
          headers: { Accept: 'application/json', Authorization: `Bearer ${currentToken}` },
        }).catch(() => null);
      }
    } finally {
      await clearPersistedAuthSession();
      router.replace('/screenviews/aut-landing/login-screen' as any);
    }
  }

  async function checkStatus() {
    if (!token) {
      router.replace('/screenviews/aut-landing/login-screen' as any);
      return;
    }

    setError('');
    setChecking(true);
    try {
      const response = await fetch(`${API_BASE_URL}/user`, {
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        const message = typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to check approval status.';
        setError(message);
        return;
      }

      const nextUser = { ...(globalThis as any)?.currentUser, ...data };
      await persistCurrentUser(nextUser);

      if (!(data as any)?.is_first_login) {
        router.replace('/screenviews/(tabs)' as any);
        return;
      }

      if (!(data as any)?.has_pending_verification) {
        router.replace('/screenviews/verify' as any);
      }
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setChecking(false);
    }
  }

  useEffect(() => {
    void checkStatus();
    const id = setInterval(() => {
      void checkStatus();
    }, 7000);
    return () => clearInterval(id);
  }, []);

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" backgroundColor={T.cyan700} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.hero}>
          <View style={styles.iconCircle}>
            <Ionicons name="hourglass-outline" size={26} color={T.cyan700} />
          </View>
          <Text style={styles.title}>Waiting for approval</Text>
          <Text style={styles.subtitle}>
            Your account setup was submitted. Please wait for the admin/receptionist to review and approve your account.
          </Text>
        </View>

        {error ? <Text style={styles.inlineError}>{error}</Text> : null}

        <View style={styles.card}>
          <Text style={styles.cardTitle}>Status</Text>
          <View style={styles.statusRow}>
            {checking ? <ActivityIndicator size="small" color={T.cyan700} /> : <Ionicons name="time-outline" size={18} color={T.slate500} />}
            <Text style={styles.statusText}>{checking ? 'Checking…' : 'Pending approval'}</Text>
          </View>

          <Pressable
            onPress={checkStatus}
            disabled={!canCheck}
            style={({ pressed }) => [
              styles.primaryButton,
              !canCheck && { opacity: 0.6 },
              pressed && canCheck && { opacity: 0.85 },
            ]}
          >
            <Text style={styles.primaryButtonText}>{checking ? 'Checking…' : 'Refresh status'}</Text>
            <Ionicons name="refresh" size={18} color={T.white} />
          </Pressable>

          <Pressable
            onPress={handleLogout}
            style={({ pressed }) => [styles.secondaryButton, pressed && { opacity: 0.85 }]}
          >
            <Text style={styles.secondaryButtonText}>Log out</Text>
          </Pressable>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: T.cyan700 },
  content: { flexGrow: 1, padding: 16, backgroundColor: T.slate50 },
  hero: {
    backgroundColor: T.white,
    borderWidth: 1,
    borderColor: T.slate200,
    borderRadius: 18,
    padding: 16,
    alignItems: 'center',
    gap: 10,
  },
  iconCircle: {
    width: 54,
    height: 54,
    borderRadius: 27,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'rgba(8,145,178,0.10)',
    borderWidth: 1,
    borderColor: 'rgba(8,145,178,0.25)',
  },
  title: { fontSize: 18, fontWeight: '800', color: T.slate900 },
  subtitle: { fontSize: 12, color: T.slate700, textAlign: 'center', lineHeight: 18 },
  inlineError: {
    marginTop: 12,
    backgroundColor: T.red100,
    borderColor: 'rgba(239,68,68,0.25)',
    borderWidth: 1,
    color: T.red700,
    padding: 10,
    borderRadius: 12,
    fontSize: 12,
  },
  card: {
    marginTop: 12,
    backgroundColor: T.white,
    borderWidth: 1,
    borderColor: T.slate200,
    borderRadius: 18,
    padding: 16,
    gap: 12,
  },
  cardTitle: { fontSize: 14, fontWeight: '800', color: T.slate900 },
  statusRow: { flexDirection: 'row', alignItems: 'center', gap: 10 },
  statusText: { fontSize: 12, color: T.slate700, fontWeight: '700' },
  primaryButton: {
    backgroundColor: T.cyan700,
    borderRadius: 14,
    paddingVertical: 12,
    paddingHorizontal: 14,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
    gap: 10,
  },
  primaryButtonText: { color: T.white, fontSize: 13, fontWeight: '800' },
  secondaryButton: {
    borderRadius: 14,
    paddingVertical: 12,
    paddingHorizontal: 14,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'rgba(100,116,139,0.10)',
    borderWidth: 1,
    borderColor: 'rgba(100,116,139,0.25)',
  },
  secondaryButtonText: { color: T.slate800, fontSize: 13, fontWeight: '800' },
});
