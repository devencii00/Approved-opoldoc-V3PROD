import React from 'react';
import { SafeAreaView, ScrollView, StyleSheet, Text, View } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

const T = {
  cyan600: '#0891b2',
  cyan700: '#0e7490',
  slate50: '#f8fafc',
  slate100: '#f1f5f9',
  slate200: '#e2e8f0',
  slate400: '#94a3b8',
  slate500: '#64748b',
  slate800: '#1e293b',
  slate900: '#0f172a',
  white: '#ffffff',
};

export default function DependentsScreen() {
  return (
    <SafeAreaView style={styles.safe}>
      <View style={styles.header}>
        <Text style={styles.eyebrow}>PATIENT PORTAL</Text>
        <Text style={styles.title}>Dependents</Text>
        <Text style={styles.subtitle}>Manage linked family members from one place.</Text>
      </View>

      <ScrollView style={styles.scroll} contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <View style={styles.card}>
          <View style={styles.iconWrap}>
            <Ionicons name="people-outline" size={24} color={T.cyan700} />
          </View>
          <Text style={styles.cardTitle}>Dependents tab ready</Text>
          <Text style={styles.cardText}>
            This tab is now part of the actual bottom navigation. The full dependents content can be added next.
          </Text>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: T.cyan700 },
  header: { backgroundColor: T.cyan700, paddingHorizontal: 20, paddingTop: 10, paddingBottom: 20 },
  eyebrow: { fontSize: 9, fontWeight: '700', letterSpacing: 1.2, color: 'rgba(255,255,255,0.65)', marginBottom: 2 },
  title: { fontSize: 30, fontWeight: '800', color: T.white, lineHeight: 34 },
  subtitle: { marginTop: 4, fontSize: 12, color: 'rgba(255,255,255,0.78)' },
  scroll: {
    flex: 1,
    backgroundColor: T.slate100,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    marginTop: -10,
  },
  content: { padding: 16, paddingBottom: 32 },
  card: {
    backgroundColor: T.white,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: T.slate200,
    padding: 18,
    shadowColor: T.slate900,
    shadowOpacity: 0.05,
    shadowOffset: { width: 0, height: 2 },
    shadowRadius: 8,
    elevation: 2,
  },
  iconWrap: {
    width: 48,
    height: 48,
    borderRadius: 16,
    backgroundColor: 'rgba(6,182,212,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  cardTitle: { fontSize: 16, fontWeight: '700', color: T.slate800, marginBottom: 6 },
  cardText: { fontSize: 13, lineHeight: 18, color: T.slate500 },
});
