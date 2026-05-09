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

export default function CreateAccountScreen() {
  const insets = useSafeAreaInsets();
  const router = useRouter();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  // animations (same system as landing/login)
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
          toValue: 1.06,
          duration: 1600,
          useNativeDriver: true,
        }),
        Animated.timing(pulseAnim, {
          toValue: 1,
          duration: 1600,
          useNativeDriver: true,
        }),
      ])
    ).start();
  }, []);

  async function handleRegister() {
    if (!email || !password || !confirmPassword) {
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

    setSubmitting(true);
    setError('');
    setSuccess('');

    try {
      const registerResponse = await fetch(`${API_BASE_URL}/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          email,
          password,
          password_confirmation: confirmPassword,
        }),
      });

      let registerData: any = {};
      try {
        registerData = await registerResponse.json();
      } catch {
        registerData = {};
      }

      if (!registerResponse.ok) {
        setError(registerData?.message || 'Unable to create account.');
        return;
      }

      const loginResponse = await fetch(`${API_BASE_URL}/login`, {
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

      let loginData: any = {};
      try {
        loginData = await loginResponse.json();
      } catch {
        loginData = {};
      }

    (globalThis as any).apiToken = loginData.token;
     (globalThis as any).currentUser = loginData.user;

      setSuccess('Account created successfully.');
      await new Promise((resolve) => setTimeout(resolve, 900));

  
      if (loginData?.user?.is_first_login) {
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

      {/* background */}
      <LinearGradient
        colors={['#0891b2', '#0e7490', '#155e75']}
        style={StyleSheet.absoluteFill}
      />

      {/* circles */}
      <View style={styles.circleTopRight} />
      <View style={styles.circleBottomLeft} />
      <View style={styles.circleMidLeft} />

      <View style={[styles.container, { paddingTop: insets.top + 40, paddingBottom: insets.bottom + 30 }]}>

        {/* HEADER */}
        <Animated.View style={{ opacity: fadeAnim, transform: [{ translateY: slideAnim }], alignItems: 'center' }}>
          <Text style={styles.tagline}>PATIENT PORTAL</Text>
          <Text style={styles.title}>Create your{'\n'}account</Text>
          <View style={styles.divider} />
          <Text style={styles.subtitle}>Join the clinic system securely</Text>
        </Animated.View>

        
        {/* LOGO (FIXED - REAL IMAGE RESTORED) */}
        <Animated.View
          style={[
            styles.logoWrapper,
            { transform: [{ scale: pulseAnim }] },
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


        {/* FORM */}
        <Animated.View style={[styles.form, { opacity: fadeAnim }]}>
          <TextInput
            placeholder="Email address"
            placeholderTextColor="rgba(255,255,255,0.5)"
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

          <TextInput
            placeholder="Confirm password"
            placeholderTextColor="rgba(255,255,255,0.5)"
            secureTextEntry
            value={confirmPassword}
            onChangeText={setConfirmPassword}
            style={styles.input}
          />

          {error ? <Text style={styles.error}>{error}</Text> : null}
          {success ? <Text style={styles.success}>{success}</Text> : null}
        </Animated.View>

        {/* BUTTONS */}
        <View style={styles.buttons}>
          <Pressable
            onPress={handleRegister}
            disabled={submitting}
            style={({ pressed }) => [
              styles.btn,
              pressed && { opacity: 0.85 },
            ]}
          >
            <LinearGradient
              colors={['rgba(255, 255, 255, 0.22)', 'rgba(255,255,255,0.10)']}
              style={styles.btnGradient}
            >
              <Text style={styles.btnText}>
                {submitting ? 'Creating...' : 'Create Account'}
              </Text>
            </LinearGradient>
          </Pressable>

          <Pressable onPress={() => router.replace('/screenviews/aut-landing/login-screen')}>
            <Text style={styles.link}>Already have an account? Log in</Text>
          </Pressable>

 {/* <Pressable onPress={() => router.push('/screenviews/aut-landing/landing-portal')}>
            <Text style={styles.link}>Back</Text>
          </Pressable> */}
         
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
  },

  form: {
    width: '100%',
    gap: 14,
    marginTop: 24,
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
  success: {
    color: 'rgba(34,197,94,0.95)',
    fontSize: 12,
    textAlign: 'center',
  },

  logoWrapper: {
    marginVertical: 18,
  },

  logoPulseRing: {
    width: 140,
    height: 140,
    borderRadius: 70,
    backgroundColor: 'rgba(255,255,255,0.08)',
    alignItems: 'center',
    justifyContent: 'center',
  },

  logoRing: {
    width: 110,
    height: 110,
    borderRadius: 55,
    borderWidth: 1.5,
    borderColor: 'rgba(255,255,255,0.25)',
    alignItems: 'center',
    justifyContent: 'center',
  },

  logoImage: {
    width: 80,
    height: 80,
  },

  buttons: {
    width: '100%',
    gap: 12,
  },

  btn: {
    borderRadius: 16,
    overflow: 'hidden',
    borderWidth: 1.5,
    borderColor: 'rgba(255,255,255,0.4)',
    marginTop: 28,
  },

  btnGradient: {
    padding: 16,
    alignItems: 'center',
  },

  btnText: {
    color: '#fff',
    fontSize: 15,
    fontWeight: '600',
   
  },

  link: {
    textAlign: 'center',
    marginTop: 10,
    color: 'rgba(255,255,255,0.8)',
  },
});
