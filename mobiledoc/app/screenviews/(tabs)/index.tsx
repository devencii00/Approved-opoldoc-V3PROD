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

// ─── Design Tokens ───────────────────────────────────────────────────────────
const T = {
  cyan500: '#06b6d4',
  cyan600: '#0891b2',
  cyan700: '#0e7490',
  cyan400: '#22d3ee',
  slate50:  '#f8fafc',
  slate100: '#f1f5f9',
  slate200: '#e2e8f0',
  slate300: '#cbd5e1',
  slate400: '#94a3b8',
  slate500: '#64748b',
  slate600: '#475569',
  slate700: '#334155',
  slate800: '#1e293b',
  slate900: '#0f172a',
  white:    '#ffffff',
  green100: 'rgba(34,197,94,0.12)',
  green700: '#15803d',
  red100:   'rgba(239,68,68,0.12)',
  red700:   '#b91c1c',
  amber100: 'rgba(245,158,11,0.12)',
  amber700: '#b45309',
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

type DashboardAppointment = {
  id: string;
  date: string;
  time: string;
  doctor: string;
  type: string;
  status: string;
};

type DashboardPrescription = {
  id: string;
  date: string;
  doctor: string;
  summary: string;
};

type DashboardVisit = {
  id: string;
  date: string;
  doctor: string;
  reason: string;
};

type DashboardNotification = {
  id: string;
  title: string;
  body: string;
};

type DashboardQueueStatus = {
  queueId: string;
  status: string;
  queueNumber: string;
  position: number | null;
  estimatedWaitMinutes: number | null;
  doctor: string;
};

type AnimatedCardProps = {
  children: ReactNode;
  delay?: number;
  style?: StyleProp<ViewStyle>;
};

// ─── Animated Card ────────────────────────────────────────────────────────────
function AnimatedCard({ children, delay = 0, style }: AnimatedCardProps) {
  const anim = useRef(new Animated.Value(0)).current;
  useEffect(() => {
    Animated.timing(anim, {
      toValue: 1,
      duration: 480,
      delay,
      useNativeDriver: true,
    }).start();
  }, []);
  return (
    <Animated.View
      style={[
        {
          opacity: anim,
          transform: [{ translateY: anim.interpolate({ inputRange: [0, 1], outputRange: [18, 0] }) }],
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

// ─── Section Card ─────────────────────────────────────────────────────────────
function SectionCard({ title, subtitle, badge, children, delay, style }: SectionCardProps) {
  return (
    <AnimatedCard delay={delay} style={[styles.card, style]}>
      <View style={styles.cardHeader}>
        <View style={{ flex: 1 }}>
          {badge && (
            <View style={styles.eyebrowRow}>
              <View style={styles.eyebrowDot} />
              <Text style={styles.eyebrowText}>{badge}</Text>
            </View>
          )}
          <Text style={styles.cardTitle}>{title}</Text>
          {subtitle && <Text style={styles.cardSubtitle}>{subtitle}</Text>}
        </View>
      </View>
      <View style={styles.cardBody}>{children}</View>
    </AnimatedCard>
  );
}

type RowItemProps = {
  title: string;
  subtitle: string;
  pill?: string;
  onPress?: () => void;
};

// ─── Row Item ─────────────────────────────────────────────────────────────────
function RowItem({ title, subtitle, pill, onPress }: RowItemProps) {
  return (
    <View style={styles.row}>
      <View style={styles.rowDot} />
      <View style={styles.rowMain}>
        <Text style={styles.rowTitle}>{title}</Text>
        <Text style={styles.rowSubtitle}>{subtitle}</Text>
        {pill && (
          <View style={styles.pillWrap}>
            <View style={styles.pill}>
              <Text style={styles.pillText}>{pill}</Text>
            </View>
          </View>
        )}
      </View>
      <Pressable
        style={({ pressed }) => [styles.viewBtn, pressed && { opacity: 0.7 }]}
        onPress={onPress}
      >
        <Text style={styles.viewBtnText}>View</Text>
      </Pressable>
    </View>
  );
}

type NotifRowProps = {
  title: string;
  body: string;
};

// ─── Notification Row ─────────────────────────────────────────────────────────
function NotifRow({ title, body }: NotifRowProps) {
  return (
    <View style={styles.notifRow}>
      <View style={styles.notifIconWrap}>
        <View style={styles.notifIcon}>
          <View style={styles.notifIconInner} />
        </View>
      </View>
      <View style={styles.notifBody}>
        <Text style={styles.notifTitle}>{title}</Text>
        <Text style={styles.notifText}>{body}</Text>
      </View>
    </View>
  );
}

// ─── Main Screen ──────────────────────────────────────────────────────────────
export default function PatientDashboardScreen() {
  const router = useRouter();
  const [upcomingAppointments, setUpcomingAppointments] = useState<DashboardAppointment[]>([]);
  const [recentPrescriptions, setRecentPrescriptions] = useState<DashboardPrescription[]>([]);
  const [recentVisits, setRecentVisits] = useState<DashboardVisit[]>([]);
  const [notifications, setNotifications] = useState<DashboardNotification[]>([]);
  const [queueStatus, setQueueStatus] = useState<DashboardQueueStatus | null>(null);
  const [error, setError] = useState('');

  useEffect(() => {
    let cancelled = false;

    async function loadQueue(token: string) {
      try {
        const queuesRes = await fetch(`${API_BASE_URL}/queues?per_page=10`, {
          headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        });

        const queuesData = await queuesRes.json().catch(() => ({}));
        if (!queuesRes.ok) {
          if (!cancelled) setQueueStatus(null);
          return;
        }

        const queueRaw = Array.isArray(queuesData?.data) ? queuesData.data : [];
        const activeQueue = queueRaw.find((q: any) => q?.status === 'waiting' || q?.status === 'serving') ?? null;
        const mappedQueue: DashboardQueueStatus | null = activeQueue
          ? {
              queueId: String(activeQueue.queue_id ?? ''),
              status: String(activeQueue.status ?? ''),
              queueNumber: activeQueue.queue_number != null ? String(activeQueue.queue_number) : '',
              position: typeof activeQueue.position === 'number' ? activeQueue.position : null,
              estimatedWaitMinutes:
                typeof activeQueue.estimated_wait_minutes === 'number' ? activeQueue.estimated_wait_minutes : null,
              doctor: (() => {
                const doctorFirst = activeQueue?.appointment?.doctor?.firstname
                  ? String(activeQueue.appointment.doctor.firstname)
                  : '';
                const doctorLast = activeQueue?.appointment?.doctor?.lastname
                  ? String(activeQueue.appointment.doctor.lastname)
                  : '';
                const doctorName = `Dr. ${[doctorFirst, doctorLast].filter(Boolean).join(' ')}`.trim();
                return doctorName === 'Dr.' ? 'Doctor' : doctorName;
              })(),
            }
          : null;

        if (!cancelled) setQueueStatus(mappedQueue);
      } catch {
        if (!cancelled) setQueueStatus(null);
      }
    }

    async function loadDashboard(token: string) {
      try {
        const [appointmentsRes, prescriptionsRes, visitsRes, notificationsRes] = await Promise.all([
          fetch(`${API_BASE_URL}/appointments?upcoming_only=1&per_page=5`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
          fetch(`${API_BASE_URL}/prescriptions?per_page=5`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
          fetch(`${API_BASE_URL}/visits?per_page=5`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
          fetch(`${API_BASE_URL}/notifications?per_page=5`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
        ]);

        const [appointmentsData, prescriptionsData, visitsData, notificationsData] = await Promise.all([
          appointmentsRes.json().catch(() => ({})),
          prescriptionsRes.json().catch(() => ({})),
          visitsRes.json().catch(() => ({})),
          notificationsRes.json().catch(() => ({})),
        ]);

        if (!appointmentsRes.ok || !prescriptionsRes.ok || !visitsRes.ok || !notificationsRes.ok) {
          const anyMessage =
            appointmentsData?.message ||
            prescriptionsData?.message ||
            visitsData?.message ||
            notificationsData?.message;
          setError(typeof anyMessage === 'string' && anyMessage.length > 0 ? anyMessage : 'Unable to load dashboard.');
          return;
        }

        const apptsRaw = Array.isArray(appointmentsData?.data) ? appointmentsData.data : [];
        const apptsMapped: DashboardAppointment[] = apptsRaw
          .filter((a: any) => a?.appointment_datetime)
          .map((a: any) => {
            const dt = new Date(a.appointment_datetime);
            const doctorFirst = a?.doctor?.firstname ? String(a.doctor.firstname) : '';
            const doctorLast = a?.doctor?.lastname ? String(a.doctor.lastname) : '';
            const doctorName = `Dr. ${[doctorFirst, doctorLast].filter(Boolean).join(' ')}`.trim();
            return {
              id: String(a.appointment_id),
              date: dt.toLocaleDateString(),
              time: dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
              doctor: doctorName === 'Dr.' ? 'Doctor' : doctorName,
              type: a?.appointment_type === 'scheduled' ? 'Scheduled' : 'Walk-in',
              status: typeof a?.status === 'string' ? a.status : '',
            };
          });

        const presRaw = Array.isArray(prescriptionsData?.data) ? prescriptionsData.data : [];
        const presMapped: DashboardPrescription[] = presRaw.map((p: any) => {
          const dt = p?.prescribed_datetime ? new Date(p.prescribed_datetime) : null;
          const doctorFirst = p?.doctor?.firstname ? String(p.doctor.firstname) : '';
          const doctorLast = p?.doctor?.lastname ? String(p.doctor.lastname) : '';
          const doctorName = `Dr. ${[doctorFirst, doctorLast].filter(Boolean).join(' ')}`.trim();
          const firstItem = Array.isArray(p?.items) && p.items.length > 0 ? p.items[0] : null;
          const summary = firstItem?.medicine_name ? String(firstItem.medicine_name) : 'Prescription';
          return {
            id: String(p.prescription_id),
            date: dt ? dt.toLocaleDateString() : '',
            doctor: doctorName === 'Dr.' ? 'Doctor' : doctorName,
            summary,
          };
        });

        const visitsRaw = Array.isArray(visitsData?.data) ? visitsData.data : [];
        const visitsMapped: DashboardVisit[] = visitsRaw.map((v: any) => {
          const dt = v?.visit_datetime ? new Date(v.visit_datetime) : null;
          const doctorFirst = v?.prescriptions?.[0]?.doctor?.firstname
            ? String(v.prescriptions[0].doctor.firstname)
            : '';
          const doctorLast = v?.prescriptions?.[0]?.doctor?.lastname
            ? String(v.prescriptions[0].doctor.lastname)
            : '';
          const doctorName = `Dr. ${[doctorFirst, doctorLast].filter(Boolean).join(' ')}`.trim();
          const reason =
            typeof v?.appointment?.reason_for_visit === 'string' && v.appointment.reason_for_visit.length > 0
              ? v.appointment.reason_for_visit
              : 'Clinic visit';
          return {
            id: String(v.transaction_id),
            date: dt ? dt.toLocaleDateString() : '',
            doctor: doctorName === 'Dr.' ? 'Doctor' : doctorName,
            reason,
          };
        });

        const notifsRaw = Array.isArray(notificationsData?.data) ? notificationsData.data : [];
        const notifsMapped: DashboardNotification[] = notifsRaw.map((n: any) => {
          const type = typeof n?.type === 'string' ? n.type : 'system';
          const title = `${type.charAt(0).toUpperCase()}${type.slice(1)}`;
          const body = typeof n?.message === 'string' ? n.message : '';
          return { id: String(n.notification_id), title, body };
        });

        if (!cancelled) {
          setUpcomingAppointments(apptsMapped);
          setRecentPrescriptions(presMapped);
          setRecentVisits(visitsMapped);
          setNotifications(notifsMapped);
          setError('');
        }
      } catch {
        if (!cancelled) setError('Network error. Please try again.');
      }
    }

    const token = (globalThis as any)?.apiToken as string | undefined;
    if (!token) {
      setError('Please log in again.');
      return () => {
        cancelled = true;
      };
    }

    loadDashboard(token);
    loadQueue(token);

    const intervalId = setInterval(() => {
      loadQueue(token);
    }, 15000);

    return () => {
      cancelled = true;
      clearInterval(intervalId);
    };
  }, []);

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" backgroundColor={T.cyan700} />

      {/* ── Top Header Bar ── */}
      <View style={styles.header}>
        <View style={styles.headerInner}>
          <View>
            <View style={styles.eyebrowRow}>
              <View style={[styles.eyebrowDot, { backgroundColor: 'rgba(255,255,255,0.7)' }]} />
              <Text style={[styles.eyebrowText, { color: 'rgba(255,255,255,0.8)' }]}>Patient Portal</Text>
            </View>
            <Text style={styles.headerTitle}>Dashboard</Text>
            <Text style={styles.headerSub}>Good morning, Patient 👋</Text>
          </View>
          <View style={styles.avatarCircle}>
            <Text style={styles.avatarText}>P</Text>
          </View>
        </View>

        {/* Stat pills in header */}
        <View style={styles.headerStats}>
          <View style={styles.headerStatPill}>
            <Text style={styles.headerStatNum}>{upcomingAppointments.length}</Text>
            <Text style={styles.headerStatLabel}>Upcoming</Text>
          </View>
          <View style={styles.headerStatDivider} />
          <View style={styles.headerStatPill}>
            <Text style={styles.headerStatNum}>{recentPrescriptions.length}</Text>
            <Text style={styles.headerStatLabel}>Prescriptions</Text>
          </View>
          <View style={styles.headerStatDivider} />
          <View style={styles.headerStatPill}>
            <Text style={styles.headerStatNum}>{recentVisits.length}</Text>
            <Text style={styles.headerStatLabel}>Visits</Text>
          </View>
        </View>
      </View>

      {/* ── Scrollable Body ── */}
      <ScrollView
        style={styles.scroll}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        {error ? <Text style={styles.inlineError}>{error}</Text> : null}

        {queueStatus ? (
          <SectionCard title="Queue Status" subtitle="Live updates while you are in the queue." badge="Queue" delay={30}>
            <View style={styles.queueStatusRow}>
              <View style={styles.queueStatusMain}>
                <Text style={styles.queueStatusTitle}>
                  Position: {queueStatus.position != null ? queueStatus.position : queueStatus.queueNumber || '—'}
                </Text>
                <Text style={styles.queueStatusSub}>
                  {queueStatus.doctor}
                  {queueStatus.estimatedWaitMinutes != null ? ` · Est. wait: ${queueStatus.estimatedWaitMinutes} min` : ''}
                </Text>
              </View>
              <Pressable
                onPress={() => router.push('/screenviews/queue' as any)}
                style={({ pressed }) => [styles.queueBtn, pressed && { opacity: 0.7 }]}
              >
                <Text style={styles.queueBtnText}>Open</Text>
              </Pressable>
            </View>
          </SectionCard>
        ) : null}

        {/* Upcoming Appointments */}
        <SectionCard
          title="Upcoming Appointments"
          subtitle="Your next scheduled visits."
          badge="Appointments"
          delay={60}
        >
          {upcomingAppointments.map((item) => (
            <RowItem
              key={item.id}
              title={item.doctor}
              subtitle={`${item.date} at ${item.time} · ${item.type}`}
              pill={item.status}
            />
          ))}
        </SectionCard>

        {/* Recent Prescriptions */}
        <SectionCard
          title="Recent Prescriptions"
          subtitle="Most recent prescriptions from your doctors."
          badge="Prescriptions"
          delay={120}
        >
          {recentPrescriptions.map((item) => (
            <RowItem
              key={item.id}
              title={item.summary}
              subtitle={`${item.date} · ${item.doctor}`}
            />
          ))}
        </SectionCard>

        {/* Recent Visits */}
        <SectionCard
          title="Recent Visits"
          subtitle="Summary of your latest clinic visits."
          badge="Visits"
          delay={180}
        >
          {recentVisits.map((item) => (
            <RowItem
              key={item.id}
              title={item.reason}
              subtitle={`${item.date} · ${item.doctor}`}
            />
          ))}
        </SectionCard>

        {/* Notifications */}
        <SectionCard
          title="Notifications"
          subtitle="Reminders and updates related to your care."
          badge="Notifications"
          delay={240}
          style={{ marginBottom: 32 }}
        >
          {notifications.map((item) => (
            <NotifRow key={item.id} title={item.title} body={item.body} />
          ))}
        </SectionCard>

      </ScrollView>
    </SafeAreaView>
  );
}

// ─── Styles ───────────────────────────────────────────────────────────────────
const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: T.cyan700,
  },

  // ── Header ──
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
    fontFamily: 'serif', // Playfair equivalent; swap for Playfair Display via expo-font
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

  // Header stat row
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

  // ── Scroll ──
  scroll: {
    flex: 1,
    backgroundColor: T.slate100,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    marginTop: -16,
  },
  inlineError: {
    fontSize: 12,
    color: '#b91c1c',
    marginBottom: 12,
    marginTop: 4,
  },
  scrollContent: {
    paddingTop: 20,
    paddingHorizontal: 16,
  },

  // ── Card ──
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

  // Eyebrow
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

  // ── Row item ──
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 11,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: T.slate100,
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
    marginRight: 10,
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
    flexDirection: 'row',
  },
  pill: {
    backgroundColor: 'rgba(6,182,212,0.1)',
    borderRadius: 999,
    paddingHorizontal: 8,
    paddingVertical: 2,
  },
  pillText: {
    fontSize: 9,
    fontWeight: '700',
    color: T.cyan700,
    letterSpacing: 0.4,
    textTransform: 'uppercase',
  },
  viewBtn: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 10,
    borderWidth: 1.5,
    borderColor: T.cyan600,
    backgroundColor: 'rgba(6,182,212,0.06)',
  },
  viewBtnText: {
    fontSize: 11,
    fontWeight: '700',
    color: T.cyan700,
  },

  queueStatusRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    gap: 12,
  },
  queueStatusMain: {
    flex: 1,
  },
  queueStatusTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: T.slate800,
    marginBottom: 2,
  },
  queueStatusSub: {
    fontSize: 11,
    color: T.slate500,
    lineHeight: 15,
  },
  queueBtn: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: 'rgba(6,182,212,0.08)',
    borderWidth: 1,
    borderColor: T.cyan600,
  },
  queueBtnText: {
    fontSize: 11,
    fontWeight: '700',
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

  // ── Notification row ──
  notifRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    paddingVertical: 11,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: T.slate100,
    gap: 10,
  },
  notifIconWrap: {
    marginTop: 2,
  },
  notifIcon: {
    width: 28,
    height: 28,
    borderRadius: 9,
    backgroundColor: 'rgba(6,182,212,0.12)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  notifIconInner: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: T.cyan500,
  },
  notifBody: {
    flex: 1,
  },
  notifTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: T.slate800,
    marginBottom: 3,
  },
  notifText: {
    fontSize: 11,
    color: T.slate500,
    lineHeight: 16,
  },
});
