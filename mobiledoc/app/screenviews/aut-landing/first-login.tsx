import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  StyleSheet,
  Pressable,
  ScrollView,
  StatusBar,
  SafeAreaView,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';

const T = {
  cyan700: '#0e7490',
  slate100: '#f1f5f9',
  slate200: '#e2e8f0',
  slate400: '#94a3b8',
  slate500: '#64748b',
  slate800: '#1e293b',
  white: '#ffffff',
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

export default function FirstLoginScreen() {
  const insets = useSafeAreaInsets();
  const router = useRouter();

  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');

  async function handleSetPassword() {
    if (!password || !confirmPassword) {
      setError('Please fill in all fields.');
      return;
    }

    if (password.length < 8) {
      setError('Password must be at least 8 characters.');
      return;
    }

    if (password !== confirmPassword) {
      setError('Passwords do not match.');
      return;
    }

    const token = (globalThis as any)?.apiToken as string | undefined;
    const currentUser = (globalThis as any)?.currentUser as any | undefined;

    if (!token || !currentUser?.user_id) {
      router.replace('/screenviews/aut-landing/login-screen');
      return;
    }

    setError('');
    setSubmitting(true);

    try {
      const response = await fetch(`${API_BASE_URL}/users/${currentUser.user_id}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          password,
          must_change_credentials: false,
        }),
      });

      let data: any = {};
      try {
        data = await response.json();
      } catch {
        data = {};
      }

      if (!response.ok) {
        const message =
          typeof data.message === 'string' && data.message.length > 0
            ? data.message
            : 'Unable to update password. Please try again.';
        setError(message);
        return;
      }

      (globalThis as any).currentUser = data;
      router.replace('/screenviews/(tabs)');
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <SafeAreaView
      style={[
        styles.safe,
        { paddingTop: insets.top, paddingBottom: insets.bottom > 0 ? insets.bottom : 12 },
      ]}
    >
      <StatusBar barStyle="light-content" backgroundColor={T.cyan700} />

      <View style={styles.header}>
        <View style={styles.headerInner}>
          <View>
            <View style={styles.eyebrowRow}>
              <View style={styles.eyebrowDot} />
              <Text style={styles.eyebrowText}>Patient Portal</Text>
            </View>
            <Text style={styles.headerTitle}>Set new password</Text>
            <Text style={styles.headerSub}>For security, please change your temporary password.</Text>
          </View>
        </View>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent} keyboardShouldPersistTaps="handled">
        <View style={styles.card}>
          <View style={styles.fieldGroup}>
            <Text style={styles.label}>New password</Text>
            <TextInput
              placeholder="Enter a strong password"
              placeholderTextColor="#9ca3af"
              secureTextEntry
              value={password}
              onChangeText={setPassword}
              style={styles.input}
            />
          </View>

          <View style={styles.fieldGroup}>
            <Text style={styles.label}>Confirm new password</Text>
            <TextInput
              placeholder="Re-enter your password"
              placeholderTextColor="#9ca3af"
              secureTextEntry
              value={confirmPassword}
              onChangeText={setConfirmPassword}
              style={styles.input}
            />
          </View>

          {error ? <Text style={styles.errorText}>{error}</Text> : null}

          <Pressable
            onPress={handleSetPassword}
            disabled={submitting}
            style={({ pressed }) => [styles.primaryButton, (pressed || submitting) && { opacity: 0.85 }]}
          >
            <Text style={styles.primaryButtonText}>{submitting ? 'Saving...' : 'Save Password'}</Text>
          </Pressable>
        </View>

        <Text style={styles.footerNote}>
          If you did not receive a temporary password, contact the clinic front desk.
        </Text>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: T.cyan700,
  },
  header: {
    backgroundColor: T.cyan700,
    paddingHorizontal: 20,
    paddingTop: 12,
    paddingBottom: 20,
  },
  headerInner: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
  },
  headerTitle: {
    fontFamily: 'serif',
    fontSize: 24,
    fontWeight: '700',
    color: T.white,
    marginBottom: 2,
    letterSpacing: 0.3,
  },
  headerSub: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.78)',
  },
  eyebrowRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    marginBottom: 4,
  },
  eyebrowDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: 'rgba(255,255,255,0.85)',
  },
  eyebrowText: {
    fontSize: 9,
    fontWeight: '700',
    letterSpacing: 0.9,
    textTransform: 'uppercase',
    color: 'rgba(255,255,255,0.85)',
  },
  scrollContent: {
    paddingHorizontal: 16,
    paddingTop: 20,
    paddingBottom: 24,
    backgroundColor: T.slate100,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    marginTop: -10,
  },
  card: {
    borderRadius: 18,
    padding: 16,
    backgroundColor: T.white,
    borderWidth: 1,
    borderColor: T.slate200,
    shadowColor: '#0f172a',
    shadowOpacity: 0.04,
    shadowOffset: { width: 0, height: 2 },
    shadowRadius: 10,
    elevation: 2,
  },
  fieldGroup: {
    marginBottom: 12,
  },
  label: {
    fontSize: 11,
    fontWeight: '500',
    color: T.slate500,
    marginBottom: 4,
  },
  input: {
    borderRadius: 10,
    borderWidth: 1,
    borderColor: T.slate200,
    paddingHorizontal: 10,
    paddingVertical: 8,
    fontSize: 13,
    color: T.slate800,
    backgroundColor: T.white,
  },
  primaryButton: {
    marginTop: 8,
    borderRadius: 999,
    backgroundColor: '#0f766e',
    paddingVertical: 11,
    alignItems: 'center',
    justifyContent: 'center',
  },
  primaryButtonText: {
    fontSize: 13,
    fontWeight: '600',
    color: T.white,
  },
  errorText: {
    marginTop: 8,
    fontSize: 11,
    color: '#b91c1c',
  },
  footerNote: {
    marginTop: 16,
    fontSize: 11,
    color: T.slate400,
  },
});
