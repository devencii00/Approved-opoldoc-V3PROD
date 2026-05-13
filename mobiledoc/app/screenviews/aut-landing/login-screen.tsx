import React, { useState, useRef, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  StyleSheet,
  Pressable,
  StatusBar,
  SafeAreaView,
  Animated,
  Platform,
  Dimensions,
  Image,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { persistAuthSession } from '@/lib/auth-storage';

const { height } = Dimensions.get('window');

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

export default function LoginScreen() {
  const insets = useSafeAreaInsets();
  const router = useRouter();

  const [mode, setMode] = useState<'login' | 'forgot-password'>('login');
  const [resetStep, setResetStep] = useState<1 | 2 | 3>(1);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [resetEmail, setResetEmail] = useState('');
  const [resetCode, setResetCode] = useState('');
  const [resetPassword, setResetPassword] = useState('');
  const [resetPasswordConfirm, setResetPasswordConfirm] = useState('');
  const [showResetPassword, setShowResetPassword] = useState(false);
  const [showResetPasswordConfirm, setShowResetPasswordConfirm] = useState(false);
  const [resetSubmitting, setResetSubmitting] = useState(false);
  const [resetError, setResetError] = useState('');
  const [resetNotice, setResetNotice] = useState('');

  // Animations (same system as landing)
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const slideAnim = useRef(new Animated.Value(30)).current;
  const pulseAnim = useRef(new Animated.Value(1)).current;

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

  async function resolvePatientRoute(token: string, user: any) {
    if (user?.must_change_credentials) {
      router.replace('/screenviews/aut-landing/first-login' as any);
      return;
    }

    if (user?.is_first_login) {
      let hasPendingVerification = false;
      try {
        const verificationResponse = await fetch(`${API_BASE_URL}/patient-verifications?status=pending&per_page=1`, {
          headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        });
        const verificationData = await verificationResponse.json().catch(() => ({}));
        const verificationItems = Array.isArray((verificationData as any)?.data)
          ? (verificationData as any).data
          : Array.isArray(verificationData)
            ? verificationData
            : [];
        hasPendingVerification = verificationResponse.ok && verificationItems.length > 0;
      } catch {
        hasPendingVerification = false;
      }

      await persistAuthSession(token, { ...user, has_pending_verification: hasPendingVerification });
      router.replace(
        hasPendingVerification
          ? '/screenviews/aut-landing/pending-approval'
          : '/screenviews/aut-landing/fillup-info'
      );
      return;
    }

    await persistAuthSession(token, { ...user, has_pending_verification: false });
    router.replace('/screenviews/(tabs)' as any);
  }

  async function handleLogin() {
    if (!email || !password) {
      setError('Please enter your email and password.');
      return;
    }

    setError('');
    setSubmitting(true);

    try {
      const response = await fetch(`${API_BASE_URL}/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          email,
          password,
          device_name: 'mobiledoc',
        }),
      });

      let data: any = {};

      try {
        data = await response.json();
      } catch {
        data = {};
      }

      if (!response.ok) {
        setError(data?.message || 'Unable to sign in.');
        return;
      }

      await persistAuthSession(String(data?.token ?? ''), data?.user ?? null);

      const role = String(data?.user?.role ?? '').toLowerCase().trim();
      if (role && role !== 'patient') {
        router.replace('/screenviews/aut-landing/staff-account-only');
        return;
      }


      await resolvePatientRoute(String(data?.token ?? ''), data?.user ?? null);
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setSubmitting(false);
    }
  }

  function openForgotPassword() {
    setMode('forgot-password');
    setResetStep(1);
    setResetEmail(email);
    setResetCode('');
    setResetPassword('');
    setResetPasswordConfirm('');
    setShowResetPassword(false);
    setShowResetPasswordConfirm(false);
    setResetError('');
    setResetNotice('');
  }

  function closeForgotPassword() {
    setMode('login');
    setResetStep(1);
    setResetCode('');
    setResetPassword('');
    setResetPasswordConfirm('');
    setShowResetPassword(false);
    setShowResetPasswordConfirm(false);
    setResetSubmitting(false);
    setResetError('');
    setResetNotice('');
  }

  function handleSendPasscode() {
    setResetError('');
    setResetNotice('');
    setResetSubmitting(true);

    setTimeout(() => {
      setResetSubmitting(false);
      setResetStep(2);
      setResetNotice(`A 5-digit passcode was sent to ${resetEmail || 'your email'}.`);
    }, 500);
  }

  function handleVerifyPasscode() {
    setResetError('');
    setResetNotice('');
    setResetSubmitting(true);

    setTimeout(() => {
      setResetSubmitting(false);
      setResetStep(3);
      setResetNotice('Passcode accepted. You can now set a new password.');
    }, 400);
  }

  function handleResetPassword() {
    setResetError('');
    setResetNotice('');

    if (!resetPassword || !resetPasswordConfirm) {
      setResetError('Please enter and confirm your new password.');
      return;
    }

    setResetSubmitting(true);

    setTimeout(() => {
      setResetSubmitting(false);
      setPassword(resetPassword);
      setMode('login');
      setResetStep(1);
      setResetCode('');
      setResetPassword('');
      setResetPasswordConfirm('');
      setShowResetPassword(false);
      setShowResetPasswordConfirm(false);
      setResetError('');
      setResetNotice('');
      setError('You can now log in with the new password.');
    }, 500);
  }

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" translucent backgroundColor="transparent" />

      {/* Background */}
      <LinearGradient
        colors={['#0891b2', '#0e7490', '#155e75']}
        style={StyleSheet.absoluteFill}
      />

      {/* Decorative circles */}
      <View style={styles.circleTopRight} />
      <View style={styles.circleBottomLeft} />
      <View style={styles.circleMidLeft} />

      <View
        style={[
          styles.container,
          { paddingTop: insets.top + 40, paddingBottom: insets.bottom + 30 },
        ]}
      >
        {/* HEADER */}
        <Animated.View
          style={{
            opacity: fadeAnim,
            transform: [{ translateY: slideAnim }],
            alignItems: 'center',
          }}
        >
          <Text style={styles.tagline}>PATIENT PORTAL</Text>
          <Text style={styles.title}>
            {mode === 'login' ? `Log in to your${'\n'}account` : `Reset your${'\n'}password`}
          </Text>
          <View style={styles.divider} />
          <Text style={styles.subtitle}>
            {mode === 'login'
              ? 'Secure access to medical records'
              : resetStep === 1
                ? 'Enter your email to receive a 5-digit passcode'
                : resetStep === 2
                  ? 'Enter the mock passcode to continue'
                  : 'Create and confirm your new password'}
          </Text>
        </Animated.View>

        {/* LOGO (FIXED - REAL IMAGE RESTORED) */}
        <Animated.View
          style={[
            styles.logoWrapper,
            { opacity: fadeAnim, transform: [{ scale: pulseAnim }] },
          ]}
        >
          <View style={styles.logoPulseRing}>
            <View style={styles.logoRing}>
              <Image
                source={require('../../../assets/images/docfiles/opoldoc.png')}
                style={styles.logoImage}
                resizeMode="contain"
              />
            </View>
          </View>
        </Animated.View>

        {/* INPUTS */}
        <Animated.View style={[styles.form, { opacity: fadeAnim }]}>
          {mode === 'login' ? (
            <>
              <TextInput
                placeholder="Email address"
                placeholderTextColor="rgba(255,255,255,0.5)"
                keyboardType="email-address"
                autoCapitalize="none"
                value={email}
                onChangeText={setEmail}
                style={styles.input}
              />

              <View style={styles.inputWrap}>
                <TextInput
                  placeholder="Password"
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

              <Pressable onPress={openForgotPassword}>
                <Text style={styles.forgotText}>I forgot my password</Text>
              </Pressable>
            </>
          ) : null}

          {mode === 'forgot-password' && resetStep === 1 ? (
            <>
              <View style={styles.stepBadge}>
                <Text style={styles.stepBadgeText}>Step 1 of 3</Text>
              </View>
              <TextInput
                placeholder="Enter your email"
                placeholderTextColor="rgba(255,255,255,0.5)"
                keyboardType="email-address"
                autoCapitalize="none"
                value={resetEmail}
                onChangeText={setResetEmail}
                style={styles.input}
              />
            </>
          ) : null}

          {mode === 'forgot-password' && resetStep === 2 ? (
            <>
              <View style={styles.stepBadge}>
                <Text style={styles.stepBadgeText}>Step 2 of 3</Text>
              </View>
              <TextInput
                placeholder="Enter 5-digit passcode"
                placeholderTextColor="rgba(255,255,255,0.5)"
                keyboardType="number-pad"
                maxLength={5}
                value={resetCode}
                onChangeText={setResetCode}
                style={styles.input}
              />
            </>
          ) : null}

          {mode === 'forgot-password' && resetStep === 3 ? (
            <>
              <View style={styles.stepBadge}>
                <Text style={styles.stepBadgeText}>Step 3 of 3</Text>
              </View>
              <View style={styles.inputWrap}>
                <TextInput
                  placeholder="New password"
                  placeholderTextColor="rgba(255,255,255,0.5)"
                  secureTextEntry={!showResetPassword}
                  value={resetPassword}
                  onChangeText={setResetPassword}
                  style={styles.inputField}
                />
                <Pressable
                  onPress={() => setShowResetPassword((value) => !value)}
                  style={styles.inputToggle}
                  hitSlop={8}
                >
                  <Ionicons name={showResetPassword ? 'eye-off-outline' : 'eye-outline'} size={20} color="rgba(255,255,255,0.78)" />
                </Pressable>
              </View>

              <View style={styles.inputWrap}>
                <TextInput
                  placeholder="Confirm new password"
                  placeholderTextColor="rgba(255,255,255,0.5)"
                  secureTextEntry={!showResetPasswordConfirm}
                  value={resetPasswordConfirm}
                  onChangeText={setResetPasswordConfirm}
                  style={styles.inputField}
                />
                <Pressable
                  onPress={() => setShowResetPasswordConfirm((value) => !value)}
                  style={styles.inputToggle}
                  hitSlop={8}
                >
                  <Ionicons name={showResetPasswordConfirm ? 'eye-off-outline' : 'eye-outline'} size={20} color="rgba(255,255,255,0.78)" />
                </Pressable>
              </View>
            </>
          ) : null}

          {mode === 'login' && error ? <Text style={styles.error}>{error}</Text> : null}
          {mode === 'forgot-password' && resetNotice ? <Text style={styles.notice}>{resetNotice}</Text> : null}
          {mode === 'forgot-password' && resetError ? <Text style={styles.error}>{resetError}</Text> : null}
        </Animated.View>

        {/* BUTTONS */}
        <View style={styles.buttons}>
          {mode === 'login' ? (
            <Pressable
              onPress={handleLogin}
              disabled={submitting}
              style={({ pressed }) => [
                styles.loginBtn,
                pressed && { opacity: 0.85 },
              ]}
            >
              <LinearGradient
                colors={['rgba(255,255,255,0.22)', 'rgba(255,255,255,0.10)']}
                style={styles.loginGradient}
              >
                <Text style={styles.loginText}>
                  {submitting ? 'Logging in...' : 'Log In'}
                </Text>
              </LinearGradient>
            </Pressable>
          ) : null}

          {mode === 'forgot-password' && resetStep === 1 ? (
            <Pressable
              onPress={handleSendPasscode}
              disabled={resetSubmitting}
              style={({ pressed }) => [
                styles.loginBtn,
                pressed && { opacity: 0.85 },
              ]}
            >
              <LinearGradient
                colors={['rgba(255,255,255,0.22)', 'rgba(255,255,255,0.10)']}
                style={styles.loginGradient}
              >
                <Text style={styles.loginText}>
                  {resetSubmitting ? 'Sending passcode...' : 'Send Passcode'}
                </Text>
              </LinearGradient>
            </Pressable>
          ) : null}

          {mode === 'forgot-password' && resetStep === 2 ? (
            <Pressable
              onPress={handleVerifyPasscode}
              disabled={resetSubmitting}
              style={({ pressed }) => [
                styles.loginBtn,
                pressed && { opacity: 0.85 },
              ]}
            >
              <LinearGradient
                colors={['rgba(255,255,255,0.22)', 'rgba(255,255,255,0.10)']}
                style={styles.loginGradient}
              >
                <Text style={styles.loginText}>
                  {resetSubmitting ? 'Verifying...' : 'Verify Passcode'}
                </Text>
              </LinearGradient>
            </Pressable>
          ) : null}

          {mode === 'forgot-password' && resetStep === 3 ? (
            <Pressable
              onPress={handleResetPassword}
              disabled={resetSubmitting}
              style={({ pressed }) => [
                styles.loginBtn,
                pressed && { opacity: 0.85 },
              ]}
            >
              <LinearGradient
                colors={['rgba(255,255,255,0.22)', 'rgba(255,255,255,0.10)']}
                style={styles.loginGradient}
              >
                <Text style={styles.loginText}>
                  {resetSubmitting ? 'Updating password...' : 'Update Password'}
                </Text>
              </LinearGradient>
            </Pressable>
          ) : null}

          {mode === 'login' ? (
            <Pressable onPress={() => router.push('/screenviews/aut-landing/create-account')}>
              <Text style={styles.createText}>Create Account</Text>
            </Pressable>
          ) : null}

          {mode === 'forgot-password' ? (
            <Pressable onPress={closeForgotPassword}>
              <Text style={styles.createText}>Back to Login</Text>
            </Pressable>
          ) : null}
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
  },

  // background circles (same system as landing)
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

  // header
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

  // logo
  logoWrapper: {
    marginVertical: 28,
  },
  logoPulseRing: {
    width: 168,
    height: 168,
    borderRadius: 84,
    backgroundColor: 'rgba(255,255,255,0.08)',
    alignItems: 'center',
    justifyContent: 'center',
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
  logoImage: {
    width: 100,
    height: 100,
  },

  // form
  form: {
    width: '100%',
    gap: 14,
  },
  input: {
    borderRadius: 14,
    padding: 14,
    color: '#fff',
    backgroundColor: 'rgba(255,255,255,0.12)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.25)',
  },
  inputWrap: {
    position: 'relative',
    justifyContent: 'center',
  },
  inputField: {
    borderRadius: 14,
    padding: 14,
    paddingRight: 48,
    color: '#fff',
    backgroundColor: 'rgba(255,255,255,0.12)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.25)',
  },
  inputToggle: {
    position: 'absolute',
    right: 14,
    height: '100%',
    justifyContent: 'center',
    alignItems: 'center',
  },
  error: {
    color: '#fecaca',
    fontSize: 12,
    textAlign: 'center',
  },
  notice: {
    color: 'rgba(255,255,255,0.82)',
    fontSize: 12,
    textAlign: 'center',
    lineHeight: 18,
  },
  forgotText: {
    color: 'rgba(255,255,255,0.85)',
    textAlign: 'right',
    fontSize: 12,
    marginTop: -2,
  },
  stepBadge: {
    alignSelf: 'center',
    borderRadius: 999,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.22)',
    backgroundColor: 'rgba(255,255,255,0.10)',
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  stepBadgeText: {
    color: '#fff',
    fontSize: 11,
    fontWeight: '600',
  },

  // buttons
  buttons: {
    width: '100%',
    marginTop: 24,
    gap: 12,
  },
  loginBtn: {
    borderRadius: 16,
    overflow: 'hidden',
    borderWidth: 1.5,
    borderColor: 'rgba(255,255,255,0.4)',
  },
  loginGradient: {
    padding: 16,
    alignItems: 'center',
  },
  loginText: {
    color: '#fff',
    fontWeight: '600',
    fontSize: 15,
  },
  createText: {
    color: 'rgba(255,255,255,0.8)',
    textAlign: 'center',
    marginTop: 10,
  },
});
