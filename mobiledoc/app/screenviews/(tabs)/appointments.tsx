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
import { useRouter } from 'expo-router';

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

type AppointmentListItem = {
  id: string;
  date: string;
  time: string;
  doctor: string;
  type: string;
  status: RowItemProps['status'];
};

type QueueRequestItem = {
  id: string;
  doctor: string;
  reason: string;
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

type RowItemProps = {
  doctor: string;
  date: string;
  time: string;
  type: string;
  status: 'Pending' | 'Scheduled' | 'Completed' | 'Cancelled';
};

function AppointmentRow({ doctor, date, time, type, status }: RowItemProps) {
  return (
    <View style={styles.row}>
      <View style={styles.rowDot} />
      <View style={styles.rowMain}>
        <Text style={styles.rowTitle}>{doctor}</Text>
        <Text style={styles.rowSubtitle}>
          {date} at {time} · {type}
        </Text>
        <View style={styles.pillWrap}>
          <View
            style={[
              styles.pill,
              status === 'Completed' && { backgroundColor: 'rgba(16,185,129,0.08)' },
              status === 'Cancelled' && { backgroundColor: 'rgba(248,113,113,0.08)' },
            ]}
          >
            <Text
              style={[
                styles.pillText,
                status === 'Completed' && { color: '#16a34a' },
                status === 'Cancelled' && { color: '#b91c1c' },
              ]}
            >
              {status}
            </Text>
          </View>
        </View>
      </View>
      <View style={styles.actionsColumn}>
        <Pressable style={({ pressed }) => [styles.primaryAction, pressed && { opacity: 0.7 }]}>
          <Text style={styles.primaryActionText}>View</Text>
        </Pressable>
      </View>
    </View>
  );
}

export default function PatientAppointmentsScreen() {
  const [items, setItems] = useState<AppointmentListItem[]>([]);
  const [queueRequests, setQueueRequests] = useState<QueueRequestItem[]>([]);
  const [error, setError] = useState('');
  const router = useRouter();

  useEffect(() => {
    let cancelled = false;

    async function load() {
      try {
        const token = (globalThis as any)?.apiToken as string | undefined;
        if (!token) {
          setError('Please log in again.');
          return;
        }

        const response = await fetch(`${API_BASE_URL}/appointments?per_page=50`, {
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
              : 'Unable to load appointments.';
          setError(message);
          return;
        }

        const raw = Array.isArray(data?.data) ? data.data : [];
        const mapped: AppointmentListItem[] = raw
          .filter((a: any) => a?.appointment_datetime)
          .map((a: any) => {
            const dt = new Date(a.appointment_datetime);
            const doctorFirst = a?.doctor?.firstname ? String(a.doctor.firstname) : '';
            const doctorLast = a?.doctor?.lastname ? String(a.doctor.lastname) : '';
            const doctorName = `Dr. ${[doctorFirst, doctorLast].filter(Boolean).join(' ')}`.trim();

            const statusRaw = typeof a?.status === 'string' ? a.status : '';
            const status: RowItemProps['status'] =
              statusRaw === 'pending'
                ? 'Pending'
                : statusRaw === 'confirmed'
                  ? 'Scheduled'
                  : statusRaw === 'completed'
                    ? 'Completed'
                    : statusRaw === 'cancelled'
                      ? 'Cancelled'
                      : 'Pending';

            return {
              id: String(a.appointment_id),
              date: dt.toLocaleDateString(),
              time: dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
              doctor: doctorName === 'Dr.' ? 'Doctor' : doctorName,
              type: a?.appointment_type === 'scheduled' ? 'Scheduled' : 'Walk-in',
              status,
            };
          });

        const queueReqs: QueueRequestItem[] = raw
          .filter((a: any) => a?.appointment_type === 'scheduled' && !a?.appointment_datetime)
          .map((a: any) => {
            const doctorFirst = a?.doctor?.firstname ? String(a.doctor.firstname) : '';
            const doctorLast = a?.doctor?.lastname ? String(a.doctor.lastname) : '';
            const doctorName = `Dr. ${[doctorFirst, doctorLast].filter(Boolean).join(' ')}`.trim();
            const reason =
              typeof a?.reason_for_visit === 'string' && a.reason_for_visit.length > 0
                ? a.reason_for_visit
                : 'Queue request';
            return {
              id: String(a.appointment_id),
              doctor: doctorName === 'Dr.' ? 'Doctor' : doctorName,
              reason,
            };
          });

        if (!cancelled) {
          setItems(mapped);
          setQueueRequests(queueReqs);
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
            <Text style={styles.headerTitle}>Appointments</Text>
            <Text style={styles.headerSub}>Review your upcoming and past visits.</Text>
          </View>
          <View style={styles.avatarCircle}>
            <Text style={styles.avatarText}>P</Text>
          </View>
        </View>

        <View style={styles.headerStats}>
          <View style={styles.headerStatPill}>
            <Text style={styles.headerStatNum}>
              {items.filter((a) => a.status === 'Scheduled' || a.status === 'Pending').length}
            </Text>
            <Text style={styles.headerStatLabel}>Scheduled</Text>
          </View>
          <View style={styles.headerStatDivider} />
          <View style={styles.headerStatPill}>
            <Text style={styles.headerStatNum}>
              {items.filter((a) => a.status === 'Completed').length}
            </Text>
            <Text style={styles.headerStatLabel}>Completed</Text>
          </View>
          <View style={styles.headerStatDivider} />
          <View style={styles.headerStatPill}>
            <Text style={styles.headerStatNum}>
              {items.filter((a) => a.status === 'Cancelled').length}
            </Text>
            <Text style={styles.headerStatLabel}>Cancelled</Text>
          </View>
        </View>
      </View>

      <ScrollView
        style={styles.scroll}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        <SectionCard title="Actions" subtitle="Book an appointment or request queue entry." badge="Actions" delay={30}>
          <View style={styles.actionRow}>
            <Pressable
              onPress={() => router.push('/screenviews/booking' as any)}
              style={({ pressed }) => [styles.actionPill, pressed && { opacity: 0.7 }]}
            >
              <Text style={styles.actionPillText}>Book appointment</Text>
            </Pressable>
            <Pressable
              onPress={() => router.push('/screenviews/queue' as any)}
              style={({ pressed }) => [styles.actionPill, pressed && { opacity: 0.7 }]}
            >
              <Text style={styles.actionPillText}>Request queue</Text>
            </Pressable>
          </View>
        </SectionCard>

        {queueRequests.length > 0 ? (
          <SectionCard title="Queue requests" subtitle="Waiting for approval." badge="Queue" delay={45}>
            {queueRequests.map((item) => (
              <View key={item.id} style={styles.row}>
                <View style={styles.rowDot} />
                <View style={styles.rowMain}>
                  <Text style={styles.rowTitle}>{item.doctor}</Text>
                  <Text style={styles.rowSubtitle}>{item.reason}</Text>
                  <View style={styles.pillWrap}>
                    <View style={[styles.pill, { backgroundColor: 'rgba(245,158,11,0.10)' }]}>
                      <Text style={[styles.pillText, { color: '#b45309' }]}>Pending</Text>
                    </View>
                  </View>
                </View>
                <View style={styles.actionsColumn}>
                  <Pressable style={({ pressed }) => [styles.primaryAction, pressed && { opacity: 0.7 }]}>
                    <Text style={styles.primaryActionText}>View</Text>
                  </Pressable>
                </View>
              </View>
            ))}
          </SectionCard>
        ) : null}

        <SectionCard
          title="Appointments"
          subtitle="Tap on an appointment to see details."
          badge="Appointments"
          delay={60}
        >
          {error ? <Text style={styles.inlineError}>{error}</Text> : null}
          {items.map((item) => (
            <AppointmentRow
              key={item.id}
              doctor={item.doctor}
              date={item.date}
              time={item.time}
              type={item.type}
              status={item.status as RowItemProps['status']}
            />
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
  headerStats: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.13)',
    borderRadius: 16,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.2)',
    paddingVertical: 12,
    paddingHorizontal: 16,
  },
  headerStatPill: {
    flex: 1,
    alignItems: 'center',
  },
  headerStatNum: {
    fontSize: 20,
    fontWeight: '700',
    color: T.white,
    lineHeight: 22,
  },
  headerStatLabel: {
    fontSize: 10,
    color: 'rgba(255,255,255,0.7)',
    fontWeight: '500',
    marginTop: 2,
    letterSpacing: 0.3,
  },
  headerStatDivider: {
    width: 1,
    height: 28,
    backgroundColor: 'rgba(255,255,255,0.2)',
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
    paddingVertical: 11,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: T.slate100,
  },
  inlineError: {
    fontSize: 12,
    color: '#b91c1c',
    marginBottom: 10,
  },
  rowDot: {
    width: 7,
    height: 7,
    borderRadius: 4,
    backgroundColor: T.cyan400,
    marginRight: 10,
    flexShrink: 0,
    alignSelf: 'flex-start',
    marginTop: 5,
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
    lineHeight: 15,
  },
  pillWrap: {
    marginTop: 6,
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
  actionRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    paddingVertical: 8,
  },
  actionPill: {
    paddingHorizontal: 12,
    paddingVertical: 9,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
  },
  actionPillText: {
    fontSize: 12,
    fontWeight: '600',
    color: T.slate700,
  },
  pill: {
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 999,
    backgroundColor: 'rgba(6,182,212,0.10)',
    alignSelf: 'flex-start',
  },
  pillText: {
    fontSize: 11,
    fontWeight: '700',
    color: T.cyan700,
  },
});
