import React, { useEffect, useState } from 'react';
import { DarkTheme, DefaultTheme, ThemeProvider } from '@react-navigation/native';
import { Stack } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import { ActivityIndicator, StyleSheet, View } from 'react-native';
import 'react-native-reanimated';

import ChatbotOverlay from '@/components/ChatbotOverlay';
import { useColorScheme } from '@/hooks/use-color-scheme';
import { hydrateAuthSession } from '@/lib/auth-storage';

export const unstable_settings = {
  anchor: 'screenviews/(tabs)',
};

export default function RootLayout() {
  const colorScheme = useColorScheme();
  const [hydrated, setHydrated] = useState(false);

  useEffect(() => {
    let cancelled = false;

    hydrateAuthSession()
      .catch(() => undefined)
      .finally(() => {
        if (!cancelled) setHydrated(true);
      });

    return () => {
      cancelled = true;
    };
  }, []);

  if (!hydrated) {
    return (
      <ThemeProvider value={colorScheme === 'dark' ? DarkTheme : DefaultTheme}>
        <View style={styles.bootScreen}>
          <ActivityIndicator size="large" color="#0891b2" />
        </View>
        <StatusBar style="auto" />
      </ThemeProvider>
    );
  }

  return (
    <ThemeProvider value={colorScheme === 'dark' ? DarkTheme : DefaultTheme}>
      <Stack screenOptions={{ headerShown: false }}>
        <Stack.Screen name="screenviews/(tabs)" options={{ headerShown: false }} />
        <Stack.Screen name="modal" options={{ presentation: 'modal', title: 'Modal' }} />
      </Stack>
      <ChatbotOverlay />
      <StatusBar style="auto" />
    </ThemeProvider>
  );
}

const styles = StyleSheet.create({
  bootScreen: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#ffffff',
  },
});
