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
  Modal,
} from 'react-native';
import type { StyleProp, ViewStyle } from 'react-native';
import { useIsFocused } from '@react-navigation/native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import {
  fetchPatientNotifications,
  formatNotificationTimestamp,
  markPatientNotificationsAsRead,
  type PatientNotification as DashboardNotification,
} from '../../../lib/notifications';

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

function formatDashboardDate(value: any): string {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return 'Date unavailable';
  return date.toLocaleDateString([], {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
}

function formatDashboardTime(value: any): string {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return 'Time unavailable';
  return date.toLocaleTimeString([], {
    hour: 'numeric',
    minute: '2-digit',
  }).replace(':00 ', '').replace(' ', '');
}

function formatCurrency(value: number): string {
  if (Number.isNaN(value)) return 'P 0.00';
  return `P ${value.toFixed(2)}`;
}

type DashboardAppointment = {
  id: string;
  date: string;
  time: string;
  doctor: string;
  type: string;
  status: string;
  statusRaw: string;
  serviceTotal: number;
  sortAt: number;
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

type DashboardQueueStatus = {
  queueId: string;
  status: string;
  queueNumber: string;
  position: number | null;
  estimatedWaitMinutes: number | null;
  doctor: string;
};

type PendingBillingCard = {
  value: string;
  sub: string;
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
function NotifRow({ title, body, meta }: { title: string; body: string; meta?: string }) {
  return (
    <View style={styles.notifRow}>
      <View style={styles.notifDot} />
      <View style={styles.notifBody}>
        <Text style={styles.notifTitle}>{title}</Text>
        <Text style={styles.notifText}>{body}</Text>
        {meta ? <Text style={styles.notifMeta}>{meta}</Text> : null}
      </View>
    </View>
  );
}

// ─── Main Screen ──────────────────────────────────────────────────────────────
export default function PatientDashboardScreen() {
  const router = useRouter();
  const isFocused = useIsFocused();
  const [upcomingAppointments, setUpcomingAppointments] = useState<DashboardAppointment[]>([]);
  const [recentPrescriptions, setRecentPrescriptions] = useState<DashboardPrescription[]>([]);
  const [recentVisits, setRecentVisits] = useState<DashboardVisit[]>([]);
  const [notifications, setNotifications] = useState<DashboardNotification[]>([]);
  const [queueStatus, setQueueStatus] = useState<DashboardQueueStatus | null>(null);
  const [pendingBilling, setPendingBilling] = useState<PendingBillingCard>({
    value: '_ _',
    sub: 'All consulted bills are already paid',
  });
  const [error, setError] = useState('');
  const [notificationsOpen, setNotificationsOpen] = useState(false);
  const loadingQueueRef = useRef(false);

  async function openNotificationsModal() {
    setNotificationsOpen(true);

    const unreadIds = notifications
      .slice(0, 10)
      .filter((item) => !item.isRead)
      .map((item) => item.id);

    if (!unreadIds.length) {
      return;
    }

    setNotifications((current) => current.map((item) => (
      unreadIds.includes(item.id) ? { ...item, isRead: true } : item
    )));

    const token = (globalThis as any)?.apiToken as string | undefined;
    if (!token) {
      return;
    }

    try {
      await markPatientNotificationsAsRead(token, unreadIds);
    } catch {
      setNotifications((current) => current.map((item) => (
        unreadIds.includes(item.id) ? { ...item, isRead: false } : item
      )));
    }
  }

  useEffect(() => {
    if (!isFocused) return;

    let cancelled = false;

    async function loadQueue(token: string) {
      if (loadingQueueRef.current) return;

      loadingQueueRef.current = true;
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
      finally { loadingQueueRef.current = false; }
    }

    async function loadDashboard(token: string) {
      try {
        const [appointmentsRes, prescriptionsRes, visitsRes, transactionsRes, notificationsList] = await Promise.all([
          fetch(`${API_BASE_URL}/appointments?per_page=100&order=latest`, { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }),
          fetch(`${API_BASE_URL}/prescriptions?per_page=5`, { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }),
          fetch(`${API_BASE_URL}/visits?per_page=5`, { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }),
          fetch(`${API_BASE_URL}/transactions?per_page=100&order=latest`, { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }),
          fetchPatientNotifications(token, 10),
        ]);
        const [appointmentsData, prescriptionsData, visitsData, transactionsData] = await Promise.all([
          appointmentsRes.json().catch(() => ({})),
          prescriptionsRes.json().catch(() => ({})),
          visitsRes.json().catch(() => ({})),
          transactionsRes.json().catch(() => ({})),
        ]);
        if (!appointmentsRes.ok || !prescriptionsRes.ok || !visitsRes.ok || !transactionsRes.ok) {
          const msg = appointmentsData?.message || prescriptionsData?.message || visitsData?.message || transactionsData?.message;
          setError(typeof msg === 'string' && msg.length > 0 ? msg : 'Unable to load dashboard.');
          return;
        }

        const apptsRaw = Array.isArray(appointmentsData?.data) ? appointmentsData.data : [];
        const apptsMapped: DashboardAppointment[] = apptsRaw
          .filter((a: any) => a?.appointment_datetime)
          .map((a: any) => {
            const dt = new Date(a.appointment_datetime);
            const f = a?.doctor?.firstname ? String(a.doctor.firstname) : '';
            const l = a?.doctor?.lastname ? String(a.doctor.lastname) : '';
            const n = `Dr. ${[f, l].filter(Boolean).join(' ')}`.trim();
            const statusRaw = typeof a?.status === 'string' ? a.status.toLowerCase() : '';
            const serviceTotal = Array.isArray(a?.services)
              ? a.services.reduce((sum: number, service: any) => {
                  const price = typeof service?.price === 'number' ? service.price : service?.price != null ? Number(service.price) : 0;
                  return sum + (Number.isNaN(price) ? 0 : price);
                }, 0)
              : 0;

            return {
              id: String(a.appointment_id),
              date: formatDashboardDate(a.appointment_datetime),
              time: formatDashboardTime(a.appointment_datetime),
              doctor: n === 'Dr.' ? 'Doctor' : n,
              type: a?.appointment_type === 'scheduled' ? 'Scheduled' : 'Walk-in',
              status: typeof a?.status === 'string' ? a.status : '',
              statusRaw,
              serviceTotal,
              sortAt: Number.isNaN(dt.getTime()) ? 0 : dt.getTime(),
            };
          });

        const nowTime = Date.now();
        const upcomingMapped = [...apptsMapped]
          .filter((appointment) => (
            appointment.sortAt >= nowTime &&
            (appointment.statusRaw === 'pending' || appointment.statusRaw === 'confirmed')
          ))
          .sort((a, b) => a.sortAt - b.sortAt);

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

        const transactionsRaw = Array.isArray(transactionsData?.data) ? transactionsData.data : [];
        const transactionByAppointmentId = new Map<string, any>();
        transactionsRaw.forEach((transaction: any) => {
          const appointmentId = transaction?.appointment_id != null
            ? String(transaction.appointment_id)
            : transaction?.appointment?.appointment_id != null
              ? String(transaction.appointment.appointment_id)
              : '';
          if (!appointmentId || transactionByAppointmentId.has(appointmentId)) {
            return;
          }
          transactionByAppointmentId.set(appointmentId, transaction);
        });

        const pendingBillingAppointment = [...apptsMapped]
          .filter((appointment) => appointment.statusRaw === 'consulted')
          .find((appointment) => {
            const transaction = transactionByAppointmentId.get(appointment.id);
            const paymentStatus = typeof transaction?.payment_status === 'string' ? transaction.payment_status.toLowerCase() : '';
            return paymentStatus !== 'paid';
          });

        const pendingBillingCard: PendingBillingCard = pendingBillingAppointment
          ? {
              value: formatCurrency(pendingBillingAppointment.serviceTotal),
              sub: `${pendingBillingAppointment.doctor} · ${pendingBillingAppointment.date} · ${pendingBillingAppointment.time}`,
            }
          : {
              value: '_ _',
              sub: 'All consulted bills are already paid',
            };

        if (!cancelled) {
          setUpcomingAppointments(upcomingMapped);
          setRecentPrescriptions(presMapped);
          setRecentVisits(visitsMapped);
          setNotifications(notificationsList);
          setPendingBilling(pendingBillingCard);
          setError('');
        }
      } catch (err) {
        if (!cancelled) {
          setError(err instanceof Error && err.message ? err.message : 'Network error. Please try again.');
        }
      }
    }

    const token = (globalThis as any)?.apiToken as string | undefined;
    if (!token) { setError('Please log in again.'); return () => { cancelled = true; }; }

    loadDashboard(token);
    loadQueue(token);
    const intervalId = setInterval(() => { loadQueue(token); }, 15000);
    return () => { cancelled = true; clearInterval(intervalId); };
  }, [isFocused]);

  const nextAppt = upcomingAppointments[0];
  const notificationPreview = notifications.slice(0, 10);


  const getGreeting = () => {
  const currentHour = new Date().getHours();
  
  if (currentHour < 12) {
    return 'Good morning';
  } else if (currentHour < 18) {
    return 'Good afternoon';
  } else {
    return 'Good evening';
  }
};

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
          <View style={styles.circleTopRight} />
          <View style={styles.circleBottomLeft} />
          <View style={styles.circleMidLeft} />
          <View style={styles.headerRow}>
          <View>
 
 <View style={styles.eyebrowRow}>
              <View style={[styles.eyebrowDot, { backgroundColor: 'rgba(255,255,255,0.7)' }]} />
              <Text style={[styles.eyebrowText, { color: 'rgba(255,255,255,0.8)' }]}>Patient Portal</Text>
            </View>
  <Text style={styles.headerTitle}>Dashboard</Text>
  <View style={styles.greetingContainer}>
    <Text style={styles.headerGreeting}>{getGreeting()}, Patient</Text>
    <Ionicons name="hand-right-outline" size={14} color="rgba(255,255,255,0.75)" style={styles.waveIcon} />
  </View>
</View>
            <View style={styles.notifBtnWrap}>
              <Pressable style={styles.notifBtn} onPress={openNotificationsModal}>
                <Ionicons name="notifications-outline" size={19} color={T.white} />
                {notifications.some((item) => !item.isRead) && (
                  <View style={styles.notifBadge}>
                    <Text style={styles.notifBadgeText}>
                      {Math.min(notifications.filter((item) => !item.isRead).length, 99)}
                    </Text>
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
              value={queueStatus ? queueStatus.queueNumber || '—' : 'Join Queue'}
              sub={queueStatus
                ? (queueStatus.position != null ? `Patient in front: ${queueStatus.position}` : 'Queue entry active')
                : 'Tap here to join the walk-in queue'}
              delay={30}
              onPress={() => router.push('/screenviews/queue' as any)}
            />
            <InfoCard
              icon="calendar-clear-outline"
              label="Appointments"
              value={nextAppt ? nextAppt.doctor : '_ _'}
              sub={nextAppt ? `${nextAppt.date} - ${nextAppt.time}` : 'No active pending or confirmed schedule'}
              delay={60}
              onPress={() => router.push('/screenviews/appointments' as any)}
            />
            <InfoCard
              icon="card-outline"
              label="Pending payment"
              value={pendingBilling.value}
              sub={pendingBilling.sub}
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
                onPress={() => router.push('/screenviews/booking' as any)}
              />
              <ActionTile
                icon="calendar-number-outline"
                title="Appointments"
                subtitle="Manage your appointments & history."
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
        </View>
      </ScrollView>
      <Modal visible={notificationsOpen} transparent animationType="fade" onRequestClose={() => setNotificationsOpen(false)}>
        <Pressable style={styles.notifDropdownBackdrop} onPress={() => setNotificationsOpen(false)} />
        <View style={styles.notifDropdownWrap}>
          <View style={styles.notifDropdownCard}>
            <View style={styles.notifDropdownHeader}>
              <View>
                <Text style={styles.notifDropdownTitle}>Notifications</Text>
                <Text style={styles.notifDropdownSubtitle}>Latest 10 updates</Text>
              </View>
              <Pressable
                onPress={() => setNotificationsOpen(false)}
                style={({ pressed }) => [styles.notifDropdownClose, pressed && { opacity: 0.75 }]}
              >
                <Ionicons name="close" size={16} color={T.slate600} />
              </Pressable>
            </View>

            <ScrollView
              style={styles.notifDropdownScroll}
              contentContainerStyle={styles.notifDropdownContent}
              showsVerticalScrollIndicator={false}
            >
              {notificationPreview.length > 0 ? (
                notificationPreview.map((item) => (
                  <NotifRow
                    key={item.id}
                    title={item.title}
                    body={item.body}
                    meta={formatNotificationTimestamp(item.createdAt)}
                  />
                ))
              ) : (
                <View style={styles.notifEmptyState}>
                  <Ionicons name="notifications-off-outline" size={22} color={T.slate400} />
                  <Text style={styles.notifEmptyTitle}>No notifications yet</Text>
                  <Text style={styles.notifEmptyText}>Your latest appointment, queue, and account updates will appear here.</Text>
                </View>
              )}
            </ScrollView>

            <View style={styles.notifDropdownFooter}>
              <Pressable
                style={({ pressed }) => [styles.notifSeeMoreBtn, pressed && { opacity: 0.9 }]}
                onPress={() => {
                  setNotificationsOpen(false);
                  router.push('/screenviews/notifications' as any);
                }}
              >
                <Text style={styles.notifSeeMoreText}>See more</Text>
                <Ionicons name="arrow-forward" size={14} color={T.cyan700} />
              </Pressable>
            </View>
          </View>
        </View>
      </Modal>
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
    paddingTop: 50,
    paddingBottom: 20,
    position: 'relative',
    overflow: 'hidden',
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
    bottom: -80,
    left: -60,
    width: 190,
    height: 190,
    borderRadius: 95,
    backgroundColor: 'rgba(255,255,255,0.07)',
  },
  circleMidLeft: {
    position: 'absolute',
    top: 30,
    left: -90,
    width: 180,
    height: 180,
    borderRadius: 90,
    backgroundColor: 'rgba(255,255,255,0.07)',
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    position: 'relative',
    zIndex: 1,
  },
  headerEyebrow: {
    fontSize: 9,
    fontWeight: '700',
    letterSpacing: 1.2,
    color: 'rgba(255,255,255,0.65)',
    marginBottom: 2,
  },
     eyebrowRow: { flexDirection: 'row', alignItems: 'center', gap: 5, marginBottom: 4 },
  eyebrowDot: { width: 6, height: 6, borderRadius: 3, backgroundColor: T.cyan500 },
  eyebrowText: { fontSize: 9, fontWeight: '700', letterSpacing: 0.9, textTransform: 'uppercase', color: T.cyan600 },
  

    headerTitle: {
    fontSize: 30,
    fontWeight: '800',
    fontFamily: 'serif',
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
  greetingContainer: {
  flexDirection: 'row',
  alignItems: 'center',
  marginTop: 2,
},
waveIcon: {
  marginLeft: 6,
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
    backgroundColor: 'rgba(255,255,255,0.07)', 
  },


 scroll: {
    flex: 1,
    backgroundColor: T.slate100,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    marginTop: -10,
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
    paddingBottom: 14,
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
  notifMeta: {
    fontSize: 10,
    color: T.slate400,
    marginTop: 6,
  },
  notifDropdownBackdrop: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(15,23,42,0.18)',
  },
  notifDropdownWrap: {
    position: 'absolute',
    top: 86,
    left: 14,
    right: 14,
    alignItems: 'flex-end',
  },
  notifDropdownCard: {
    width: '100%',
    maxWidth: 360,
    backgroundColor: T.white,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: T.slate200,
    shadowColor: T.slate900,
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.14,
    shadowRadius: 18,
    elevation: 8,
    overflow: 'hidden',
  },
  notifDropdownHeader: {
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: T.slate100,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
  },
  notifDropdownTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: T.slate800,
  },
  notifDropdownSubtitle: {
    fontSize: 11,
    color: T.slate500,
    marginTop: 2,
  },
  notifDropdownClose: {
    width: 30,
    height: 30,
    borderRadius: 15,
    backgroundColor: T.slate50,
    alignItems: 'center',
    justifyContent: 'center',
  },
  notifDropdownScroll: {
    maxHeight: 360,
  },
  notifDropdownContent: {
    paddingBottom: 6,
  },
  notifDropdownFooter: {
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderTopWidth: 1,
    borderTopColor: T.slate100,
  },
  notifSeeMoreBtn: {
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(8,145,178,0.18)',
    backgroundColor: 'rgba(6,182,212,0.08)',
    paddingHorizontal: 14,
    paddingVertical: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
  },
  notifSeeMoreText: {
    fontSize: 12,
    fontWeight: '700',
    color: T.cyan700,
  },
  notifEmptyState: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 24,
    paddingVertical: 28,
  },
  notifEmptyTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: T.slate700,
    marginTop: 10,
    marginBottom: 4,
  },
  notifEmptyText: {
    fontSize: 11,
    color: T.slate500,
    lineHeight: 16,
    textAlign: 'center',
  },

});
