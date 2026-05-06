import React, { useRef, useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Pressable,
  ScrollView,
  StatusBar,
  Animated,
  SafeAreaView,
} from 'react-native';
import type { StyleProp, ViewStyle } from 'react-native';

const T = {
  cyan500: '#06b6d4',
  cyan600: '#0891b2',
  cyan700: '#0e7490',
  cyan400: '#22d3ee',
  slate50: '#f8fafc',
  slate100: '#f1f5f9',
  slate200: '#e2e8f0',
  slate300: '#cbd5e1',
  slate400: '#94a3b8',
  slate500: '#64748b',
  slate600: '#475569',
  slate700: '#334155',
  slate800: '#1e293b',
  slate900: '#0f172a',
  white: '#ffffff',
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

type PrescriptionListItem = {
  id: string;
  date: string;
  doctor: string;
  medicine: string;
  dosage: string;
  duration: string;
};

type AnimatedCardProps = {
  children: ReactNode;
  delay?: number;
  style?: StyleProp<ViewStyle>;
};

function AnimatedCard({ children, delay = 0, style }: AnimatedCardProps) {
  const anim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    Animated.timing(anim, {
      toValue: 1,
      duration: 480,
      delay,
      useNativeDriver: true,
    }).start();
  }, [anim, delay]);

  return (
    <Animated.View
      style={[
        {
          opacity: anim,
          transform: [
            {
              translateY: anim.interpolate({
                inputRange: [0, 1],
                outputRange: [18, 0],
              }),
            },
          ],
        },
        style,
      ]}
    >
      {children}
    </Animated.View>
  );
}

type SectionCardProps = {
  title: string;
  subtitle?: string;
  badge?: string;
  children: ReactNode;
  delay?: number;
  style?: StyleProp<ViewStyle>;
};

function SectionCard({ title, subtitle, badge, children, delay, style }: SectionCardProps) {
  return (
    <AnimatedCard delay={delay} style={[styles.card, style]}>
      <View style={styles.cardHeader}>
        <View style={{ flex: 1 }}>
          {badge ? (
            <View style={styles.eyebrowRow}>
              <View style={styles.eyebrowDot} />
              <Text style={styles.eyebrowText}>{badge}</Text>
            </View>
          ) : null}
          <Text style={styles.cardTitle}>{title}</Text>
          {subtitle ? <Text style={styles.cardSubtitle}>{subtitle}</Text> : null}
        </View>
      </View>
      <View style={styles.cardBody}>{children}</View>
    </AnimatedCard>
  );
}

export default function PatientPrescriptionsScreen() {
  const [items, setItems] = useState<PrescriptionListItem[]>([]);
  const [error, setError] = useState('');

  useEffect(() => {
    let cancelled = false;

    async function load() {
      try {
        const token = (globalThis as any)?.apiToken as string | undefined;
        if (!token) {
          setError('Please log in again.');
          return;
        }

        const response = await fetch(`${API_BASE_URL}/prescriptions?per_page=50`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
        });

        const data = await response.json();
        if (!response.ok) {
          const message =
            typeof data?.message === 'string' && data.message.length > 0
              ? data.message
              : 'Unable to load prescriptions.';
          setError(message);
          return;
        }

        const raw = Array.isArray(data?.data) ? data.data : [];
        const mapped: PrescriptionListItem[] = raw.map((p: any) => {
          const dt = p?.prescribed_datetime ? new Date(p.prescribed_datetime) : null;
          const doctorFirst = p?.doctor?.firstname ? String(p.doctor.firstname) : '';
          const doctorLast = p?.doctor?.lastname ? String(p.doctor.lastname) : '';
          const doctorName = `Dr. ${[doctorFirst, doctorLast].filter(Boolean).join(' ')}`.trim();

          const firstItem = Array.isArray(p?.items) && p.items.length > 0 ? p.items[0] : null;
          const medicineName = firstItem?.medicine_name ? String(firstItem.medicine_name) : 'Prescription';
          const dosage = firstItem?.dosage ? String(firstItem.dosage) : '';
          const duration = firstItem?.duration ? String(firstItem.duration) : '';

          return {
            id: String(p.prescription_id),
            date: dt ? dt.toLocaleDateString() : '',
            doctor: doctorName === 'Dr.' ? 'Doctor' : doctorName,
            medicine: medicineName,
            dosage,
            duration,
          };
        });

        if (!cancelled) {
          setItems(mapped);
          setError('');
        }
      } catch {
        if (!cancelled) setError('Network error. Please try again.');
      }
    }

    load();
    return () => {
      cancelled = true;
    };
  }, []);

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" backgroundColor={T.cyan700} />

      <View style={styles.header}>
        <View style={styles.headerInner}>
          <View>
            <View style={styles.eyebrowRow}>
              <View style={[styles.eyebrowDot, { backgroundColor: 'rgba(255,255,255,0.7)' }]} />
              <Text style={[styles.eyebrowText, { color: 'rgba(255,255,255,0.8)' }]}>
                Patient Portal
              </Text>
            </View>
            <Text style={styles.headerTitle}>Prescriptions</Text>
            <Text style={styles.headerSub}>Prescriptions issued by your doctors.</Text>
          </View>
          <View style={styles.avatarCircle}>
            <Text style={styles.avatarText}>P</Text>
          </View>
        </View>
      </View>

      <ScrollView
        style={styles.scroll}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        <SectionCard
          title="Prescriptions"
          subtitle="Tap to view full prescription details."
          badge="Prescriptions"
          delay={60}
        >
          {error ? <Text style={styles.inlineError}>{error}</Text> : null}
          {items.map((item) => (
            <View key={item.id} style={styles.row}>
              <View style={styles.rowMain}>
                <Text style={styles.rowTitle}>{item.medicine}</Text>
                <Text style={styles.rowSubtitle}>
                  {item.date} · {item.doctor}
                </Text>
                <Text style={styles.rowMeta}>
                  {[item.dosage, item.duration].filter(Boolean).join(' · ')}
                </Text>
              </View>
              <View style={styles.actionsColumn}>
                <Pressable style={({ pressed }) => [styles.primaryAction, pressed && { opacity: 0.7 }]}>
                  <Text style={styles.primaryActionText}>View</Text>
                </Pressable>
                <Pressable
                  style={({ pressed }) => [styles.secondaryAction, pressed && { opacity: 0.7 }]}
                >
                  <Text style={styles.secondaryActionText}>Download</Text>
                </Pressable>
              </View>
            </View>
          ))}
        </SectionCard>
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
    paddingBottom: 24,
  },
  headerInner: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    marginBottom: 20,
  },
  headerTitle: {
    fontFamily: 'serif',
    fontSize: 26,
    fontWeight: '700',
    color: T.white,
    marginBottom: 2,
    letterSpacing: 0.3,
  },
  headerSub: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.75)',
    fontWeight: '400',
  },
  avatarCircle: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.2)',
    borderWidth: 1.5,
    borderColor: 'rgba(255,255,255,0.35)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: {
    color: T.white,
    fontSize: 16,
    fontWeight: '700',
  },

  scroll: {
    flex: 1,
    backgroundColor: T.slate100,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    marginTop: -16,
  },
  scrollContent: {
    paddingTop: 20,
    paddingHorizontal: 16,
    paddingBottom: 24,
  },

  card: {
    backgroundColor: T.white,
    borderRadius: 20,
    marginBottom: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    shadowColor: T.slate900,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 10,
    elevation: 2,
    overflow: 'hidden',
  },
  cardHeader: {
    paddingHorizontal: 16,
    paddingTop: 16,
    paddingBottom: 10,
    borderBottomWidth: 1,
    borderBottomColor: T.slate100,
  },
  cardTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: T.slate900,
    letterSpacing: 0.1,
  },
  cardSubtitle: {
    fontSize: 11,
    color: T.slate400,
    marginTop: 2,
  },
  cardBody: {
    paddingHorizontal: 16,
    paddingBottom: 8,
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
    backgroundColor: T.cyan500,
  },
  eyebrowText: {
    fontSize: 9,
    fontWeight: '700',
    letterSpacing: 0.9,
    textTransform: 'uppercase',
    color: T.cyan600,
  },

  row: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 11,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: T.slate100,
  },
  rowMain: {
    flex: 1,
    marginRight: 12,
  },
  rowTitle: {
    fontSize: 13,
    fontWeight: '600',
    color: T.slate800,
    marginBottom: 2,
  },
  rowSubtitle: {
    fontSize: 11,
    color: T.slate500,
  },
  rowMeta: {
    fontSize: 11,
    color: T.slate400,
    marginTop: 2,
  },
  inlineError: {
    fontSize: 12,
    color: '#b91c1c',
    marginBottom: 10,
  },
  actionsColumn: {
    alignItems: 'flex-end',
    gap: 6,
    marginLeft: 8,
  },
  primaryAction: {
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 999,
    backgroundColor: 'rgba(6,182,212,0.08)',
    borderWidth: 1,
    borderColor: T.cyan600,
  },
  primaryActionText: {
    fontSize: 11,
    fontWeight: '600',
    color: T.cyan700,
  },
  secondaryAction: {
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
  },
  secondaryActionText: {
    fontSize: 11,
    fontWeight: '500',
    color: T.slate500,
  },
});
