import React, { useEffect, useRef, useState } from 'react';
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
import { Ionicons } from '@expo/vector-icons';
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
  green700: '#15803d',
  red700: '#b91c1c',
  amber700: '#b45309',
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

type AppointmentStatusTone = 'info' | 'success' | 'danger' | 'warning';

type AppointmentListItem = {
  id: string;
  date: string;
  time: string;
  doctor: string;
  type: string;
  status: string;
  statusTone: AppointmentStatusTone;
  reason: string;
  services: string[];
  queueNumber: string | null;
  patientName: string;
  isDependentAppointment: boolean;
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
  badge?: string;
  children: ReactNode;
  delay?: number;
  style?: StyleProp<ViewStyle>;
};

function SectionCard({ title, badge, children, delay, style }: SectionCardProps) {
  return (
    <AnimatedCard delay={delay} style={[styles.sectionCard, style]}>
      <View style={styles.sectionHeader}>
        {badge ? (
          <View style={styles.sectionBadge}>
            <Text style={styles.sectionBadgeText}>{badge}</Text>
          </View>
        ) : null}
        <Text style={styles.sectionTitle}>{title}</Text>
      </View>
      <View style={styles.sectionBody}>{children}</View>
    </AnimatedCard>
  );
}

function statusColors(tone: AppointmentStatusTone) {
  if (tone === 'success') {
    return { backgroundColor: 'rgba(34,197,94,0.10)', textColor: T.green700 };
  }
  if (tone === 'danger') {
    return { backgroundColor: 'rgba(239,68,68,0.10)', textColor: T.red700 };
  }
  if (tone === 'warning') {
    return { backgroundColor: 'rgba(245,158,11,0.10)', textColor: T.amber700 };
  }
  return { backgroundColor: 'rgba(6,182,212,0.10)', textColor: T.cyan700 };
}

function AppointmentDetailsCard({ item }: { item: AppointmentListItem }) {
  const colors = statusColors(item.statusTone);

  return (
    <View style={styles.appointmentCard}>
      <View style={styles.appointmentTopRow}>
        <View style={styles.rowIconWrap}>
          <Ionicons name="calendar-clear-outline" size={18} color={T.cyan700} />
        </View>
        <View style={styles.rowMain}>
          <Text style={styles.rowTitle}>{item.doctor}</Text>
          <Text style={styles.rowSubtitle}>{`${item.date} · ${item.time}`}</Text>
        </View>
        <View style={[styles.pill, { backgroundColor: colors.backgroundColor }]}>
          <Text style={[styles.pillText, { color: colors.textColor }]}>{item.status}</Text>
        </View>
      </View>

      <View style={styles.metaWrap}>
        <View style={styles.metaChip}>
          <Ionicons name="medkit-outline" size={14} color={T.cyan700} />
          <Text style={styles.metaChipText}>{item.type}</Text>
        </View>
        {item.queueNumber ? (
          <View style={styles.metaChip}>
            <Ionicons name="people-outline" size={14} color={T.cyan700} />
            <Text style={styles.metaChipText}>{`Queue #${item.queueNumber}`}</Text>
          </View>
        ) : null}
        {item.isDependentAppointment ? (
          <View style={styles.metaChip}>
            <Ionicons name="person-outline" size={14} color={T.cyan700} />
            <Text style={styles.metaChipText}>Dependent</Text>
          </View>
        ) : null}
      </View>

      <View style={styles.detailList}>
        {item.isDependentAppointment ? (
          <View style={styles.detailRow}>
            <Ionicons name="people-outline" size={15} color={T.slate500} />
            <Text style={styles.detailText}>{`Dependent appointment: appointment for "${item.patientName}"`}</Text>
          </View>
        ) : null}
        <View style={styles.detailRow}>
          <Ionicons name="document-text-outline" size={15} color={T.slate500} />
          <Text style={styles.detailText}>{item.reason}</Text>
        </View>
        <View style={styles.detailRow}>
          <Ionicons name="layers-outline" size={15} color={T.slate500} />
          <Text style={styles.detailText}>
            {item.services.length > 0 ? item.services.join(', ') : 'Service details not specified yet.'}
          </Text>
        </View>
      </View>
    </View>
  );
}

function EmptyState() {
  return (
    <View style={styles.emptyState}>
      <View style={styles.emptyIconWrap}>
        <Ionicons name="calendar-outline" size={22} color={T.cyan700} />
      </View>
      <Text style={styles.emptyTitle}>No active upcoming appointments</Text>
      <Text style={styles.emptyText}>Only appointments with pending or confirmed status appear here.</Text>
    </View>
  );
}

export default function PatientAppointmentsScreen() {
  const router = useRouter();
  const currentUserId = Number((globalThis as any)?.currentUser?.user_id ?? 0);
  const [items, setItems] = useState<AppointmentListItem[]>([]);
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

        const response = await fetch(`${API_BASE_URL}/appointments?upcoming_only=1&per_page=50&order=oldest`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${token}`,
          },
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
          const message =
            typeof data?.message === 'string' && data.message.length > 0
              ? data.message
              : 'Unable to load appointments.';
          if (!cancelled) setError(message);
          return;
        }

        const raw = Array.isArray(data?.data) ? data.data : [];
        const mapped: AppointmentListItem[] = raw
          .filter((appointment: any) => {
            if (!appointment?.appointment_datetime) return false;
            const statusRaw = typeof appointment?.status === 'string' ? appointment.status.toLowerCase() : '';
            return statusRaw === 'pending' || statusRaw === 'confirmed';
          })
          .map((appointment: any) => {
            const dt = new Date(appointment.appointment_datetime);
            const doctorFirst = appointment?.doctor?.firstname ? String(appointment.doctor.firstname) : '';
            const doctorLast = appointment?.doctor?.lastname ? String(appointment.doctor.lastname) : '';
            const doctorName = `Dr. ${[doctorFirst, doctorLast].filter(Boolean).join(' ')}`.trim();
            const statusRaw = typeof appointment?.status === 'string' ? appointment.status.toLowerCase() : '';
            const patientFirst = appointment?.patient?.firstname ? String(appointment.patient.firstname) : '';
            const patientLast = appointment?.patient?.lastname ? String(appointment.patient.lastname) : '';
            const patientName = [patientFirst, patientLast].filter(Boolean).join(' ').trim() || 'Dependent';
            const patientId = Number(appointment?.patient_id ?? appointment?.patient?.user_id ?? 0);

            let status = 'Pending';
            let statusTone: AppointmentStatusTone = 'warning';
            if (statusRaw === 'confirmed') {
              status = 'Confirmed';
              statusTone = 'info';
            }

            const services = Array.isArray(appointment?.services)
              ? appointment.services
                  .map((service: any) =>
                    typeof service?.service_name === 'string' && service.service_name.trim().length > 0
                      ? service.service_name.trim()
                      : null
                  )
                  .filter(Boolean)
              : [];

            return {
              id: String(appointment.appointment_id),
              date: dt.toLocaleDateString(),
              time: dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
              doctor: doctorName === 'Dr.' ? 'Doctor' : doctorName,
              type: appointment?.appointment_type === 'scheduled' ? 'Scheduled' : 'Walk-in',
              status,
              statusTone,
              reason:
                typeof appointment?.reason_for_visit === 'string' && appointment.reason_for_visit.trim().length > 0
                  ? appointment.reason_for_visit.trim()
                  : 'Reason for visit not provided.',
              services: services as string[],
              queueNumber:
                appointment?.queue?.queue_number != null ? String(appointment.queue.queue_number) : null,
              patientName,
              isDependentAppointment: patientId > 0 && currentUserId > 0 && patientId !== currentUserId,
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
  }, [currentUserId]);

  const nextAppointment = items[0] ?? null;

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" backgroundColor={T.cyan700} />
      <ScrollView
        style={styles.pageScroll}
        contentContainerStyle={styles.pageScrollContent}
        showsVerticalScrollIndicator={false}
      >
        <View style={styles.headerBackgroundFill} />
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

              <Text style={styles.headerTitle}>Appointments</Text>
              <Text style={styles.headerGreeting}>View your upcoming appointments.</Text>
            </View>
            <Pressable
              style={({ pressed }) => [styles.headerBtn, pressed && { opacity: 0.85 }]}
              onPress={() => router.replace('/screenviews/(tabs)' as any)}
            >
              <Text style={styles.headerBtnText}>Back</Text>
            </Pressable>
          </View>
        </View>

        <View style={styles.contentSurface}>
          {error ? <Text style={styles.inlineError}>{error}</Text> : null}

          {nextAppointment && items.length > 0 ? (
            <SectionCard title="Next Appointment" badge="Next" delay={120}>
              <AppointmentDetailsCard item={nextAppointment} />
            </SectionCard>
          ) : null}

          <SectionCard title="Upcoming Appointments" badge="Scheduled" delay={160} style={{ marginBottom: 24 }}>
            {items.length === 0 ? <EmptyState /> : items.map((item) => <AppointmentDetailsCard key={item.id} item={item} />)}
          </SectionCard>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: T.cyan700,
  },
  headerBackgroundFill: {
    backgroundColor: T.cyan700,
    position: 'absolute',
    top: -1000,
    left: 0,
    right: 0,
    height: 1000,
  },
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
    backgroundColor: 'rgba(255,255,255,0.05)',
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
    maxWidth: 250,
  },
  headerBtn: {
    marginTop: 4,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: 'rgba(255,255,255,0.15)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.25)',
  },
  headerBtnText: {
    fontSize: 12,
    fontWeight: '700',
    color: T.white,
  },
  pageScroll: {
    flex: 1,
    backgroundColor: 'rgba(255,255,255,0.07)',
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
  appointmentCard: {
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: T.slate100,
  },
  appointmentTopRow: {
    flexDirection: 'row',
    alignItems: 'center',
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
    borderRadius: 999,
    paddingHorizontal: 8,
    paddingVertical: 4,
    alignSelf: 'flex-start',
  },
  pillText: {
    fontSize: 9,
    fontWeight: '700',
    letterSpacing: 0.4,
    textTransform: 'uppercase',
  },
  metaWrap: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginTop: 12,
  },
  metaChip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: T.slate50,
    borderWidth: 1,
    borderColor: T.slate200,
    borderRadius: 999,
    paddingHorizontal: 10,
    paddingVertical: 6,
  },
  metaChipText: {
    fontSize: 10,
    fontWeight: '600',
    color: T.slate700,
  },
  detailList: {
    marginTop: 12,
    gap: 8,
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 8,
  },
  detailText: {
    flex: 1,
    fontSize: 11,
    color: T.slate600,
    lineHeight: 16,
  },
  emptyState: {
    paddingHorizontal: 16,
    paddingVertical: 22,
    alignItems: 'center',
  },
  emptyIconWrap: {
    width: 48,
    height: 48,
    borderRadius: 16,
    backgroundColor: 'rgba(6,182,212,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  emptyTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: T.slate800,
    marginBottom: 4,
  },
  emptyText: {
    fontSize: 11,
    color: T.slate500,
    lineHeight: 16,
    textAlign: 'center',
    maxWidth: 250,
  },
});
