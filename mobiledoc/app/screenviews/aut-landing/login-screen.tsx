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

const { height } = Dimensions.get('window');

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

export default function LoginScreen() {
  const insets = useSafeAreaInsets();
  const router = useRouter();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');

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

     (globalThis as any).apiToken = data.token;
(globalThis as any).currentUser = data.user;


      if (data?.user?.is_first_login) {
        router.replace('/screenviews/aut-landing/first-login' as any);
        return;
      }

      router.replace('/screenviews/(tabs)');
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setSubmitting(false);
    }
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
            Log in to your{'\n'}account
          </Text>
          <View style={styles.divider} />
          <Text style={styles.subtitle}>Secure access to medical records</Text>
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
          <TextInput
            placeholder="Email address"
            placeholderTextColor="rgba(255,255,255,0.5)"
            keyboardType="email-address"
            autoCapitalize="none"
            value={email}
            onChangeText={setEmail}
            style={styles.input}
          />

          <TextInput
            placeholder="Password"
            placeholderTextColor="rgba(255,255,255,0.5)"
            secureTextEntry
            value={password}
            onChangeText={setPassword}
            style={styles.input}
          />

          {error ? <Text style={styles.error}>{error}</Text> : null}
        </Animated.View>

        {/* BUTTONS */}
        <View style={styles.buttons}>
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

          <Pressable onPress={() => router.push('/screenviews/aut-landing/create-account')}>
            <Text style={styles.createText}>Create Account</Text>
          </Pressable>
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
  error: {
    color: '#fecaca',
    fontSize: 12,
    textAlign: 'center',
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