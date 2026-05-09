import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Pressable,
  SafeAreaView,
  StatusBar,
  Platform,
  Dimensions,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { useRouter } from 'expo-router';

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');
const { height } = Dimensions.get('window');

export default function StaffAccountOnlyScreen() {
  const router = useRouter();
  const [loggingOut, setLoggingOut] = useState(false);

  async function handleLogout() {
    if (loggingOut) return;
    setLoggingOut(true);

    const token = (globalThis as any)?.apiToken as string | undefined;
    try {
      if (token) {
        await fetch(`${API_BASE_URL}/logout`, {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
        }).catch(() => undefined);
      }
    } finally {
      (globalThis as any).apiToken = undefined;
      (globalThis as any).currentUser = undefined;
      router.replace('/screenviews/aut-landing/landing-portal');
      setLoggingOut(false);
    }
  }

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" translucent backgroundColor="transparent" />
      <LinearGradient colors={['#0891b2', '#0e7490', '#155e75']} style={StyleSheet.absoluteFill} />

      <View style={styles.circleTopRight} />
      <View style={styles.circleBottomLeft} />
      <View style={styles.circleMidLeft} />

      <View style={styles.container}>
        <Text style={styles.tagline}>PATIENT PORTAL</Text>
        <Text style={styles.title}>Mobile access{'\n'}restricted</Text>
        <View style={styles.divider} />
        <Text style={styles.subtitle}>
          Staff accounts are only accessible through website.
        </Text>
        <Text style={styles.message}>
            Staff accounts are only accessible through website, please try login you account there
        </Text>

        <Pressable
          onPress={handleLogout}
          disabled={loggingOut}
          style={({ pressed }) => [styles.primaryButton, pressed && { opacity: 0.85 }, loggingOut && { opacity: 0.75 }]}
        >
          <LinearGradient
            colors={['rgba(255,255,255,0.22)', 'rgba(255,255,255,0.10)']}
            style={styles.primaryButtonGradient}
          >
            <Text style={styles.primaryButtonText}>{loggingOut ? 'Logging out...' : 'Log-out'}</Text>
          </LinearGradient>
        </Pressable>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1 },
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
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 28,
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
  message: {
    color: 'rgba(255,255,255,0.82)',
    fontSize: 12,
    lineHeight: 18,
    textAlign: 'center',
    marginTop: 14,
    marginBottom: 24,
    maxWidth: 360,
  },
  primaryButton: {
    width: '100%',
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
    textAlign: 'center',
  },
});
