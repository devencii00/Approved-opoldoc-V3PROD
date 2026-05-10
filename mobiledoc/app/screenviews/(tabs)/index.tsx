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
import { Ionicons } from '@expo/vector-icons';

// ─── Design Tokens ───────────────────────────────────────────────────────────
const T = {
  cyan500: '#06b6d4',
  cyan600: '#0891b2',
  cyan700: '#0e7490',
  cyan400: '#22d3ee',
  cyan100: '#cffafe',
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

type IconName = React.ComponentProps<typeof Ionicons>['name'];

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

// ─── Info Stat Card ───────────────────────────────────────────────────────────
type InfoCardProps = {
  icon: IconName;
  label: string;
  value: string;
  sub?: string;
  delay?: number;
  onPress?: () => void;
};

function InfoCard({ icon, label, value, sub, delay = 0, onPress }: InfoCardProps) {
  return (
    <AnimatedCard delay={delay} style={styles.infoCard}>
      <Pressable style={({ pressed }) => [styles.infoCardInner, pressed && { opacity: 0.85 }]} onPress={onPress}>
        <View style={styles.infoCardTop}>
          <View style={styles.infoIconCircle}>
            <Ionicons name={icon} size={18} color={T.cyan700} />
          </View>
          <Text style={styles.infoLabel}>{label}</Text>
        </View>
        <View style={styles.infoCardBottom}>
          <Text style={styles.infoValue}>{value}</Text>
          <Text style={styles.infoSub}>{sub || '—'}</Text>
        </View>
      </Pressable>
    </AnimatedCard>
  );
}

// ─── Quick Action Tile ────────────────────────────────────────────────────────
type ActionTileProps = {
  icon: IconName;
  title: string;
  subtitle: string;
  delay?: number;
  onPress?: () => void;
};

function ActionTile({ icon, title, subtitle, delay = 0, onPress }: ActionTileProps) {
  return (
    <AnimatedCard delay={delay} style={styles.actionTile}>
      <Pressable style={({ pressed }) => [styles.actionTileInner, pressed && { opacity: 0.85 }]} onPress={onPress}>
        <View style={styles.actionTileTop}>
          <View style={styles.actionIconCircle}>
            <Ionicons name={icon} size={28} color={T.cyan700} />
          </View>
          <View style={styles.actionArrow}>
            <Ionicons name="arrow-forward" size={14} color={T.white} />
          </View>
        </View>
        <Text style={styles.actionTitle}>{title}</Text>
        <Text style={styles.actionSubtitle}>{subtitle}</Text>
      </Pressable>
    </AnimatedCard>
  );
}

// ─── Section List Card ────────────────────────────────────────────────────────
type SectionCardProps = {
  title: string;
  badge?: string;
  children: ReactNode;
  delay?: number;
  style?: StyleProp<ViewStyle>;
};

function SectionCard({ title, badge, children, delay, style }: SectionCardProps) {
  return (
    <AnimatedCard delay={delay} style={[styles.sectionCard, style]}>
      <View style={styles.sectionHeader}>
        {badge && (
          <View style={styles.sectionBadge}>
            <Text style={styles.sectionBadgeText}>{badge}</Text>
          </View>
        )}
        <Text style={styles.sectionTitle}>{title}</Text>
      </View>
      <View style={styles.sectionBody}>{children}</View>
    </AnimatedCard>
  );
}

// ─── Row Item ─────────────────────────────────────────────────────────────────
type RowItemProps = {
  icon?: IconName;
  title: string;
  subtitle: string;
  pill?: string;
  onPress?: () => void;
};

function RowItem({ icon, title, subtitle, pill, onPress }: RowItemProps) {
  return (
    <Pressable
      style={({ pressed }) => [styles.rowItem, pressed && { backgroundColor: T.slate50 }]}
      onPress={onPress}
    >
      <View style={styles.rowIconWrap}>
        <Ionicons name={icon ?? 'document-text-outline'} size={18} color={T.cyan700} />
      </View>
      <View style={styles.rowMain}>
        <Text style={styles.rowTitle}>{title}</Text>
        <Text style={styles.rowSubtitle}>{subtitle}</Text>
        {pill && (
          <View style={styles.pill}>
            <Text style={styles.pillText}>{pill}</Text>
          </View>
        )}
      </View>
      <Ionicons name="chevron-forward" size={18} color={T.slate300} />
    </Pressable>
  );
}

// ─── Notification Row ─────────────────────────────────────────────────────────
function NotifRow({ title, body }: { title: string; body: string }) {
  return (
    <View style={styles.notifRow}>
      <View style={styles.notifDot} />
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

  // Dummy pending payment for demo (replace with real API call)
  const pendingPayment = 'P 720.00';

  useEffect(() => {
    let cancelled = false;

    async function loadQueue(token: string) {
      try {
        const queuesRes = await fetch(`${API_BASE_URL}/queues?per_page=10`, {
          headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        });
        const queuesData = await queuesRes.json().catch(() => ({}));
        if (!queuesRes.ok) { if (!cancelled) setQueueStatus(null); return; }
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
                const f = activeQueue?.appointment?.doctor?.firstname ? String(activeQueue.appointment.doctor.firstname) : '';
                const l = activeQueue?.appointment?.doctor?.lastname ? String(activeQueue.appointment.doctor.lastname) : '';
                const n = `Dr. ${[f, l].filter(Boolean).join(' ')}`.trim();
                return n === 'Dr.' ? 'Doctor' : n;
              })(),
            }
          : null;
        if (!cancelled) setQueueStatus(mappedQueue);
      } catch { if (!cancelled) setQueueStatus(null); }
    }

    async function loadDashboard(token: string) {
      try {
        const [appointmentsRes, prescriptionsRes, visitsRes, notificationsRes] = await Promise.all([
          fetch(`${API_BASE_URL}/appointments?upcoming_only=1&per_page=5`, { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }),
          fetch(`${API_BASE_URL}/prescriptions?per_page=5`, { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }),
          fetch(`${API_BASE_URL}/visits?per_page=5`, { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }),
          fetch(`${API_BASE_URL}/notifications?per_page=5`, { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }),
        ]);
        const [appointmentsData, prescriptionsData, visitsData, notificationsData] = await Promise.all([
          appointmentsRes.json().catch(() => ({})),
          prescriptionsRes.json().catch(() => ({})),
          visitsRes.json().catch(() => ({})),
          notificationsRes.json().catch(() => ({})),
        ]);
        if (!appointmentsRes.ok || !prescriptionsRes.ok || !visitsRes.ok || !notificationsRes.ok) {
          const msg = appointmentsData?.message || prescriptionsData?.message || visitsData?.message || notificationsData?.message;
          setError(typeof msg === 'string' && msg.length > 0 ? msg : 'Unable to load dashboard.');
          return;
        }

        const apptsRaw = Array.isArray(appointmentsData?.data) ? appointmentsData.data : [];
        const apptsMapped: DashboardAppointment[] = apptsRaw.filter((a: any) => a?.appointment_datetime).map((a: any) => {
          const dt = new Date(a.appointment_datetime);
          const f = a?.doctor?.firstname ? String(a.doctor.firstname) : '';
          const l = a?.doctor?.lastname ? String(a.doctor.lastname) : '';
          const n = `Dr. ${[f, l].filter(Boolean).join(' ')}`.trim();
          return { id: String(a.appointment_id), date: dt.toLocaleDateString(), time: dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }), doctor: n === 'Dr.' ? 'Doctor' : n, type: a?.appointment_type === 'scheduled' ? 'Scheduled' : 'Walk-in', status: typeof a?.status === 'string' ? a.status : '' };
        });

        const presRaw = Array.isArray(prescriptionsData?.data) ? prescriptionsData.data : [];
        const presMapped: DashboardPrescription[] = presRaw.map((p: any) => {
          const dt = p?.prescribed_datetime ? new Date(p.prescribed_datetime) : null;
          const f = p?.doctor?.firstname ? String(p.doctor.firstname) : '';
          const l = p?.doctor?.lastname ? String(p.doctor.lastname) : '';
          const n = `Dr. ${[f, l].filter(Boolean).join(' ')}`.trim();
          const first = Array.isArray(p?.items) && p.items.length > 0 ? p.items[0] : null;
          return { id: String(p.prescription_id), date: dt ? dt.toLocaleDateString() : '', doctor: n === 'Dr.' ? 'Doctor' : n, summary: first?.medicine_name ? String(first.medicine_name) : 'Prescription' };
        });

        const visitsRaw = Array.isArray(visitsData?.data) ? visitsData.data : [];
        const visitsMapped: DashboardVisit[] = visitsRaw.map((v: any) => {
          const dt = v?.visit_datetime ? new Date(v.visit_datetime) : null;
          const f = v?.prescriptions?.[0]?.doctor?.firstname ? String(v.prescriptions[0].doctor.firstname) : '';
          const l = v?.prescriptions?.[0]?.doctor?.lastname ? String(v.prescriptions[0].doctor.lastname) : '';
          const n = `Dr. ${[f, l].filter(Boolean).join(' ')}`.trim();
          const reason = typeof v?.appointment?.reason_for_visit === 'string' && v.appointment.reason_for_visit.length > 0 ? v.appointment.reason_for_visit : 'Clinic visit';
          return { id: String(v.transaction_id), date: dt ? dt.toLocaleDateString() : '', doctor: n === 'Dr.' ? 'Doctor' : n, reason };
        });

        const notifsRaw = Array.isArray(notificationsData?.data) ? notificationsData.data : [];
        const notifsMapped: DashboardNotification[] = notifsRaw.map((n: any) => {
          const type = typeof n?.type === 'string' ? n.type : 'system';
          return { id: String(n.notification_id), title: `${type.charAt(0).toUpperCase()}${type.slice(1)}`, body: typeof n?.message === 'string' ? n.message : '' };
        });

        if (!cancelled) {
          setUpcomingAppointments(apptsMapped);
          setRecentPrescriptions(presMapped);
          setRecentVisits(visitsMapped);
          setNotifications(notifsMapped);
          setError('');
        }
      } catch { if (!cancelled) setError('Network error. Please try again.'); }
    }

    const token = (globalThis as any)?.apiToken as string | undefined;
    if (!token) { setError('Please log in again.'); return () => { cancelled = true; }; }

    loadDashboard(token);
    loadQueue(token);
    const intervalId = setInterval(() => { loadQueue(token); }, 15000);
    return () => { cancelled = true; clearInterval(intervalId); };
  }, []);

  const nextAppt = upcomingAppointments[0];

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" backgroundColor={T.cyan700} />
      <ScrollView
        style={styles.pageScroll}
        contentContainerStyle={styles.pageScrollContent}
        showsVerticalScrollIndicator={false}
      >
        <View style={{ backgroundColor: T.cyan700, position: 'absolute', top: -1000, left: 0, right: 0, height: 1000 }} />
        <View style={styles.header}>
          <View style={styles.headerRow}>
            <View>
              <Text style={styles.headerEyebrow}>PATIENT PORTAL</Text>
              <Text style={styles.headerTitle}>Dashboard</Text>
              <Text style={styles.headerGreeting}>Good morning, Patient 👋</Text>
            </View>
            <View style={styles.notifBtnWrap}>
              <Pressable style={styles.notifBtn} onPress={() => router.push('/screenviews/notifications' as any)}>
                <Ionicons name="notifications-outline" size={19} color={T.white} />
                {notifications.length > 0 && (
                  <View style={styles.notifBadge}>
                    <Text style={styles.notifBadgeText}>{notifications.length}</Text>
                  </View>
                )}
              </Pressable>
            </View>
          </View>
        </View>

        <View style={styles.contentSurface}>
          {error ? <Text style={styles.inlineError}>{error}</Text> : null}

          {/* ── Info Cards Row ── */}
          <View style={styles.infoRow}>
            <InfoCard
              icon="people-outline"
              label="Your current Queue"
              value={queueStatus ? queueStatus.queueNumber || '—' : '—'}
              sub={queueStatus && queueStatus.position != null ? `Patient in front: ${queueStatus.position}` : 'No active queue'}
              delay={30}
              onPress={() => router.push('/screenviews/queue' as any)}
            />
            <InfoCard
              icon="calendar-clear-outline"
              label="Upcoming appointments"
              value={nextAppt ? nextAppt.date : '—'}
              sub={nextAppt ? `${nextAppt.time} · ${nextAppt.doctor}` : `${upcomingAppointments.length} scheduled`}
              delay={60}
              onPress={() => router.push('/screenviews/appointments' as any)}
            />
            <InfoCard
              icon="card-outline"
              label="Pending payment"
              value={pendingPayment}
              sub="Please pay to the front desk"
              delay={90}
            />
          </View>

          {/* ── Quick Actions ── */}
          <AnimatedCard delay={120} style={styles.actionSection}>
            <Text style={styles.actionSectionTitle}>What would you like to do?</Text>
            <View style={styles.actionGrid}>
              <ActionTile
                icon="calendar-outline"
                title="Book Appointment"
                subtitle="Schedule a new appointment with your doctor."
                delay={140}
                onPress={() => router.push('/screenviews/book-appointment' as any)}
              />
              <ActionTile
                icon="calendar-number-outline"
                title="Appointments"
                subtitle="View and manage your upcoming appointments."
                delay={160}
                onPress={() => router.push('/screenviews/appointments' as any)}
              />
              <ActionTile
                icon="chatbubble-ellipses-outline"
                title="Chat"
                subtitle="Message your care team securely."
                delay={180}
                onPress={() => router.push('/screenviews/chat' as any)}
              />
              <ActionTile
                icon="folder-open-outline"
                title="Records"
                subtitle="View your medical records and history."
                delay={200}
                onPress={() => router.push('/screenviews/records' as any)}
              />
            </View>
          </AnimatedCard>

          {/* ── Recent Prescriptions ── */}
          {recentPrescriptions.length > 0 && (
            <SectionCard title="Recent Prescriptions" badge="Rx" delay={220}>
              {recentPrescriptions.map((item) => (
                <RowItem key={item.id} icon="medkit-outline" title={item.summary} subtitle={`${item.date} · ${item.doctor}`} />
              ))}
            </SectionCard>
          )}

          {/* ── Recent Visits ── */}
          {recentVisits.length > 0 && (
            <SectionCard title="Recent Visits" badge="History" delay={260} style={{ marginBottom: 8 }}>
              {recentVisits.map((item) => (
                <RowItem key={item.id} icon="business-outline" title={item.reason} subtitle={`${item.date} · ${item.doctor}`} />
              ))}
            </SectionCard>
          )}

          {/* ── Notifications ── */}
          {notifications.length > 0 && (
            <SectionCard title="Notifications" badge="Updates" delay={300} style={{ marginBottom: 24 }}>
              {notifications.map((item) => (
                <NotifRow key={item.id} title={item.title} body={item.body} />
              ))}
            </SectionCard>
          )}
        </View>
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
    paddingTop: 10,
    paddingBottom: 20,
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
  },
  headerEyebrow: {
    fontSize: 9,
    fontWeight: '700',
    letterSpacing: 1.2,
    color: 'rgba(255,255,255,0.65)',
    marginBottom: 2,
  },
  headerTitle: {
    fontSize: 30,
    fontWeight: '800',
    color: T.white,
    letterSpacing: 0.2,
    lineHeight: 34,
  },
  headerGreeting: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.75)',
    marginTop: 2,
    fontWeight: '400',
  },
  notifBtnWrap: {
    marginTop: 4,
  },
  notifBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.15)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.25)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  notifBadge: {
    position: 'absolute',
    top: -2,
    right: -2,
    backgroundColor: '#ef4444',
    borderRadius: 8,
    minWidth: 16,
    height: 16,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 3,
  },
  notifBadgeText: {
    fontSize: 9,
    fontWeight: '800',
    color: T.white,
  },

  // ── Page Scroll ──
  pageScroll: {
    flex: 1,
    backgroundColor: T.slate100,
  },
  pageScrollContent: {
    flexGrow: 1,
  },
  contentSurface: {
    flex: 1,
    backgroundColor: T.slate100,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    paddingTop: 20,
    paddingHorizontal: 14,
    paddingBottom: 84,
  },
  inlineError: {
    fontSize: 12,
    color: T.red700,
    marginBottom: 10,
  },

  // ── Info Cards ──
  infoRow: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 18,
    alignItems: 'stretch',
  },
  infoCard: {
    flex: 1,
  },
  infoCardInner: {
    flex: 1,
    backgroundColor: T.white,
    borderRadius: 16,
    padding: 12,
    borderWidth: 1,
    borderColor: T.slate200,
    shadowColor: T.slate900,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 2,
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    minHeight: 142,
  },
  infoCardTop: {
    width: '100%',
  },
  infoCardBottom: {
    width: '100%',
  },
  infoIconCircle: {
    width: 36,
    height: 36,
    borderRadius: 10,
    backgroundColor: 'rgba(6,182,212,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 8,
  },
  infoLabel: {
    fontSize: 9,
    fontWeight: '600',
    color: T.slate400,
    letterSpacing: 0.2,
    marginBottom: 4,
    lineHeight: 12,
  },
  infoValue: {
    fontSize: 13,
    fontWeight: '800',
    color: T.cyan700,
    lineHeight: 15,
    marginBottom: 2,
  },
  infoSub: {
    fontSize: 9,
    color: T.slate500,
    lineHeight: 13,
  },

  // ── Action Grid ──
  actionSection: {
    marginBottom: 18,
  },
  actionSectionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: T.slate800,
    marginBottom: 12,
    letterSpacing: 0.1,
  },
  actionGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  actionTile: {
    width: '48%',
  },
  actionTileInner: {
    backgroundColor: T.white,
    borderRadius: 18,
    padding: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    shadowColor: T.slate900,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
    minHeight: 140,
  },
  actionTileTop: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  actionIconCircle: {
    width: 44,
    height: 44,
    borderRadius: 14,
    backgroundColor: 'rgba(6,182,212,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  actionArrow: {
    width: 26,
    height: 26,
    borderRadius: 13,
    backgroundColor: T.cyan600,
    alignItems: 'center',
    justifyContent: 'center',
  },
  actionTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: T.slate800,
    marginBottom: 4,
    lineHeight: 17,
  },
  actionSubtitle: {
    fontSize: 10,
    color: T.slate500,
    lineHeight: 14,
  },

  // ── Section Card ──
  sectionCard: {
    backgroundColor: T.white,
    borderRadius: 18,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: T.slate200,
    shadowColor: T.slate900,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
    overflow: 'hidden',
  },
  sectionHeader: {
    paddingHorizontal: 16,
    paddingTop: 14,
    paddingBottom: 10,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    borderBottomWidth: 1,
    borderBottomColor: T.slate100,
  },
  sectionBadge: {
    backgroundColor: 'rgba(6,182,212,0.1)',
    borderRadius: 6,
    paddingHorizontal: 8,
    paddingVertical: 2,
  },
  sectionBadgeText: {
    fontSize: 9,
    fontWeight: '800',
    color: T.cyan700,
    letterSpacing: 0.5,
    textTransform: 'uppercase',
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: T.slate800,
  },
  sectionBody: {
    paddingBottom: 4,
  },

  // ── Row Item ──
  rowItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: T.slate100,
    gap: 10,
  },
  rowIconWrap: {
    width: 34,
    height: 34,
    borderRadius: 10,
    backgroundColor: T.slate100,
    alignItems: 'center',
    justifyContent: 'center',
    flexShrink: 0,
  },
  rowMain: {
    flex: 1,
  },
  rowTitle: {
    fontSize: 12,
    fontWeight: '600',
    color: T.slate800,
    marginBottom: 2,
  },
  rowSubtitle: {
    fontSize: 10,
    color: T.slate500,
    lineHeight: 14,
  },
  pill: {
    marginTop: 5,
    alignSelf: 'flex-start',
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
  // ── Notification Row ──
  notifRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: T.slate100,
    gap: 10,
  },
  notifDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: T.cyan500,
    marginTop: 4,
    flexShrink: 0,
  },
  notifBody: {
    flex: 1,
  },
  notifTitle: {
    fontSize: 12,
    fontWeight: '700',
    color: T.slate800,
    marginBottom: 2,
  },
  notifText: {
    fontSize: 10,
    color: T.slate500,
    lineHeight: 14,
  },

});
