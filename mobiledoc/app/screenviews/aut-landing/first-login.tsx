import React, { useEffect, useRef, useState } from 'react';
import {
  View,
  Text,
  TextInput,
  StyleSheet,
  Pressable,
  Animated,
  StatusBar,
  SafeAreaView,
  Platform,
  Dimensions,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { persistCurrentUser } from '@/lib/auth-storage';

const { height } = Dimensions.get('window');

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

export default function FirstLoginScreen() {
  const insets = useSafeAreaInsets();
  const router = useRouter();

  const fadeAnim = useRef(new Animated.Value(0)).current;
  const slideAnim = useRef(new Animated.Value(30)).current;

  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    Animated.parallel([
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 700,
        useNativeDriver: true,
      }),
      Animated.timing(slideAnim, {
        toValue: 0,
        duration: 700,
        useNativeDriver: true,
      }),
    ]).start();

    const user = (globalThis as any)?.currentUser as any | undefined;
    const role = String(user?.role ?? '').toLowerCase().trim();
    if (role && role !== 'patient') {
      router.replace('/screenviews/aut-landing/staff-account-only');
    }
  }, []);

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

      await persistCurrentUser(data);
      if ((data as any)?.is_first_login) {
        router.replace('/screenviews/aut-landing/fillup-info' as any);
        return;
      }
      router.replace('/screenviews/(tabs)' as any);
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" translucent backgroundColor="transparent" />

      <LinearGradient
        colors={['#0891b2', '#0e7490', '#155e75']}
        style={StyleSheet.absoluteFill}
      />

      <View style={styles.circleTopRight} />
      <View style={styles.circleBottomLeft} />
      <View style={styles.circleMidLeft} />

      <View
        style={[
          styles.container,
          { paddingTop: insets.top + 40, paddingBottom: insets.bottom + 30 },
        ]}
      >
        <Animated.View
          style={{
            opacity: fadeAnim,
            transform: [{ translateY: slideAnim }],
            alignItems: 'center',
          }}
        >
          <Text style={styles.tagline}>PATIENT PORTAL</Text>
          <Text style={styles.title}>
            First login{'\n'}security setup
          </Text>
          <View style={styles.divider} />
          <Text style={styles.subtitle}>Change your temporary password to continue</Text>
        </Animated.View>

        <Animated.View style={[styles.form, { opacity: fadeAnim }]}>
          <View style={styles.inputWrap}>
            <TextInput
              placeholder="New password"
              placeholderTextColor="rgba(255,255,255,0.5)"
              secureTextEntry={!showPassword}
              value={password}
              onChangeText={setPassword}
              style={styles.inputField}
            />
            <Pressable
              onPress={() => setShowPassword((value) => !value)}
              style={styles.inputToggle}
              hitSlop={8}
            >
              <Ionicons name={showPassword ? 'eye-off-outline' : 'eye-outline'} size={20} color="rgba(255,255,255,0.78)" />
            </Pressable>
          </View>

          <View style={styles.inputWrap}>
            <TextInput
              placeholder="Confirm new password"
              placeholderTextColor="rgba(255,255,255,0.5)"
              secureTextEntry={!showConfirmPassword}
              value={confirmPassword}
              onChangeText={setConfirmPassword}
              style={styles.inputField}
            />
            <Pressable
              onPress={() => setShowConfirmPassword((value) => !value)}
              style={styles.inputToggle}
              hitSlop={8}
            >
              <Ionicons name={showConfirmPassword ? 'eye-off-outline' : 'eye-outline'} size={20} color="rgba(255,255,255,0.78)" />
            </Pressable>
          </View>

          {error ? <Text style={styles.error}>{error}</Text> : null}
        </Animated.View>

        <View style={styles.buttons}>
          <Pressable
            onPress={handleSetPassword}
            disabled={submitting}
            style={({ pressed }) => [styles.primaryButton, pressed && { opacity: 0.85 }]}
          >
            <LinearGradient
              colors={['rgba(255,255,255,0.22)', 'rgba(255,255,255,0.10)']}
              style={styles.primaryButtonGradient}
            >
              <Text style={styles.primaryButtonText}>{submitting ? 'Saving...' : 'Save Password'}</Text>
            </LinearGradient>
          </Pressable>

          <Text style={styles.footerNote}>
            If you did not receive a temporary password, contact the clinic front desk.
          </Text>
        </View>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1 },
  container: {
    flex: 1,
    paddingHorizontal: 28,
    alignItems: 'center',
    justifyContent: 'center', // This centers content vertically
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
    bottom: -60,
    left: -60,
    width: 200,
    height: 200,
    borderRadius: 100,
    backgroundColor: 'rgba(255,255,255,0.07)',
  },
  circleMidLeft: {
    position: 'absolute',
    top: height * 0.4,
    left: -100,
    width: 220,
    height: 220,
    borderRadius: 110,
    backgroundColor: 'rgba(255,255,255,0.05)',
  },
  tagline: {
    color: 'rgba(255,255,255,0.6)',
    fontSize: 10,
    letterSpacing: 2,
  },
  title: {
    color: '#fff',
    fontSize: 28,
    fontWeight: '700',
    textAlign: 'center',
    fontFamily: Platform.OS === 'ios' ? 'Georgia' : 'serif',
    marginTop: 8,
  },
  divider: {
    width: 40,
    height: 2,
    backgroundColor: 'rgba(255,255,255,0.4)',
    marginVertical: 12,
  },
  subtitle: {
    color: 'rgba(255,255,255,0.6)',
    fontSize: 13,
    fontStyle: 'italic',
    textAlign: 'center',
  },
  form: {
    width: '100%',
    gap: 14,
    marginTop: 32, // Added some spacing after removing the logo
  },
  input: {
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.25)',
    padding: 14,
    fontSize: 13,
    color: '#fff',
    backgroundColor: 'rgba(255,255,255,0.12)',
  },
  inputWrap: {
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.25)',
    backgroundColor: 'rgba(255,255,255,0.12)',
    flexDirection: 'row',
    alignItems: 'center',
  },
  inputField: {
    flex: 1,
    padding: 14,
    fontSize: 13,
    color: '#fff',
  },
  inputToggle: {
    paddingHorizontal: 14,
    paddingVertical: 10,
  },
  error: {
    color: '#fecaca',
    fontSize: 12,
    textAlign: 'center',
  },
  buttons: {
    width: '100%',
    marginTop: 24,
    gap: 12,
  },
  primaryButton: {
    borderRadius: 16,
    overflow: 'hidden',
    borderWidth: 1.5,
    borderColor: 'rgba(255,255,255,0.4)',
  },
  primaryButtonGradient: {
    padding: 16,
    alignItems: 'center',
  },
  primaryButtonText: {
    fontSize: 15,
    fontWeight: '600',
    color: '#fff',
  },
  footerNote: {
    fontSize: 11,
    color: 'rgba(255,255,255,0.7)',
    textAlign: 'center',
    lineHeight: 16,
  },
});
